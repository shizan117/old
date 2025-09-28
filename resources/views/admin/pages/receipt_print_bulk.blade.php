@extends ('admin.layouts.master')
@section('title')
    View Money Receipt
@endsection
@section('custom_css')
    <style  media="print">
        @media print {
            @page {
                margin: 12px;
            }
            .p-break{
                page-break-after: always;
            }
        }
    </style>
@endsection

@section('content')
    @foreach($receipts as $receipt)

        <?php
            $address = $setting['address'];
            $phone = $setting['phone'];
            $logo = $setting['logo'];
            $signature = $setting['company_signature'];
            $companyName = $setting['companyName'];
            if($receipt->client->resellerId != null){
                $address = $receipt->client->reseller->resellerLocation;
                $phone = $receipt->client->reseller->phone;
                $logo = $receipt->client->reseller->logo;
                $signature = $receipt->client->reseller->signature;
                $companyName = $receipt->client->reseller->business_name;
            }
        ?>

        @if($setting['receipt_print_type']=='regular')
            <div class="row" style="line-height: 1.2;">
                <div class="col-md-12">
                    <div class="card-box">

                        <div class="panel-body">
                            <div class="clearfix">
                                <div class="row">
                                    <div class="col-4">
                                        @if(file_exists("assets/images/".$logo))
                                            <img src="{{ asset("assets/images/".$logo) }}" height="50px">
                                            {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                                        @else
                                            <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                                            {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                                        @endif
                                    </div>
                                    <div class="col-4 text-center">
                                        {{--<div class="text-center"></div>--}}
                                        <span class="btn btn-outline-secondary" style="margin-top: 10px;font-size: 22px;">Office Copy</span>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-right">
                                            <h4>Money Receipt #<br>
                                                <strong>RCPT-{{sprintf('%06d',$receipt->id)}}</strong>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-0">

                                    <div class="pull-left m-t-20 table-borderless table-sm">
                                        <address>
                                            <table class="table">
                                                <tr>
                                                    <td>Client Name:</td>
                                                    <th>{{ $receipt->client->client_name }}</th>
                                                </tr>
                                                <tr>
                                                    <td>Username:</td>
                                                    <th>{{ $receipt->client->username }}</th>
                                                </tr>
                                                <tr>
                                                    <td>Address:</td>
                                                    @php(($receipt->client->house_no == null)?$house_no='':$house_no='House:'.$receipt->client->house_no)
                                                    @php(($receipt->client->road_no == null)?$road_no='':$road_no='Road:'.$receipt->client->road_no)

                                                    <th>
                                                        @if($house_no != '' && $road_no != '')
                                                            {{ $house_no.', '.$road_no.', ' }}
                                                        @else
                                                            @if($house_no != '')
                                                                {{ $house_no.', ' }}
                                                            @endif
                                                            @if($road_no != '')
                                                                {{ $road_no.', ' }}
                                                            @endif
                                                        @endif
                                                        {{ $receipt->client->address }}
                                                        <br>
                                                        {{ $receipt->client->thana.', '.$receipt->client->district }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <td>Phone:</td>
                                                    <th>{{ $receipt->client->phone }}</th>
                                                </tr>
                                            </table>
                                        </address>
                                    </div>
                                    <div class="pull-right m-t-20 text-right">
                                        <p>
                                            Payment Date: <strong>{{ date('d-M-y',strtotime($receipt->payment_date)) }}</strong><br>
                                            {{-- Payment Method: <strong>{{ ($receipt->transaction != null ) ?$receipt->transaction->account->account_name: 'Online Payment'  }}</strong> --}}
                                            Payment Method: <strong>{{ optional($receipt->transaction->account)->account_name ?? 'Online Payment' }}</strong>
                                            <br>
                                            Received By: <strong>
                                                {{ ($receipt->user != null)? $receipt->user->name:'Online Paid'  }}
                                                ({{ date('d-M-y h:ia',strtotime($receipt->created_at)) }})
                                        </strong><br>
                                        </p>
                                        <strong>{!! $address !!}</strong><br>
                                        Phone: <strong>{{ $phone }}</strong><br>
                                    </div>
                                </div><!-- end col -->
                            </div>
                            <!-- end row -->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table m-t-0 table-sm table-bordered">
                                            <thead>
                                            <tr class="text-center">
                                                <th>#</th>
                                                <th>Item Name</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="text-center">1</td>
                                                <td>Monthly Rent</td>
                                                <td class="text-center">
                                                    @if($receipt->plan_price >0)
                                                        {{$receipt->bandwidth}} - {{date('F', mktime(0, 0, 0, $receipt['bill_month'], 10)).'/'.$receipt['bill_year']}}
                                                    @else
                                                        -
                                                    @endif

                                                </td>
                                                <td class="text-right">{{ $receipt->plan_price }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">2</td>
                                                <td>Service Charge</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ $receipt->service_charge }} {{ $setting['currencyCode'] }}</td>
                                            </tr>

                                            <tr>
                                                <td class="text-center">3</td>
                                                <td>Advance Payment</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ $receipt->advance_payment }} {{ $setting['currencyCode'] }}</td>
                                            </tr>

                                            <tr>
                                                <td class="text-center">4</td>
                                                <td>Previous Due</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ $receipt->pre_due }} {{ $setting['currencyCode'] }}</td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xl-5 col-5">
                                    <div class="clearfix">
                                        <div class="table-borderless table-sm" style="border: #0b0b0b 1px solid">
                                            <table class="table">
                                                <tr>
                                                    <td class="text-right"><b>Client's Previous Balance:</b></td>
                                                    <td class="text-right">{{ number_format(($receipt->pre_balance),2,'.','') }} {{ $setting['currencyCode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Paid From Advance:</b></td>
                                                    <td class="text-right">{{ $receipt->paid_from_advance }} {{ $setting['currencyCode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Advance Paid:</b></td>
                                                    <td class="text-right">{{ number_format($receipt->advance_payment,2,'.','') }} {{ $setting['currencyCode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Client's Balance:</b></td>
                                                    <td class="text-right"><b>{{ ($receipt->pre_balance + $receipt->advance_payment) - $receipt->paid_from_advance }} {{ $setting['currencyCode'] }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Client's Due:</b></td>
                                                    <td class="text-right"><b>{{ $receipt->due }} {{ $setting['currencyCode'] }}</b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-5 col-5 offset-2 text-right offset-xl-2">
                                    <div class="table-borderless table-sm">
                                        <table class="table">
                                            <tr>
                                                <td class="text-right"><b>Total:</b></td>
                                                <td class="text-right">{{ $receipt->total }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Discount:</b></td>
                                                <td class="text-right">{{ $receipt->discount }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Sub Total:</b></td>
                                                <td class="text-right">{{ $receipt->all_total }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>VAT:</b></td>--}}
                                                {{--<td class="text-right">{{ $receipt->vat }} {{ $setting['currencyCode'] }}</td>--}}
                                            {{--</tr>--}}

                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>Grand Total:</b></td>--}}
                                                {{--<td class="text-right">{{ $receipt->sub_total }} {{ $setting['currencyCode'] }}</td>--}}
                                            {{--</tr>--}}
                                            <tr>
                                                <td class="text-right"><b>Paid Amount:</b></td>
                                                <td class="text-right">{{ $receipt->paid_amount }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Due Amount:</b></td>
                                                <td class="text-right">{{ $receipt->due }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row m-t-20">
                                <div class="col-xl-6 col-6 m-t-5">
                                    _________________________________ <br>
                                    <strong>Customer Signature</strong>

                                </div>

                                <div class="col-xl-3 col-6 offset-xl-3 text-right m-t-5">
                                    <img src="{{ asset("assets/images/".$signature) }}" alt="" width="150"
                                         style="margin-right: 80px !important">
                                    <br>__________________________________ <br>
                                    <strong>Authorized Signature & Co. Stamp</strong> <br>
                                </div>
                            </div>
                        </div> <!-- end row -->

                    </div> <!-- end card-box -->
                </div><!-- end col -->

                <div class="col-md-12 mt-2">
                    <div class="card-box">

                        <div class="panel-body">
                            <div class="clearfix">
                                <div class="row">
                                    <div class="col-4">
                                        @if(file_exists("assets/images/".$logo))
                                            <img src="{{ asset("assets/images/".$logo) }}" height="50px">
                                            {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                                        @else
                                            <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                                            {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                                        @endif
                                    </div>
                                    <div class="col-4 text-center">
                                        {{--<div class="text-center"></div>--}}
                                        <span class="btn btn-outline-secondary" style="margin-top: 10px;font-size: 22px;">Customer Copy</span>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-right">
                                            <h4>Money Receipt #<br>
                                                <strong>RCPT-{{sprintf('%06d',$receipt->id)}}</strong>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-0">

                                    <div class="pull-left m-t-20 table-borderless table-sm">
                                        <address>
                                            <table class="table">
                                                <tr>
                                                    <td>Client Name:</td>
                                                    <th>{{ $receipt->client->client_name }}</th>
                                                </tr>
                                                <tr>
                                                    <td>Username:</td>
                                                    <th>{{ $receipt->client->username }}</th>
                                                </tr>
                                                <tr>
                                                    <td>Address:</td>
                                                    @php(($receipt->client->house_no == null)?$house_no='':$house_no='House:'.$receipt->client->house_no)
                                                    @php(($receipt->client->road_no == null)?$road_no='':$road_no='Road:'.$receipt->client->road_no)

                                                    <th>
                                                        @if($house_no != '' && $road_no != '')
                                                            {{ $house_no.', '.$road_no.', ' }}
                                                        @else
                                                            @if($house_no != '')
                                                                {{ $house_no.', ' }}
                                                            @endif
                                                            @if($road_no != '')
                                                                {{ $road_no.', ' }}
                                                            @endif
                                                        @endif
                                                        {{ $receipt->client->address }}
                                                        <br>
                                                        {{ $receipt->client->thana.', '.$receipt->client->district }}
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <td>Phone:</td>
                                                    <th>{{ $receipt->client->phone }}</th>
                                                </tr>
                                            </table>
                                        </address>
                                    </div>
                                    <div class="pull-right m-t-20 text-right">
                                        <p>
                                            Payment Date: <strong>{{ date('d-M-y',strtotime($receipt->payment_date)) }}</strong><br>
                                            Payment Method: <strong>{{ ($receipt->transaction != null ) ?$receipt->transaction->account->account_name: 'Online Payment'  }}</strong><br>
                                            Received By: <strong>
                                                {{ ($receipt->user != null)? $receipt->user->name:'Online Paid'  }}
                                                ({{ date('d-M-y h:ia',strtotime($receipt->created_at)) }})
                                        </p>
                                        <strong>{!! $address !!}</strong><br>
                                        Phone: <strong>{{ $phone }}</strong><br>
                                    </div>
                                </div><!-- end col -->
                            </div>
                            <!-- end row -->

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table m-t-0 table-sm table-bordered">
                                            <thead>
                                            <tr class="text-center">
                                                <th>#</th>
                                                <th>Item Name</th>
                                                <th>Description</th>
                                                <th>Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td class="text-center">1</td>
                                                <td>Monthly Rent</td>
                                                <td class="text-center">
                                                    @if($receipt->plan_price >0)
                                                        {{$receipt->bandwidth}} - {{date('F', mktime(0, 0, 0, $receipt['bill_month'], 10)).'/'.$receipt['bill_year']}}
                                                    @else
                                                        -
                                                    @endif

                                                </td>
                                                <td class="text-right">{{ $receipt->plan_price }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">2</td>
                                                <td>Service Charge</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ $receipt->service_charge }} {{ $setting['currencyCode'] }}</td>
                                            </tr>

                                            <tr>
                                                <td class="text-center">3</td>
                                                <td>Advance Payment</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ $receipt->advance_payment }} {{ $setting['currencyCode'] }}</td>
                                            </tr>

                                            <tr>
                                                <td class="text-center">4</td>
                                                <td>Previous Due</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">{{ $receipt->pre_due }} {{ $setting['currencyCode'] }}</td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xl-5 col-5">
                                    <div class="clearfix">
                                        <div class="table-borderless table-sm" style="border: #0b0b0b 1px solid">
                                            <table class="table">
                                                <tr>
                                                    <td class="text-right"><b>Client's Previous Balance:</b></td>
                                                    <td class="text-right">{{ number_format(($receipt->pre_balance),2,'.','') }} {{ $setting['currencyCode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Paid From Advance:</b></td>
                                                    <td class="text-right">{{ $receipt->paid_from_advance }} {{ $setting['currencyCode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Advance Paid:</b></td>
                                                    <td class="text-right">{{ number_format($receipt->advance_payment,2,'.','') }} {{ $setting['currencyCode'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Client's Balance:</b></td>
                                                    <td class="text-right"><b>{{ ($receipt->pre_balance + $receipt->advance_payment) - $receipt->paid_from_advance }} {{ $setting['currencyCode'] }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><b>Client's Due:</b></td>
                                                    <td class="text-right"><b>{{ $receipt->due }} {{ $setting['currencyCode'] }}</b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-5 col-5 offset-2 text-right offset-xl-2">
                                    <div class="table-borderless table-sm">
                                        <table class="table">
                                            <tr>
                                                <td class="text-right"><b>Total:</b></td>
                                                <td class="text-right">{{ $receipt->total }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Discount:</b></td>
                                                <td class="text-right">{{ $receipt->discount }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Sub Total:</b></td>
                                                <td class="text-right">{{ $receipt->all_total }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>VAT:</b></td>--}}
                                                {{--<td class="text-right">{{ $receipt->vat }} {{ $setting['currencyCode'] }}</td>--}}
                                            {{--</tr>--}}

                                            {{--<tr>--}}
                                                {{--<td class="text-right"><b>Grand Total:</b></td>--}}
                                                {{--<td class="text-right">{{ $receipt->sub_total }} {{ $setting['currencyCode'] }}</td>--}}
                                            {{--</tr>--}}
                                            <tr>
                                                <td class="text-right"><b>Paid Amount:</b></td>
                                                <td class="text-right">{{ $receipt->paid_amount }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Due Amount:</b></td>
                                                <td class="text-right">{{ $receipt->due }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row m-t-20">
                                <div class="col-xl-6 col-6 m-t-5">
                                    _________________________________ <br>
                                    <strong>Customer Signature</strong>

                                </div>

                                <div class="col-xl-3 col-6 offset-xl-3 text-right m-t-5">
                                    <img src="{{ asset("assets/images/".$signature) }}" alt="" width="150"
                                         style="margin-right: 80px !important">
                                    <br>__________________________________ <br>
                                    <strong>Authorized Signature & Co. Stamp</strong> <br>
                                </div>
                            </div>
                        </div> <!-- end row -->

                    </div> <!-- end card-box -->
                </div><!-- end col -->
            </div>
        @else
            <div id="pos_receipt">
                <table align="center" border="1" cellspacing="0" style="width:219px; height:auto; margin:0 auto !important;">
                    <thead>
                    <tr>
                        <td colspan="3" style="text-align:left; font-family:Verdana, Geneva, sans-serif;  text-align:center; font-size:15px">
                            <h4>{{ $companyName }} </h4>
                            {!! $address !!} <br/> Phone : {{ $phone }}</td>
                    </tr>
                    <tr><td colspan="3" style="text-align:center; font-size: 15px; font-weight: 700; ">Money Receipt</td></tr>
                    <tr>
                        <td style="font-size: 15px;" colspan="3">Receipt No: {{ "RCPT-".sprintf('%06d',$receipt->id) }}</td>
                    </tr>
                    <tr style="font-size: 15px;">
                        <td colspan="3">
                            Client Name: {{ $receipt->client->client_name }}
                        </td>
                    </tr>
                    <tr style="font-size: 15px;">
                        <td colspan="3">
                            Client ID: {{ $receipt->client->username }}
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="item-info">
                        <td colspan="3" valign="top">
                            <table align="left" width="100%" border="0" cellpadding="2" cellspacing="1" style="font-size:10px">
                                <tr><td width="9%" height="10"></td>
                                </tr>
                                <tr class="invoice_print" style="color:#000; font-weight:bold; font-size:12px;text-align: center;">
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>

                                <tr>
                                    <td class="text-center">1</td>
                                    <td>Monthly Rent</td>
                                    <td class="text-center">
                                        @if($receipt->plan_price >0)
                                            {{$receipt->bandwidth}} - {{date('F', mktime(0, 0, 0, $receipt['bill_month'], 10)).'/'.$receipt['bill_year']}}
                                        @else
                                            -
                                        @endif

                                    </td>
                                    <td class="text-right">{{ $receipt->plan_price }} {{ $setting['currencyCode'] }}</td>
                                </tr>
                                <tr>
                                    <td class="text-center">2</td>
                                    <td>Service Charge</td>
                                    <td class="text-center">-</td>
                                    <td class="text-right">{{ $receipt->service_charge }} {{ $setting['currencyCode'] }}</td>
                                </tr>

                                <tr>
                                    <td class="text-center">3</td>
                                    <td>Advance Payment</td>
                                    <td class="text-center">-</td>
                                    <td class="text-right">{{ $receipt->advance_payment }} {{ $setting['currencyCode'] }}</td>
                                </tr>

                                <tr>
                                    <td class="text-center">4</td>
                                    <td>Previous Due</td>
                                    <td class="text-center">-</td>
                                    <td class="text-right">{{ $receipt->pre_due }} {{ $setting['currencyCode'] }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr class="text-right">
                        <td colspan="2">Total: </td>
                        <td>{{ $receipt->total }} {{ $setting['currencyCode'] }}</td>
                    </tr>
                    <tr class="text-right">
                        <td colspan="2">Discount(-): </td>
                        <td>{{ $receipt->discount }} {{ $setting['currencyCode'] }}</td>
                    </tr>
                    {{--<tr class="text-right">--}}
                        {{--<td colspan="2">VAT(+) : </td>--}}
                        {{--<td>{{ $receipt->vat }} {{ $setting['currencyCode'] }}</td>--}}
                    {{--</tr>--}}
                    <tr class="text-right">
                        <td colspan="2">Grand Total: </td>
                        <td>{{ $receipt->sub_total }} {{ $setting['currencyCode'] }}</td>
                    </tr>
                    <tr class="text-right">
                        <td colspan="2">Paid Amount : </td>
                        <td>{{ $receipt->paid_amount }} {{ $setting['currencyCode'] }}</td>
                    </tr>
                    <tr class="text-right">
                        <td colspan="2">Due Amount : </td>
                        <td>{{ $receipt->due }} {{ $setting['currencyCode'] }}</td>
                    </tr>
                    {{--<tr v-if="print_html.sales.due_collect>0" class="text-right">--}}
                    {{--<td colspan="2">Due Collect : </td>--}}
                    {{--<td>@{{ print_html.sales.due_collect }}/-</td>--}}
                    {{--</tr>--}}
                    {{--<tr class="text-right">--}}
                    {{--<td colspan="2">Net Payable : </td>--}}
                    {{--<td> @{{ parseFloat(print_html.sales.total_amount-print_html.sales.discount_amount)+parseFloat(print_html.sales.tax_amount)+parseFloat(print_html.sales.extra_charge)+parseFloat(print_html.customer.pre_due??0) }}/-</td>--}}
                    {{--</tr>--}}
                    {{--<tr class="text-right">--}}
                    {{--<td colspan="2">Change Amount : </td>--}}
                    {{--<td>@{{ print_html.sales.refund }}/-</td>--}}
                    {{--</tr>--}}
                    {{--<tr class="text-right">--}}
                    {{--<td colspan="2">Mode of Payment : </td>--}}
                    {{--<td>@{{ print_html.sales.gateway }}</td>--}}
                    {{--</tr>--}}
                    {{--<tr>--}}
                    {{--<td valign="top" colspan="3" style="padding-top: 10px; text-align: center;font-size: 13px;">--}}
                    {{--No exchange after use/No cashback<br>--}}
                    {{--Note: Physical damage, burn case, sticker remove are not valid for warranty <br>--}}
                    {{--<strong style="font-size: 15px;">*** Thanks For Using Our Service ***</strong>--}}
                    {{--</td>--}}
                    {{--</tr>--}}
                    <tr>
                        <td valign="top" colspan="3">
                            <table align = 'center' width = '100%'>
                                <tr>
                                    <td style = 'font-family:verdana; text-align:left; font-size:12px;'>Printed by: {{ Auth::user()->name }}</td>
                                    <td style = 'font-family:verdana; text-align:right; font-size:12px;'>{{  date('d-M-y H:i:s') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td height="20" colspan="3" style="font-size: 14px; text-align: center;" border="0">
                            Powered by: Deelko 01915871644
                        </td>
                    </tr>
                    </tbody>

                </table>
            </div>
        @endif

        @if(!$loop->last)
            <p class="p-break"></p>
        @endif

    @endforeach
@endsection

@section('custom_js')
    <script>
        $(document).ready(function(){
            setTimeout(function(){
                window.print()
            },1000)

            window.onafterprint = window.close;
        })
    </script>
@endsection
