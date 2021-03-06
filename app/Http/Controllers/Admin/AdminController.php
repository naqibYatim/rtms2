<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\LeaveDay;
use App\Leave; 
use App\Body;
use App\Neck;
use App\Sleeve;
use App\Material;
use App\DeliverySetting;
use App\Design;
use App\Unit;
use App\Order;
use App\Spec;
use App\Price;
use App\BlockDay;
use App\BlockDate;
use App\Stock;
use App\Invoice;
use App\Receipt;
use Carbon\Carbon;
use DB;

class AdminController extends Controller
{
    // admin
    
    public function __construct()
    {
        $this->middleware('admin');
    }
    
    //navigation bar
    //manage customer dropdown
    public function addAgent() 
    {
        return \View::make('admin/add_agent');
    }
    
    public function agentList() 
    {
        return \View::make('admin/agent_list');
    }
    
    public function addCustomer() 
    {
        return \View::make('admin/add_customer');
    }
    
    public function customerApplication() 
    {
        //return \View::make('admin/customer_application');
        $customer = User::selectRaw('*')
                    ->where('u_type', 7)
                    ->where('u_status','=',2)
                    ->paginate(10);
                    //->get();
        $agent = User::selectRaw('*')
                    ->where('u_type', 6)
                    ->where('u_status','=',2)
                    ->paginate(10);          

        return view('admin/customer_application', compact('customer','agent'));
    }
    
    //manage staff dropdown
    public function manageStaff() 
    {
        $staffs = User::selectRaw('*')
                    ->whereIn('u_type', array(3,4,5))
                    ->where('u_status','=',1)
                    ->paginate(30);
         

        return view('admin/manage_staff', compact('staffs'));
    }
    
    public function staffApplication() 
    {
        $staff = User::selectRaw('*')
                    ->whereIn('u_type', array(3,4,5))
                    ->where('u_status','=',2)
                    ->paginate(10);        

        return view('admin/staff_application', compact('staff'));
    }
    
    public function addStaff() 
    {
        return \View::make('admin/add_newstaff');
    }
    
    public function leaveList() 
    {
        $leave = DB::table('leave')
                    ->join('user', 'leave.u_id', '=', 'user.u_id')
                    ->where('leave.l_status','=',1)
                    ->paginate(30);        
        
        return view('admin/leave_list', compact('leave'));
    }
    
    public function leaveApplication() 
    {
        $leave = DB::table('leave')
                    ->join('user', 'leave.u_id', '=', 'user.u_id')
                    ->where('leave.l_status','=',2)
                    ->paginate(30);         
                
        return view('admin/leave_application', compact('leave'));
    }
    
    public function leaveDay() 
    {
        $staff = DB::table('leave_day')
                    ->rightJoin('user', 'leave_day.u_id', '=', 'user.u_id')
                    ->whereIn('user.u_type', array(3,4,5))
                    ->where('user.u_status','=',1)
                    ->paginate(30);
        
        $leave = LeaveDay::all();
         
        return view('admin/leave_day', compact('staff','leave'));
    }
    
    public function staffPerformance() 
    {
        
        $unit = Unit::all();
        $designs = Design::all();
        $order = Order::all();
        $user = DB::table('user')
                    ->where('u_type', 3)
                    ->where('u_status','=',1)
                    ->paginate(5);
    
        $print = DB::table('user')
                    ->where('u_type', 5)
                    ->where('u_status','=',1)
                    ->paginate(5);
       
        $tailor = DB::table('user')
                    ->where('u_type', 4)
                    ->where('u_status','=',1)
                    ->paginate(5);
         
        return view('admin/staff_performance', compact('user','print','tailor','unit','designs','order'));
    }
    
    public function orderSetting() 
    {
        $body = Body::selectRaw('*')
                ->where('b_status','=',1)
                ->get();
    
        $material = Material::selectRaw('*')
                ->where('m_status','=',1)                
                ->get();
 
        $neck = Neck::selectRaw('*')
                ->where('n_status','=',1)                
                ->get();

        $sleeve = Sleeve::selectRaw('*')
                ->get();
        
        $delivery = DeliverySetting::selectRaw('*')
                ->first();
        
        $block_day = BlockDay::selectRaw('*')
                ->get();
        
        $block_date = BlockDate::selectRaw('*')
                ->where('bdt_status','=',1)
                ->whereDate('date','>', Carbon::now())
                ->get();
        
        $today = Carbon::now();
        
        //dd($today);
        
        return view('admin/order_setting', compact('body','material','neck','sleeve','delivery','block_day','block_date','today'));
    }
    
