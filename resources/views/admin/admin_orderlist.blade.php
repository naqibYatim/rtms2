@extends('layouts.layout')

@section('content')
<style>
    form, form formbutton { display: inline; }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><i class="fa fa-list"></i> Order List <div class="float-right">Total Orders : {{$order->count()}}</div></div>

                <div class="card-body">
                    @if(session()->has('message'))
                        <div class="alert alert-success">
                            {{ session()->get('message') }}
                        </div><br>
                    @endif               
                    <div class="row">
                        <div class="col-md-12">
                            <form action="{{ route('admin.filterorder') }}" method="post">
                                {{ csrf_field() }}
                            <select name="month" class="form-control-sm" id="bulan">
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>

                            <select name="years" id="tahun" class="form-control-sm">
                                @foreach($years as $year)
                                <option value="{{$year}}">{{$year}}</option>
                                @endforeach
                            </select>                       
                            <button class="btn-sm" type="submit" >Filter</button>
                            </form>
                            <a href="{{ route('admin.orderlist') }}"><button class="btn-sm" >Reset</button></a>
                            <input type="text" name="search" id="search" style="float: right" placeholder=" Search order list data" />
                            <br>
                        </div>
                    </div><br>
                    
                    @if(!$order->isempty())
                        <table class="table table-hover">
                            <thead class="thead-dark">
                              <tr>
                                <th scope="col">No</th>
                                <th scope="col">Ref No</th>
                                <th scope="col">Created Date</th>
                                <th scope="col">Customer Name</th>
                                <th scope="col">File name</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Delivery Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                              </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($order as $ord)
                              <tr>
                                <td>{{$no}}</td>
                                <th scope="row">{{$ord->ref_num}}</th>
                                <td>{{date('d/m/Y', strtotime($date->where('o_id',$ord->o_id)->pluck('created_at')->first()))}}</td>
                                <td>{{$ord->u_fullname}}</td>
                                <td>{{$ord->file_name}}</td>
                                <td>{{$ord->quantity_total}}</td>
                                <td>{{date('d/m/Y', strtotime($ord->delivery_date))}}</td>
                                <td>
                                        @if($ord->o_status==1)
                                           Waiting for design
                                        @endif
                                        @if($ord->o_status==2)
                                           Order Confirm
                                        @endif
                                        @if($ord->o_status==3)
                                           Design Confirm
                                        @endif
                                        @if($ord->o_status==4)
                                           Printing
                                        @endif
                                        @if($ord->o_status==5)
                                           Waiting for Tailor
                                        @endif
                                        @if($ord->o_status==6)
                                           Sewing
                                        @endif
                                        @if($ord->o_status==7)
                                           Deliver
                                        @endif
                                        @if($ord->o_status==8)
                                           Reprint
                                        @endif
                                        @if($ord->o_status==9)
                                           Completed
                                        @endif
                                        @if($ord->o_status==10)
                                           Customer Request Design
                                        @endif
                                        @if($ord->o_status==0)
                                           Draft
                                        @endif
                                </td>
                                <td>
                                    <a href="{{route('admin.updateorder',$ord->o_id)}}"><button ><i class="fa fa-edit"></i></button></a>
                                
                                    <form class="formbutton" action="{{route('admin.deleteorder')}}" method="POST">{{ csrf_field() }}
                                        <button  type="submit" onclick="return confirm('Are you sure to delete this order?')" ><i class="fa fa-trash"></i></button>
                                        <input type="hidden" name="o_id" value=" {{$ord->o_id}}">                                 
                                    </form>
                                    
                                    <a href="{{route('general.joborder',$ord->o_id)}}"><button >Job Order</button></a>
                                </td>
                              </tr>
                              @php $no ++; @endphp
                              @endforeach
                              {{ $order->links() }}
                            </tbody>
                          </table>
                    @else
                    No Order
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function () {

    $("#bulan").val( '{{$m}}' );
    $("#tahun").val( '{{$y}}' );

    fetch_customer_data();

    // function to return data if search input has value in it
    function fetch_customer_data(query = '')
    {
        $.ajax({
            url:"{{ route('admin.searchOrder') }}",
            method:'GET',
            data:{query:query},
            dataType:'json',
            success:function(data)
        {
            $('tbody').html(data.table_data);
        }
        })
    }

    $(document).on('keyup', '#search', function(){
        var query = $(this).val();
        fetch_customer_data(query);
    });

});    
</script>
@endsection
