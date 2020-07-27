<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Billplz\Client;
use App\BillplzPayment;
use Illuminate\Support\Facades\Auth;
use DB;
use App\User;
use App\Invoice;
use App\Receipt;
use Http\Client\Common\HttpMethodsClient;
use Http\Adapter\Guzzle6\Client as GuzzleHttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;

class BillplzController extends Controller
{
    // function to pay with billplz
    public function payOrder(Request $request){

        $price = $request->input('price')*100;

        // Creating Client
        $billplz = Client::make('api-key', 'xsignature-key');
        
        // Using Sandbox
        $billplz->useSandbox();

        $billplzTable = BillplzPayment::all();
        if ($billplzTable->isEmpty()) { 
            // Create an instance of Collection 
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

        // check if form is submitted from total order or single order
        $oids = $request->input('orderids');
        if (isset($oids)) {

            $prices = $request->input('prices');
            $count = 0;
            foreach($oids as $oid){

                $oriprice = $prices[$count] +0;
                $billarr = $responsebill->toArray();
                $billid = $billarr['id'];
                
                $billplzPayment = new BillplzPayment;
                $billplzPayment->user_id = $uid;
                $billplzPayment->order_id = $oid;
                $billplzPayment->bill_id = $billid;
                $billplzPayment->collection_id = $collectionid;
                $billplzPayment->payment_status = 'due';
                $billplzPayment->order_price = $oriprice;
                $billplzPayment->save();
                $count++ ;

            }

        }else{

            $oid = $request->input('oid');
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

        }

        return redirect($billarr['url']);
    }

    public function bpRedirect(){

        $billplz = Client::make('api-key', 'xsignature-key');
        $bill = $billplz->bill();
        $data = $bill->redirect($_GET);     
        // var_dump($data);
        $uid = Auth::user()->u_id;
        $user = User::find($uid);
        if($data['paid'] == true){

            // update the payment status into billplz_payment table
            DB::table('billplz_payments')
                    ->where('bill_id', '=', $data['id'])
                    ->update(array('payment_status' => 'paid','updated_at'=>DB::raw('now()')));
            
            $bps = DB::table('billplz_payments')
                ->where('bill_id', '=', $data['id'])
                ->get();

            foreach($bps as $bp){

                $ord = DB::table('orders')
                        ->where('o_id', '=', $bp->order_id)
                        ->first();

                $bal = $ord->balance - $bp->order_price;

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

            }

            // return view receipt page
            $rcp = Receipt::all();
            $receipts = DB::table('receipt')
                        ->leftJoin('orders','receipt.o_id','=','orders.o_id')
                        ->leftJoin('user','orders.u_id_customer','=','user.u_id')
                        ->where('receipt.re_status','=','1')
                        ->where('orders.u_id_customer','=',$uid)
                        ->paginate(30);
            $user = User::all();

            return view('customer/receipt',compact('receipts','user','rcp'));

        }else{

            // delete row in billpliz_payment table that failed to make payment 
            BillplzPayment::where('bill_id', '=', $data['id'])->delete();

            // return view invoice page
            $invs = Invoice::all();
            $invoice = DB::table('invoice')
                        ->leftJoin('orders', 'invoice.o_id', '=', 'orders.o_id')
                        ->leftJoin('user', 'orders.u_id_customer', '=', 'user.u_id')
                        ->where('orders.active','=','1')
                        ->where('orders.u_id_customer','=',$uid)
                        ->paginate(30);
            
            return view('customer/invoice', compact('invoice', 'invs')); 
        }

    }

    public function bpWebhook(Request $request){
        
    }

}