    public function orderList() 
    {
        $ye = date('Y')+1;
        $m = date('m');
        $y = date('Y');
        
        $years = [];
        for ($year=2017; $year <= $ye; $year++) $years[] = $year;
        
        $order = DB::table('orders')
                    ->Join('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.o_status','<>','9')
                    ->where('orders.active','=','1')
                    ->orderBy('orders.delivery_date', 'asc')
                    ->paginate(30);

        $date = Order::all();
        
        return view('admin/admin_orderlist',compact('order','y','m','years','date'));
    }
    
    public function filterOrder(Request $request) 
    { 
        $data = $request->all();
       
        $m = $data['month'];
        $y = $data['years'];
        
        $ye = date('Y')+1;
        
        $years = [];
        for ($year=2017; $year <= $ye; $year++) { $years[] = $year ;}
        
        $order = DB::table('orders')
                    ->Join('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.o_status','<>','9')
                    ->where('orders.active','=','1')
                    ->whereMonth('orders.delivery_date','=',$m)
                    ->whereYear('orders.delivery_date','=',$y)
                    ->orderBy('orders.delivery_date', 'asc')
                    ->paginate(30);

        $date = Order::all();
        
        return view('admin/admin_orderlist',compact('order','m','y','years','date'));
    }

    // function to search order by keyword
    function searchOrder(Request $request)
    {
        if($request->ajax())
        {
            $output = '';
            $query = $request->get('query');
            if($query != '')
            {
                $data = DB::table('orders')
                    ->Join('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.o_status','!=','9')
                    ->where('orders.active','=','1')
                    ->where('orders.ref_num', 'like', '%'.$query.'%')
                    ->orWhere('user.u_fullname', 'like', '%'.$query.'%')
                    ->orWhere('orders.file_name', 'like', '%'.$query.'%')
                    ->orWhere('orders.quantity_total', 'like', '%'.$query.'%')
                    ->orderBy('orders.delivery_date', 'asc')
                    ->get();
                    
            }
            else
            {
                $data = DB::table('orders')
                    ->Join('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.o_status','<>','9')
                    ->where('orders.active','=','1')
                    ->orderBy('orders.delivery_date', 'asc')
                    ->get();

            }
            $total_row = $data->count();
            $date = Order::all();
            if($total_row > 0)
            {
                
                $no = 1;
                foreach($data as $row)
                {
                    // status of order
                    $status = $row->o_status;
                    $activestat = $row->active;
                    if($status != 9 && $activestat == 1){
                        $statdesc = '';
                        if($status == 1){
                            $statdesc = 'Waiting for design';
                        }else if($status == 2){
                            $statdesc = 'Order Confirm';
                        }else if($status == 3){
                            $statdesc = 'Design Confirm';
                        }else if($status == 4){
                            $statdesc = 'Printing';
                        }else if($status == 5){
                            $statdesc = 'Waiting for Tailor';
                        }else if($status == 6){
                            $statdesc = 'Sewing';
                        }else if($status == 7){
                            $statdesc = 'Deliver';
                        }else if($status == 8){
                            $statdesc = 'Reprint';
                        }else if($status == 9){
                            $statdesc = 'Completed';
                        }else if($status == 10){
                            $statdesc = 'Customer Request Design';
                        }else if($status == 0){
                            $statdesc = 'Draft';
                        }
                        $orderid = $row->o_id;

                        // action column
                        $action = '<a href="'.route('admin.updateorder',$orderid).'"><button ><i class="fa fa-edit"></i></button></a>'.
                                '<form class="formbutton" action="'.route('admin.deleteorder').'" method="POST">'. csrf_field() .'
                                    <button  type="submit" onclick="return confirm(\'Are you sure to delete this order?\')" ><i class="fa fa-trash"></i></button>
                                    <input type="hidden" name="o_id" value=" '.$orderid.'">                                 
                                </form>'.
                                '<a href="'.route('general.joborder',$orderid).'"><button >Job Order</button></a>';

                        $output .= '
                        <tr>
                        <td>'.$no.'</td>
                        <td>'.$row->ref_num.'</td>
                        <td>'.date('d/m/Y', strtotime($date->where('o_id',$row->o_id)->pluck('created_at')->first())).'</td>
                        <td>'.$row->u_fullname.'</td>
                        <td>'.$row->file_name.'</td>
                        <td>'.$row->quantity_total.'</td>
                        <td>'.date('d/m/Y', strtotime($row->delivery_date)).'</td>
                        <td>'.$statdesc.'</td>
                        <td>'.$action.'</td>
                        </tr>
                        ';
                        $no ++;
                    }
                }
            }
            else
            {
                $output = '
                <tr>
                    <td align="center" colspan="10">No Data Found</td>
                </tr>
                ';
            }
            $data = array(
                'table_data'  => $output
            );
            // send data to ajax request
            echo json_encode($data);
        }
    }
    
