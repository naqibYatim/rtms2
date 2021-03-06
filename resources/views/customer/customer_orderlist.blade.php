@extends('layouts.layout')

@section('content')
<style>
    table {
  table-layout:fixed;
}
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Order List <div class="float-right"><a href="{{ route('customer.home') }}"><button class="btn-sm">Add Order</button></a></div></div>

                <div class="card-body">
                    @if(session()->has('message'))
                        <div class="alert alert-success">
                            {{ session()->get('message') }}
                        </div>
                    @endif

                    <div class="panel-body">

                        @if(!$ordersdraft->isempty())

                            <table class="table table-hover">
                                <thead class="thead-dark">
                                  <tr>
                                    <th scope="col" width="5%">No</th>
                                    <th scope="col" >Cloth Name</th>
                                    <th scope="col" width="8%">Quantity</th>
                                    <th scope="col" width="30%">Note</th>
                                    <th scope="col" width="10%">Delivery Date</th>
                                    <th scope="col" width="8%">Payment Balance</th>
                                    <th scope="col" width="10%">Status</th>
                                    <th scope="col" width="10%">Action</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    <?php $no=1; ?>
                                    @foreach($ordersdraft as $singleorderrow)
                                        <tr>

                                            
                                            <th scope="row"><?php echo $no; ?></th>
                                            <td>{{$singleorderrow->file_name}}</td>
                                            <td>{{$singleorderrow->quantity_total}}</td>
                                            <td>{{$singleorderrow->note}}</td>
                                            <td>{{date('d/m/Y', strtotime($singleorderrow->delivery_date))}}</td>
                                            <td>RM {{$singleorderrow->balance}}</td>
                                            <td>
                                                @if($singleorderrow->o_status==1)
                                                    Waiting for design
                                                 @endif
                                                 @if($singleorderrow->o_status==2)
                                                    Order Submitted
                                                 @endif
                                                 @if($singleorderrow->o_status==3)
                                                    Designed
                                                 @endif
                                                 @if($singleorderrow->o_status==4)
                                                    Printing
                                                 @endif
                                                 @if($singleorderrow->o_status==5)
                                                    Waiting for Tailor
                                                 @endif
                                                 @if($singleorderrow->o_status==6)
                                                    Sewing
                                                 @endif
                                                 @if($singleorderrow->o_status==7)
                                                    Deliver
                                                 @endif
                                                 @if($singleorderrow->o_status==8)
                                                    Reprint
                                                 @endif
                                                 @if($singleorderrow->o_status==9)
                                                    Completed
                                                 @endif
                                                 @if($singleorderrow->o_status==10)
                                                    Customer Request Design
                                                 @endif
                                                 @if($singleorderrow->o_status==0)
                                                    Draft
                                                 @endif    
                                                 
                                            </td>
                                            <td><a href="{{route('general.joborder',$singleorderrow->o_id)}}" target="_blank">View Job Order</a></td>
                                        </tr>
                                        <?php $no++; ?>
                                  @endforeach
                                  
                                </tbody>
                            </table>
                            
                        @else
                            <p>No order found</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
    form {    
        display: inline;
    }
</style>