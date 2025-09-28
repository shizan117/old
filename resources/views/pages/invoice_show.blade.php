<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Deelko">
    <meta name="csrf-token" id="_token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href={{ asset("assets/images/favicon.ico") }}>

    <title>View Invoice - {{ $setting['companyName'] }} {{ config('app.name', 'ISP Billing') }}</title>

    <!-- App css -->
    <link href={{ asset("assets/css/bootstrap.min.css") }} rel="stylesheet" type="text/css" />
    <link href={{ asset("assets/css/icons.css") }} rel="stylesheet" type="text/css" />
    <link href={{ asset("assets/css/style.css") }} rel="stylesheet" type="text/css" />

</head>


<body style="background: #fff;">
<?php
$address = $setting['address'];
$phone = $setting['phone'];
$logo = $setting['logo'];
$signature = $setting['company_signature'];
if($invoice->client->resellerId != null){
    $address = $invoice->client->reseller->resellerLocation;
    $phone = $invoice->client->reseller->phone;
    $logo = $invoice->client->reseller->logo;
    $signature = $invoice->client->reseller->signature;
}
?>

<!-- Begin page -->
<div id="wrapper">
    <!-- Start content -->
    <div class="content px-5" style="color: #373737; !important;">
        <div class="clearfix">
            <div class="pull-left row">
                @if(file_exists("assets/images/".$logo))
                    <img src="{{ asset("assets/images/".$logo) }}" height="50px">
                    {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                @else
                    <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                    {{--<span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span>--}}
                @endif
            </div>
            <div class="pull-right text-right">
                <h4 style="color: #373737;">Invoice #<br>
                    <strong>CINV-{{sprintf('%06d',$invoice->id)}}</strong>
                </h4>
            </div>
        </div>

        <hr>

        <div class="clearfix row">
            <div class="col-6 pull-left m-t-5 table-borderless table-sm">
                <address>
                    <table class="table">
                        <tr>
                            <td>Client Name:</td>
                            <th>{{ $invoice->client->client_name }}</th>
                        </tr>
                        <tr>
                            <td>Username:</td>
                            <th>{{ $invoice->client->username }}</th>
                        </tr>
                        <tr>
                            <td>Address:</td>
                            @php(($invoice->client->house_no == null)?$house_no='':$house_no='House:'.$invoice->client->house_no)
                            @php(($invoice->client->road_no == null)?$road_no='':$road_no='Road:'.$invoice->client->road_no)

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
                                {{ $invoice->client->address }}
                                <br>
                                {{ $invoice->client->thana.', '.$invoice->client->district }}
                                <br>
                                Bangladesh
                            </th>
                        </tr>
                        <tr>
                            <td>Phone:</td>
                            <th>{{ $invoice->client->phone }}</th>
                        </tr>
                    </table>
                </address>
            </div>
            <div class="col-6 pull-right m-t-5 text-right">
                <p>Invoice Date: <strong>{{ $invoice->created_at->format('d M Y') }}</strong></p>

                <strong>{!! $address !!}<br>Bangladesh</strong><br>
                Helpline: <strong>{{ $phone }}</strong><br>
            </div>
        </div>
        <!-- end row -->


        <div class="row clearfix">
            <div class="col-md-12">
                <div class="table-responsive table-sm">
                    <table class="table table-bordered m-t-5">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>Monthly Rent</td>
                            <td>{{$invoice->bandwidth}}
                                - {{date('F', mktime(0, 0, 0, $invoice['bill_month'], 10)).'/'.$invoice['bill_year']}}</td>
                            <td class="text-right">{{ $invoice->plan_price }} {{ $setting['currencyCode'] }}</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Service Charge</td>
                            <td>-</td>
                            <td class="text-right">{{ $invoice->service_charge }} {{ $setting['currencyCode'] }}</td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="clearfix row">
            <div class="col-6 pull-left">
                <div class="clearfix">
                    <h5>PAYMENT TERMS AND POLICIES</h5>

                    <p>
                        {!! $setting['payment_terms_condition'] !!}
                    </p>
                </div>
            </div>
            <div class="col-6 pull-right text-right">
                <div class="table-borderless table-sm">
                    <table class="table">
                        <tr>
                            <td class="text-right"><b>Total:</b></td>
                            <td class="text-right">
                                {{ $invoice->total }} {{ $setting['currencyCode'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><b>Discount:</b></td>
                            <td class="text-right">
                                {{ $invoice->discount }} {{ $setting['currencyCode'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><b>Sub Total:</b></td>
                            <td class="text-right">
                                {{ $invoice->all_total }} {{ $setting['currencyCode'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><b>VAT:</b></td>
                            <td class="text-right">
                                {{ $invoice->vat }} {{ $setting['currencyCode'] }}
                            </td>
                        </tr>

                        <tr>
                            <td class="text-right"><b>Grand Total:</b></td>
                            <td class="text-right">
                                {{ $invoice->sub_total }} {{ $setting['currencyCode'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><b>Paid Amount:</b></td>
                            <td class="text-right">
                                {{ $invoice->paid_amount }} {{ $setting['currencyCode'] }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><b>Due Amount (With Vat):</b></td>
                            <td class="text-right">
                                {{ $invoice->due }} {{ $setting['currencyCode'] }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="clearfix row">
            <div class="col-6 pull-left">
                <br><br>
                _________________________________ <br>
                <strong>Customer Signature</strong> <br>

            </div>

            <div class="col-6 pull-right text-right">
                <br>
                @if($signature !='')
                    <img src="{{ asset("assets/images/".$signature) }}" alt="" width="150"
                         style="margin-right: 80px !important">
                    <br>
                @endif
                __________________________________ <br>
                <strong>Authorized Signature & Co. Stamp</strong> <br>
            </div>
        </div>





    </div>

    <!-- END wrapper -->
</div>

<!-- jQuery  -->
<script src={{ asset("assets/js/jquery.min.js") }}></script>
<script src={{ asset("assets/js/popper.min.js") }}></script>
<script src={{ asset("assets/js/bootstrap.min.js") }}></script>
<script src={{ asset("assets/js/detect.js") }}></script>
<script src={{ asset("assets/js/fastclick.js") }}></script>
<script src={{ asset("assets/js/jquery.blockUI.js") }}></script>
<script src={{ asset("assets/js/waves.js") }}></script>
<script src={{ asset("assets/js/jquery.nicescroll.js") }}></script>
<script src={{ asset("assets/js/jquery.slimscroll.js") }}></script>
<script src={{ asset("assets/js/jquery.scrollTo.min.js") }}></script>

<!-- App js -->
<script src={{ asset("assets/js/jquery.core.js") }}></script>
<script src={{ asset("assets/js/jquery.app.js") }}></script>

</body>
</html>