    public function orderHistory() 
    {
        $ye = date('Y')+1;
        $m = date('m');
        $y = date('Y');
        
        $years = [];
        for ($year=2017; $year <= $ye; $year++) $years[] = $year;
        
        $order = DB::table('orders')
                    ->Join('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.o_status','=','9')
                    ->where('orders.active','=','1')
                    ->orderBy('orders.delivery_date', 'asc')
                    ->paginate(30);
        
        $invoice = Invoice::all();
        
        return view('admin/order_history',compact('order','invoice','y','m','years'));
    }
    
    public function filterHistory(Request $request) 
    {
        $data = $request->all();
       
        $m = $data['month'];
        $y = $data['years'];
        
        $ye = date('Y')+1;
        
        $years = [];
        for ($year=2017; $year <= $ye; $year++) { $years[] = $year ;}
        
        $order = DB::table('orders')
                    ->Join('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.o_status','=','9')
                    ->where('orders.active','=','1')
                    ->whereMonth('orders.delivery_date','=',$m)
                    ->whereYear('orders.delivery_date','=',$y)
                    ->orderBy('orders.delivery_date', 'asc')
                    ->paginate(30);
        
        $invoice = Invoice::all();
        
        return view('admin/order_history',compact('order','invoice','m','y','years'));
    }
    
    //manage stock dropdown
    public function stockList() 
    {
        $material = Material::selectRaw('*')
                ->where('m_status','=',1)                
                ->get();
        
        $stocks = Stock::selectRaw('*')
                ->where('st_status','=',1)                
                ->get();
        
        return view('admin/stock_list', compact('material','stocks'));
    }
    
    //invoice and receipt dropdown
    public function invoiceList() 
    {
        $ye = date('Y')+1;
        $m = date('m');
        $y = date('Y');
        
        $years = [];
        for ($year=2017; $year <= $ye; $year++) $years[] = $year;
        $invs = Invoice::all();
        $invoice = DB::table('invoice')
                    ->leftJoin('orders', 'invoice.o_id', '=', 'orders.o_id')
                    ->leftJoin('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.active','=','1')
                    ->paginate(30);        
        
        
        return view('admin/invoice_list',compact('invoice','y','m','years', 'invs'));
    }
    
    public function invoiceFilter(Request $request) 
    {
        $data = $request->all();
       
        $m = $data['month'];
        $y = $data['years'];
        
        $ye = date('Y')+1;
        
        $years = [];
        for ($year=2017; $year <= $ye; $year++) { $years[] = $year ;}
        
        $invoice = DB::table('invoice')
                    ->leftJoin('orders', 'invoice.o_id', '=', 'orders.o_id')
                    ->leftJoin('user', 'orders.u_id_customer', '=', 'user.u_id')
                    ->where('orders.active','=','1')
                    ->whereMonth('invoice.created_at','=',$m)
                    ->whereYear('invoice.created_at','=',$y)
                    ->paginate(30);        
        
        
        return view('admin/invoice_list',compact('invoice','y','m','years'));
    }
    
    public function invoicePending() 
    {
        return \View::make('admin/invoice_pending');
    }
    
    public function receiptList() 
    {
        return \View::make('admin/receipt_list');
    }
    
    public function receiptPending() 
    {
        return \View::make('admin/receipt_pending');
    }
    //sale
    public function sale() 
    {
        return \View::make('admin/sale');
    }
    //profile
    public function adminProfile() 
    {
       // Get the currently authenticated user's ID...
       $admin = Auth::user();
       return view('admin/admin_profile', compact('admin'));

    }
   
    //update profile
    public function updateProfile(Request $request,  $id) 
    {
     
       $admin = User::find($id);
       $admin->u_fullname = $request->input('name');
       $admin->email = $request->input('email');
       $admin->phone = $request->input('phone');
       $admin->address = $request->input('address');      
       $admin->save();
       
       return back()->with('success','Your profile updated successfully');

    }

