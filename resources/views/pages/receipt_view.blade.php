@extends ('layouts.master')
@section('title')
    View Money Receipt
@endsection
@section('custom_css')
    <style  media="print">

        @media print
        {
            @page
            {
                margin: 12px;
            }
        }

    </style>
@endsection

@section('content')
        <div class="row">
            <div class="col-md-12">
                <div class="card-box">

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-12">
                            <div class="pull-left">
                                @if(file_exists("assets/images/".$setting['logo']))
                                    <img src="{{ asset("assets/images/".$setting['logo']) }}" height="50px">
                                    {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                                @else
                                    <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                                    {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                                @endif
                            </div>
                            <div class="pull-right">
                                <h4>Receipt # <br>
                                    <strong>RCPT-{{sprintf('%06d',$payment->id)}}</strong>
                                </h4>
                            </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">

                                <div class="pull-left m-t-5">
                                    <address>
                                        Client Name: <strong>{{ $payment->client->client_name }}</strong><br>
                                        Username: <strong>{{ $payment->client->username }}</strong><br>
                                        {{ $payment->client->address }}</br>
                                        <abbr title="Phone">Phone:</abbr> {{ $payment->client->phone }}
                                    </address>
                                </div>
                                <div class="pull-right m-t-5 text-right">
                                    <p><strong>Payment Date: </strong> {{ $payment->created_at->format('d M Y') }}</p>
                                    <strong>{!! $setting['address'] !!}</strong><br>
                                    Phone: <strong>{{ $setting['phone'] }}</strong><br>
                                </div>
                            </div><!-- end col -->
                        </div>
                        <!-- end row -->



                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table m-t-15 table-sm">
                                        <thead>
                                        <tr><th>#</th>
                                            <th>Item Name</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                        </tr></thead>
                                        <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Monthly Rent</td>
                                            <td>
                                                @if($payment->plan_price >0)
                                                    {{$payment->bandwidth}} - {{date('F', mktime(0, 0, 0, $payment['bill_month'], 10)).'/'.$payment['bill_year']}}
                                                @else
                                                    -
                                                @endif

                                            </td>
                                            <td class="text-right">{{ $payment->plan_price }}</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Installation Charge</td>
                                            <td>-</td>
                                            <td class="text-right">{{ $payment->service_charge }}</td>
                                        </tr>

                                        <tr>
                                            <td>3</td>
                                            <td>Advance Payment</td>
                                            <td>-</td>
                                            <td class="text-right">{{ $payment->advance_payment }}</td>
                                        </tr>

                                        <tr>
                                            <td>4</td>
                                            <td>Previous Due</td>
                                            <td>-</td>
                                            <td class="text-right">{{ $payment->pre_due }}</td>
                                        </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-5 col-6">
                                <div class="clearfix">
                                    <div class="table-borderless table-sm" style="border: #0b0b0b 1px solid">
                                        <table class="table">
                                            <tr>
                                                <td class="text-right"><b>Client's Previous Balance:</b></td>
                                                <td class="text-right">{{ number_format(($payment->client->balance - $payment->advance_payment),2,'.','') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Paid From Advance:</b></td>
                                                <td class="text-right">{{ $payment->paid_from_advance }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Advance Paid:</b></td>
                                                <td class="text-right">{{ number_format($payment->advance_payment,2,'.','') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Client's Balance:</b></td>
                                                <td class="text-right"><b>{{ $payment->client->balance }}</b></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Client's Due (With Vat):</b></td>
                                                <td class="text-right"><b>{{ $payment->due }}</b></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-6 offset-xl-4">
                                <div class="table-borderless table-sm">
                                    <table class="table">
                                        <tr>
                                            <td class="text-right"><b>Total:</b></td>
                                            <td class="text-right">{{ $payment->total }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>Discount:</b></td>
                                            <td class="text-right">{{ $payment->discount }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>Sub Total:</b></td>
                                            <td class="text-right">{{ $payment->all_total }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>VAT:</b></td>
                                            <td class="text-right">{{ $payment->vat }}</td>
                                        </tr>

                                        <tr>
                                            <td class="text-right"><b>Grand Total:</b></td>
                                            <td class="text-right">{{ $payment->sub_total }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>Paid Amount:</b></td>
                                            <td class="text-right">{{ $payment->paid_amount }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>Due Amount (With Vat):</b></td>
                                            <td class="text-right">{{ $payment->due }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-6 col-6">
                                </br></br>
                                _________________________________ <br>
                                <strong>Customer Signature</strong> </br>

                            </div>

                            <div class="col-xl-3 col-6 offset-xl-3 text-right">
                                </br></br>
                                __________________________________ <br>
                                <strong>Authorized Signature & Co. Stamp</strong> </br>
                            </div>
                        </div>
                        <hr>
                        <div class="d-print-none">
                            <div class="pull-right">
                                <a href="javascript:window.print()" class="btn btn-light waves-effect waves-light"><i class="fa fa-print"></i></a>
                                <a href="{{ route('receipt.index') }}" class="btn btn-primary waves-effect waves-light">Back</a>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>                    <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->
        </div>
        <!-- end row -->


        {{--<div class="row receipt-view">--}}
            {{--<div class="col-md-12">--}}
                {{--<div class="card-box">--}}

                    {{--<div class="panel-body">--}}
                        {{--<div class="row">--}}
                            {{--<div class="col-12">--}}
                                {{--<div class="pull-left">--}}
                                    {{--<img src="{{ Storage::url($setting['logo']) }}" width="70px"> <span class="logo">{{ $setting['companyName'] }}</span>--}}
                                {{--</div>--}}
                                {{--<div class="pull-right">--}}
                                    {{--<h4>Receipt # <br>--}}
                                        {{--<strong>RCPT-{{sprintf('%06d',$payment->id)}}</strong>--}}
                                    {{--</h4>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<hr>--}}
                        {{--<div class="row">--}}
                            {{--<div class="col-12">--}}

                                {{--<div class="pull-left m-t-5">--}}
                                    {{--<address>--}}
                                        {{--Client Name: <strong>{{ $payment->client->client_name }}</strong><br>--}}
                                        {{--Username: <strong>{{ $payment->client->username }}</strong><br>--}}
                                        {{--{{ $payment->client->address }}</br>--}}
                                        {{--<abbr title="Phone">Phone:</abbr> {{ $payment->client->phone }}--}}
                                    {{--</address>--}}
                                {{--</div>--}}
                                {{--<div class="pull-right m-t-5 text-right">--}}
                                    {{--<p><strong>Payment Date: </strong> {{ $payment->created_at->format('d M Y') }}</p>--}}
                                    {{--<strong>{!! $setting['address'] !!}</strong><br>--}}
                                    {{--Phone: <strong>{{ $setting['phone'] }}</strong><br>--}}
                                {{--</div>--}}
                            {{--</div><!-- end col -->--}}
                        {{--</div>--}}
                        {{--<!-- end row -->--}}



                        {{--<div class="row">--}}
                            {{--<div class="col-md-12">--}}
                                {{--<div class="table-responsive">--}}
                                    {{--<table class="table m-t-15 table-sm">--}}
                                        {{--<thead>--}}
                                        {{--<tr><th>#</th>--}}
                                            {{--<th>Item Name</th>--}}
                                            {{--<th>Description</th>--}}
                                            {{--<th>Amount</th>--}}
                                        {{--</tr></thead>--}}
                                        {{--<tbody>--}}
                                        {{--<tr>--}}
                                            {{--<td>1</td>--}}
                                            {{--<td>Monthly Rent</td>--}}
                                            {{--<td>{{$payment->bandwidth}} - {{date('F', mktime(0, 0, 0, $payment['bill_month'], 10)).'/'.$payment['bill_year']}}</td>--}}
                                            {{--<td class="text-right">{{ $payment->plan_price }}</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                            {{--<td>2</td>--}}
                                            {{--<td>Installation Charge</td>--}}
                                            {{--<td>-</td>--}}
                                            {{--<td class="text-right">{{ $payment->service_charge }}</td>--}}
                                        {{--</tr>--}}

                                        {{--<tr>--}}
                                            {{--<td>2</td>--}}
                                            {{--<td>Advance Payment</td>--}}
                                            {{--<td>-</td>--}}
                                            {{--<td class="text-right">{{ $payment->advance_payment }}</td>--}}
                                        {{--</tr>--}}

                                        {{--<tr>--}}
                                            {{--<td>2</td>--}}
                                            {{--<td>Previous Due</td>--}}
                                            {{--<td>-</td>--}}
                                            {{--<td class="text-right">{{ $payment->pre_due }}</td>--}}
                                        {{--</tr>--}}

                                        {{--</tbody>--}}
                                    {{--</table>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--<div class="row">--}}
                            {{--<div class="col-xl-5 col-6">--}}
                                {{--<div class="clearfix">--}}
                                    {{--<div class="table-borderless table-sm" style="border: #0b0b0b 1px solid">--}}
                                        {{--<table class="table">--}}
                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>Client's Previous Balance:</b></td>--}}
                                                {{--@if($payment->paid_amount > $payment->sub_total)--}}
                                                    {{--@php($today_ad_paid = $payment->paid_amount - $payment->sub_total)--}}
                                                {{--@else--}}
                                                    {{--@php($today_ad_paid = 0.00)--}}
                                                {{--@endif--}}
                                                {{--<td class="text-right">{{ number_format((($payment->client->balance + $payment->paid_from_advance) - $today_ad_paid),2,'.','') }}</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>Paid From Advance:</b></td>--}}
                                                {{--<td class="text-right">{{ $payment->paid_from_advance }}</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>Advance Paid:</b></td>--}}
                                                {{--<td class="text-right">{{ number_format($today_ad_paid,2,'.','') }}</td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>Client's Balance:</b></td>--}}
                                                {{--<td class="text-right"><b>{{ $payment->client->balance }}</b></td>--}}
                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>Client's Due:</b></td>--}}
                                                {{--<td class="text-right"><b>{{ $payment->due }}</b></td>--}}
                                            {{--</tr>--}}
                                        {{--</table>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<div class="col-xl-3 col-6 offset-xl-4">--}}
                                {{--<div class="table-borderless table-sm">--}}
                                    {{--<table class="table">--}}
                                        {{--<tr>--}}
                                            {{--<td class="text-right"><b>Total:</b></td>--}}
                                            {{--<td class="text-right">{{ $payment->total }}</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                            {{--<td class="text-right"><b>Discount:</b></td>--}}
                                            {{--<td class="text-right">{{ $payment->discount }}</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                            {{--<td class="text-right"><b>Sub Total:</b></td>--}}
                                            {{--<td class="text-right">{{ $payment->all_total }}</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                            {{--<td class="text-right"><b>VAT:</b></td>--}}
                                            {{--<td class="text-right">{{ $payment->vat }}</td>--}}
                                        {{--</tr>--}}

                                        {{--<tr>--}}
                                            {{--<td class="text-right"><b>Grand Total:</b></td>--}}
                                            {{--<td class="text-right">{{ $payment->sub_total }}</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                            {{--<td class="text-right"><b>Paid Amount:</b></td>--}}
                                            {{--<td class="text-right">{{ $payment->paid_amount }}</td>--}}
                                        {{--</tr>--}}
                                        {{--<tr>--}}
                                            {{--<td class="text-right"><b>Due Amount:</b></td>--}}
                                            {{--<td class="text-right">{{ $payment->due }}</td>--}}
                                        {{--</tr>--}}
                                    {{--</table>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div>--}}

                        {{--<div class="row">--}}
                            {{--<div class="col-xl-6 col-6">--}}
                                {{--</br></br>--}}
                                {{--_________________________________ <br>--}}
                                {{--<strong>Customer Signature</strong> </br>--}}

                            {{--</div>--}}

                            {{--<div class="col-xl-3 col-6 offset-xl-3 text-right">--}}
                                {{--</br></br>--}}
                                {{--__________________________________ <br>--}}
                                {{--<strong>Authorized Signature & Co. Stamp</strong> </br>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>                    <!-- end row -->--}}

                {{--</div> <!-- end card-box -->--}}
            {{--</div><!-- end col -->--}}
        {{--</div>--}}
@endsection
