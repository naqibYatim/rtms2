@extends('layouts.layout')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Invoice Page</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif 

                    @if(!$invoice->isempty())
                        <table class="table table-hover">
                            <thead class="thead-dark">
                              <tr>
                                <th scope="col">Ref Num</th>
                                <th scope="col">File Name</th>
                                <th scope="col">Delivery</th>
                                <th scope="col">Total Quantity</th>
                                <th scope="col">Total Price</th>
                                <th scope="col">Created Date</th>
                                <th scope="col">View</th>
                                <th scope="col">Action</th>
                              </tr>
                            </thead>
                            <tbody>          
                              <?php $totpayments = 0; $orderids = array(); $prices = array(); ?>                     
                                @foreach($invoice as $inv)
                              <tr>
                                <td>{{$inv->ref_num}}</td>
                                <td>{{$inv->file_name}}</td>
                                <td>{{$inv->delivery_type}}</td>
                                <td>{{$inv->quantity_total}}</td>
                                <td>{{$inv->total_price}}</td>
                                <td>
                                  @php
                                      $invdate = $invs->where('i_id', $inv->i_id)->pluck('created_at');
                                  @endphp
                                  {{date('d/m/Y', strtotime($invdate[0]))}}
                                </td>
                                <td><a href="{{route('general.invoice',$inv->o_id)}}"><button class="btn btn-primary">View</button></a></td>
                                {!! Form::open(array( 'route'=>'customer.payorder', 'method' => 'POST')) !!}
                                  <input type="hidden" name="price" id="price" value="{{$inv->total_price}}">
                                  <input type="hidden" name="oid" id="oid" value="{{$inv->o_id}}">
                                  <td>
                                    @if($inv->balance == 0)
                                      <button class="btn btn-primary" disabled>Pay</button>
                                    @else
                                    <?php $totpayments += $inv->balance; array_push($orderids,$inv->o_id); array_push($prices,$inv->total_price); ?> 
                                      <button class="btn btn-primary">Pay</button>
                                    @endif 
                                  </td>
                                {!! Form::close() !!} 
                              </tr>
                              @endforeach
                              <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align:right"><b>Total:</b></td>
                                <td><b>{{$totpayments}}</b></td>
                                <td></td>
                                <td></td>
                                {!! Form::open(array( 'route'=>'customer.payorder', 'method' => 'POST')) !!}
                                  <input type="hidden" name="price" id="price" value="{{$totpayments}}">
                                  @foreach($orderids as $oid)
                                    <input type="hidden" name="orderids[]" value="{{$oid}}">
                                  @endforeach
                                  @foreach($prices as $price)
                                    <input type="hidden" name="prices[]" value="{{$price}}">
                                  @endforeach
                                  <td>
                                    @if($totpayments == 0)
                                      <button class="btn btn-primary" disabled>Pay</button>
                                    @else
                                      <button class="btn btn-primary">Pay</button>
                                    @endif 
                                  </td>
                                {!! Form::close() !!} 
                              </tr>
                              {{ $invoice->links() }}
                            </tbody>
                          </table>                    
                    
                    
                    @else
                    No invoice
                    @endif 
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