    //display admin password form
    public function adminChangePassword () 
    {
       
       return view('admin/change_password');
      
    }

     //change admin password
    public function updateChangePassword (Request $request) 
    {       
//        $this->validate($request, [     
//            'old_password'     => 'required',
//            'new_password'     => 'required|min:8',
//            'confirm_password' => 'required|same:new_password',     
//        ]);
        $data = $request->all();
        
        $id = Auth::id();
        $admin = User::find($id);
        
        if(!Hash::check($data['old_password'], $admin->password))
        {
            return back()->with('error','Your current password is incorrect!');              
        }
        elseif($data['new_password'] <> $data['confirm_password'])
        {
            return back()->with('error','New password and confirm password did not match!');
        }
        else 
        {       
            $admin->password = Hash::make($request->input('new_password'));
            $admin->save();
            
            return back()->with('success','Your new password updated successfully');
        }      
    
    }   
    
    public function pricing() 
    {
        $body = Body::selectRaw('*')
                ->where('b_status','=',1)
                ->get();

        $sleeve = Sleeve::selectRaw('*')
                ->get();
        
        return view('admin/pricing', compact('body','sleeve'));
    }

    // method to view new order page
    public function neworder() 
    {

        // get data from tables
        $materials = Material::where('m_status', 1)->get();
        $deliverysettings = DeliverySetting::select('min_day')->first();
        $blockdays = BlockDay::select('day')->where('bd_status', 1)->get();
        $blockdates = BlockDate::select('date')->get();
        $bodies = Body::where('b_status', 1)->get();
        $sleeves = Sleeve::where('sl_status', 1)->get();
        $necks = Neck::where('n_status', 1)->get();
        $prices = Price::all();
        $users = User::where('u_status', 1)->where(function($query) {
            $query->where('u_type', 6)->orWhere('u_type', 7)->orWhere('u_type', 8)->orWhere('u_type', 9);
        })->get();
        $designers = User::where('u_status', 1)->where('u_type', 3)->get();
        // return view with all the data from the tables
        return view('admin/neworder')
        ->with('materials', $materials)
        ->with('deliverysettings', $deliverysettings)
        ->with('blockdays', $blockdays)
        ->with('blockdates', $blockdates)
        ->with('bodies', $bodies)
        ->with('sleeves', $sleeves)
        ->with('necks', $necks)
        ->with('prices', $prices)
        ->with('users', $users)
        ->with('designers', $designers);

    }
    
    public function updateOrder($oid) 
    {
        $materials = Material::where('m_status', 1)->get();
        $deliverysettings = DeliverySetting::select('min_day')->first();
        $blockdays = BlockDay::select('day')->where('bd_status', 1)->get();
        $blockdates = BlockDate::select('date')->get();
        $bodies = Body::where('b_status', 1)->get();
        $sleeves = Sleeve::where('sl_status', 1)->get();
        $necks = Neck::where('n_status', 1)->get();
        $prices = Price::all();
        $users = User::where('u_status', 1)->where(function($query) {
            $query->where('u_type', 6)->orWhere('u_type', 7)->orWhere('u_type', 8)->orWhere('u_type', 9);
        })->get();
        $designers = User::where('u_status', 1)->where('u_type', 3)->get();

        $custorders = Order::find($oid);
        $custunames = User::where('u_id', $custorders->u_id_customer)->get();
        $designs = Design::where('o_id', $oid)->get();

        $specorders = Spec::where('o_id', $oid)->get();
        $unitorders = Unit::where('o_id', $oid)->get();
        $custorderid = $oid;
        

        //var_dump($unitorders->size);

        return view('admin/update_order')    
            ->with('materials', $materials)
            ->with('deliverysettings', $deliverysettings)
            ->with('blockdays', $blockdays)
            ->with('blockdates', $blockdates)
            ->with('bodies', $bodies)
            ->with('sleeves', $sleeves)
            ->with('necks', $necks)
            ->with('prices', $prices)
            ->with('users', $users)
            ->with('designers', $designers)
            ->with('custunames', $custunames)
            ->with('custorders', $custorders)
            ->with('designs', $designs)
            ->with('specorders', $specorders)
            ->with('unitorders', $unitorders)
            ->with('custorderid', $custorderid);  

    }
    
   
}
