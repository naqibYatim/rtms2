<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Billplz\Client;
use App\BillplzPayment;
use Illuminate\Support\Facades\Auth;
use DB;
use App\User;
use Http\Client\Common\HttpMethodsClient;
use Http\Adapter\Guzzle6\Client as GuzzleHttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;

class BillplzController extends Controller
{
    // function to pay with billplz
    public function payOrder(Request $request){

        $price = $request->input('price')*100;
        $oid = $request->input('oid');

        // Creating Client
        $billplz = Client::make('api-key', 'xsignature-key');
        
        // Using Sandbox
        $billplz->useSandbox();

        $billplzTable = BillplzPayment::all();
        if ($billplzTable->isEmpty()) { 
            // Create an instance of Collection (maybe need to created per month)
            $collection = $billplz->collection();
            $responsecreatecollection = $collection->create('RTMS Bill Collection');
            $collarr = $responsecreatecollection->toArray();
            $collectionid = $collarr['id'];
        } else {
            // get the collection id
            $collectionid = BillplzPayment::latest('created_at')
                            ->first()->collection_id;
        }
        $uid = Auth::user()->u_id;
        $user = User::find($uid);
        $bill = $billplz->bill();
        $responsebill = $bill->create(
            $collectionid,
            $user->email,
            $user->phone,
            $user->u_fullname,
            \Duit\MYR::given($price),
            'http://localhost/rtms2/public/customer/bpwebhook/',
            'Bill payment for '.$user->u_fullname,
            ['redirect_url' => 'http://localhost/rtms2/public/customer/bpredirect/']
        ); 
        $oriprice = $price/100;
        $billarr = $responsebill->toArray();
        // get bill id
        $billid = $billarr['id'];
        // Store the bill into billplz_payment table
        $billplzPayment = new BillplzPayment;
        $billplzPayment->user_id = $uid;
        $billplzPayment->order_id = $oid;
        $billplzPayment->bill_id = $billid;
        $billplzPayment->collection_id = $collectionid;
        $billplzPayment->payment_status = 'due';
        $billplzPayment->order_price = $oriprice;
        $billplzPayment->save();

        return redirect($billarr['url']);
    }

    public function bpRedirect(){
        // setup a redirect page where user will be redirected after payment is completed
        $billplz = Client::make('a0cc4de0-ffd6-4435-86da-d8faf4de9919', 'S-m0uAna8sTOgYpscyO3hZ7g');
        $bill = $billplz->bill();
        $data = $bill->redirect($_GET);     // Expected $billplz to be an array!
        // var_dump($data);
        $uid = Auth::user()->u_id;
        $user = User::find($uid);
        if($data['paid'] == true){
            // update the payment status into billplz_payment table
            DB::table('billplz_payments')
                    ->where('bill_id', '=', $data['id'])
                    ->update(array('payment_status' => 'paid','updated_at'=>DB::raw('now()')));
            
            $bp = DB::table('billplz_payments')
                ->where('bill_id', '=', $data['id'])
                ->first();
            $ord = DB::table('orders')
                    ->where('o_id', '=', $bp->order_id)
                    ->first();
            $bal = $ord->balance - $bp->order_price;
            //var_dump($bal);

            // update balance in table order
            DB::table('orders')
                    ->where('o_id', '=', $bp->order_id)
                    ->update(array('balance' => $bal,'updated_at'=>DB::raw('now()')));

            DB::table('receipt')->insert([
                     'o_id' => $bp->order_id,
                     'description'=> 'Payment from billplz',
                     'total_paid'=> $bp->order_price,
                     're_status'=> '1',
                     'created_at' => DB::raw('now()'),
                     'updated_at' => DB::raw('now()')
                    ]);
            // return view receipt page
            $receipts = DB::table('receipt')
                        ->leftJoin('orders','receipt.o_id','=','orders.o_id')
                        ->leftJoin('user','orders.u_id_customer','=','user.u_id')
                        ->where('receipt.re_status','=','1')
                        ->where('orders.u_id_customer','=',$uid)
                        ->paginate(30);
            $user = User::all();

            return view('customer/receipt',compact('receipts','user'));
        }else{
            // return view invoice page
            $invoice = DB::table('invoice')
                        ->leftJoin('orders', 'invoice.o_id', '=', 'orders.o_id')
                        ->leftJoin('user', 'orders.u_id_customer', '=', 'user.u_id')
                        ->where('orders.active','=','1')
                        ->where('orders.u_id_customer','=',$uid)
                        ->paginate(30);
            
            return view('customer/invoice', compact('invoice')); 
        }
    }

    public function bpWebhook(Request $request){
        
        // //$billplz = Client::make('fa0b3d87-8551-4e62-b4a4-3447eee0b4f1', 'S-KGhvHpAm5NTNBMLbgGXdsA');
        // //$bill = $billplz->bill();
        // // setup a webhook to receive POST request from Billplz
        // $data = $bill->webhook($_POST);     // Undefined index: paid
        // $dataarr = $data->toArray();
        // var_dump($dataarr);
        // if($dataarr['state']=='paid'){

        //     $uid = Auth::user()->u_id;
        //     $user = User::find($uid);
        //     // dd("update table order and receipt");
        //     // Store the bill into billplz_payment table
        //     $billplzPayment = new BillplzPayment;
        //     $billplzPayment->user_id = $uid;
        //     $billplzPayment->order_id = 1;
        //     $billplzPayment->bill_id = $dataarr['state'];
        //     $billplzPayment->collection_id = $dataarr['amount'];
        //     $billplzPayment->save();

        // }

    }

}
