@extends ('admin.layouts.master')
@section('title')
    View Invoice
@endsection
<?php
$address = $setting['address'];
$phone = $setting['phone'];
$logo = $setting['logo'];
$signature = $setting['company_signature'];
if ($invoice->client->resellerId != null) {
    $address = $invoice->client->reseller->resellerLocation;
    $phone = $invoice->client->reseller->phone;
    $logo = $invoice->client->reseller->logo;
    $signature = $invoice->client->reseller->signature;
}
?>

@php
    $usedCableType = DB::table('clients')
        ->where('client_name', $invoice->client->client_name)
        ->where('username', $invoice->client->username)
        ->value('cable_type');
@endphp
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card-box">
                <div class="panel-body">
                    <div class="clearfix">
                        <div class="row">
                            <div class="col-4">
                                @if (file_exists('assets/images/' . $logo))
                                    <img src="{{ asset('assets/images/' . $logo) }}" height="50px">
                                    {{-- <span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span> --}}
                                @else
                                    <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                                    {{-- <span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span> --}}
                                @endif
                            </div>
                            <div class="col-4 text-center">
                                {{-- <div class="text-center"></div> --}}
                                <span class="btn btn-outline-secondary" style="margin-top: 10px; font-size: 22px;">Customer Copy</span>
                            </div>
                            <div class="col-4">
                                <div class="text-right">
                                    <h4>
                                        Invoice #<br>
                                        <strong>CINV-{{ sprintf('%06d', $invoice->id) }}</strong>
                                        <br>
                                        Status: <strong>{{$invoice->due > 0 ? "UNPAID" : "PAID"}}</strong>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <style>
                        @media print {
                            .row {
                                display: flex;
                                flex-wrap: nowrap;
                            }
                            .col-md-6 {
                                width: 50% !important;
                                float: none !important;
                                display: inline-block;
                                vertical-align: top;
                            }
                            .pull-right {
                                text-align: right !important;
                            }
                            .table-borderless {
                                width: 100%;
                            }
                            .text-right {
                                text-align: right !important;
                            }
                        }
                    </style>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-borderless table-sm">
                                <address>
                                    <table class="table">
                                        <tr>
                                            <td>Client Name:</td>
                                            <th>{{ $invoice->client->client_name }}</th>
                                        </tr>
                                        <tr>
                                            <td>Username:</td>
                                            <th>{{ $invoice->client->username }} <span style="padding-left: 15px;">({{ $usedCableType }})</span></th>
                                        </tr>
                                        <tr>
                                            <td>Address:</td>
                                            <th>
                                                @php
                                                    $house_no = $invoice->client->house_no ? 'House:' . $invoice->client->house_no : '';
                                                    $road_no = $invoice->client->road_no ? 'Road:' . $invoice->client->road_no : '';
                                                @endphp
                                                @if ($house_no && $road_no)
                                                    {{ $house_no . ', ' . $road_no . ', ' }}
                                                @else
                                                    {{ $house_no ? $house_no . ', ' : '' }}
                                                    {{ $road_no ? $road_no . ', ' : '' }}
                                                @endif
                                                {{ $invoice->client->address }}
                                                {{ $invoice->client->thana . ', ' . $invoice->client->district }}
                                                Bangladesh
                                            </th>
                                        </tr>
                                        <tr>
                                            <td>Zone/area:</td>
                                            <th>{{ $invoice->client->distribution->distribution }}</th>
                                        </tr>
                                        <tr>
                                            <td>Phone:</td>
                                            <th>{{ $invoice->client->phone }}</th>
                                        </tr>
                                    </table>
                                </address>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-right m-t-5">
                                <p>
                                    Invoice Date: <strong>{{ $invoice->created_at->format('d M Y') }}</strong><br>
                                    Paid Time: <strong>
                                        @if ($invoice->updated_at->diffInSeconds($invoice->created_at) >= 3 || $invoice->paid_amount > 0)
                                            {{ $invoice->updated_at->format('d M Y h:i A') }}
                                        @else
                                            Pending
                                        @endif
                                    </strong>
                                </p>
                                <strong>{!! $address !!}<br>Bangladesh</strong><br>
                                Helpline: <strong>{{ $phone }}</strong>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive table-sm">
                                <table class="table table-bordered m-t-2">
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
                                        <td>{{ $invoice->bandwidth }}
                                            -
                                            {{ date('F', mktime(0, 0, 0, $invoice['bill_month'], 10)) . '/' . $invoice['bill_year'] }}
                                        </td>
                                        <td class="text-right">{{ $invoice->plan_price }}
                                            {{ $setting['currencyCode'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Service Charge</td>
                                        <td>{{ $invoice->charge_for ?? '-' }}</td>
                                        <td class="text-right">{{ $invoice->service_charge }}
                                            {{ $setting['currencyCode'] }}</td>
                                    </tr>

                                    <tr>
                                        <td>3</td>
                                        <td>OTC Fee</td>
                                        <td>-</td>
                                        <td class="text-right">{{ $invoice->otc_charge }}
                                            {{ $setting['currencyCode'] }}</td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6 col-6">
                            <div class="clearfix">
                                <h5>PAYMENT TERMS AND POLICIES</h5>

                                <p>
                                    {!! $setting['payment_terms_condition'] !!}
                                </p>
                            </div>
                        </div>
                        <div class="offset-xl-1 col-xl-5 col-5 offset-1 text-right">
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

                    <div class="row">
                        <div class="col-xl-6 col-6">
                            <br><br>
                            _________________________________ <br>
                            <strong>Customer Signature</strong> <br>

                        </div>

                        <div class="col-xl-3 col-6 offset-xl-3 text-right">
                            <br>
                            <img src="{{ asset('assets/images/' . $signature) }}" alt="" width="150"
                                 style="margin-right: 80px !important">
                            <br>
                            __________________________________ <br>
                            <strong>Authorized Signature & Co. Stamp</strong> <br>
                        </div>
                    </div>
                    <hr>
                    {{-- <div class="d-print-none">
                        <div class="pull-right">
                            <a href="javascript:window.print()" class="btn btn-light waves-effect waves-light">
                                <i class="fa fa-print"></i></a>
                            <a href="{{ route('invoice.index') }}"
                                class="btn btn-primary waves-effect waves-light">Back</a>

                            @if ($invoice->client->phone != '')
                                <?php
                                $mobile_no = $invoice->client->phone;

                                $lenght = strlen((string) $mobile_no);
                                if ($lenght > 11) {
                                    $to = substr_replace($mobile_no, '880', 0, 3) . '';
                                } elseif ($lenght == 11) {
                                    $to = substr_replace($mobile_no, '880', 0, 1) . '';
                                } else {
                                    $to = substr_replace($mobile_no, '880', 0, 0) . '';
                                }
                                $message = urlencode(URL::signedRoute('invoice.share', $invoice->id));
                                ?>

                                <a href="{{ 'https://api.whatsapp.com/send/?phone=' . $to . '&text=' . $message }}"
                                    title="Share invoice via Whatsapp" class="btn btn-success waves-effect waves-light"
                                    style="font-size: 18px;" target="_blank">
                                    <i class="fa fa-whatsapp"></i>
                                </a>
                            @endif
                        </div>
                        <div class="clearfix"></div>
                    </div> --}}
                </div> <!-- end row -->

            </div> <!-- end card-box -->

            <div class="card-box">
                <div class="panel-body">
                    <div class="clearfix">
                        <div class="row">
                            <div class="col-4">
                                @if (file_exists('assets/images/' . $logo))
                                    <img src="{{ asset('assets/images/' . $logo) }}" height="50px">
                                    {{-- <span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span> --}}
                                @else
                                    <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                                    {{-- <span><span class="logo">{{ $setting['companyName'] }}</span><br><span><strong>Internet Service Provider</strong></span></span> --}}
                                @endif
                            </div>
                            <div class="col-4 text-center">
                                {{-- <div class="text-center"></div> --}}
                                <span class="btn btn-outline-secondary" style="margin-top: 10px; font-size: 22px;">Office Copy</span>
                            </div>
                            <div class="col-4">
                                <div class="text-right">
                                    <h4>
                                        Invoice #<br>
                                        <strong>CINV-{{ sprintf('%06d', $invoice->id) }}</strong>
                                        <br>
                                        Status: <strong>{{$invoice->due > 0 ? "UNPAID" : "PAID"}}</strong>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="pull-left m-t-5 table-borderless table-sm">
                                <address>
                                    <table class="table">
                                        <tr>
                                            <td>Client Name:</td>
                                            <th>{{ $invoice->client->client_name }}</th>
                                        </tr>
                                        <tr>
                                            <td>Username:</td>
                                            <th>{{ $invoice->client->username }} <span
                                                        style="padding-left: 15px;">({{ $usedCableType }})</span></th>
                                        </tr>
                                        <tr>
                                            <td>Address:</td>
                                            @php($invoice->client->house_no == null ? ($house_no = '') : ($house_no = 'House:' . $invoice->client->house_no))
                                            @php($invoice->client->road_no == null ? ($road_no = '') : ($road_no = 'Road:' . $invoice->client->road_no))

                                            <th>
                                                @if ($house_no != '' && $road_no != '')
                                                    {{ $house_no . ', ' . $road_no . ', ' }}
                                                @else
                                                    @if ($house_no != '')
                                                        {{ $house_no . ', ' }}
                                                    @endif
                                                    @if ($road_no != '')
                                                        {{ $road_no . ', ' }}
                                                    @endif
                                                @endif
                                                {{ $invoice->client->address }}
                                                {{ $invoice->client->thana . ', ' . $invoice->client->district }}
                                                Bangladesh
                                            </th>
                                        </tr>
                                        <tr>
                                            <td>Zone/area:</td>
                                            <th>{{$invoice->client->distribution->distribution}}</th>
                                        </tr>
                                        <tr>
                                            <td>Phone:</td>
                                            <th>{{ $invoice->client->phone }}</th>
                                        </tr>
                                    </table>
                                </address>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="pull-right m-t-5 text-right">
                                <p>Invoice Date: <strong>{{ $invoice->created_at->format('d M Y') }}</strong>

                                    @if ($invoice->updated_at->diffInSeconds($invoice->created_at) >= 3 ||
                                             $invoice->paid_amount > 0)
                                        <br>
                                        Paid Time: <strong>{{ $invoice->updated_at->format('d M Y h:i A') }}</strong>
                                    @else
                                        <br>
                                        Paid Time: <strong>Pending</strong>
                                    @endif


                                </p>

                                <strong>{!! $address !!}<br>Bangladesh</strong><br>
                                Helpline: <strong>{{ $phone }}</strong><br>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->


                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive table-sm">
                                <table class="table table-bordered m-t-2">
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
                                        <td>{{ $invoice->bandwidth }}
                                            -
                                            {{ date('F', mktime(0, 0, 0, $invoice['bill_month'], 10)) . '/' . $invoice['bill_year'] }}
                                        </td>
                                        <td class="text-right">{{ $invoice->plan_price }}
                                            {{ $setting['currencyCode'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Service Charge</td>
                                        <td>{{ $invoice->charge_for ?? '-' }}</td>
                                        <td class="text-right">{{ $invoice->service_charge }}
                                            {{ $setting['currencyCode'] }}</td>
                                    </tr>

                                    <tr>
                                        <td>3</td>
                                        <td>OTC Fee</td>
                                        <td>-</td>
                                        <td class="text-right">{{ $invoice->otc_charge }}
                                            {{ $setting['currencyCode'] }}</td>
                                    </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6 col-6">
                            <div class="clearfix">
                                <h5>PAYMENT TERMS AND POLICIES</h5>

                                <p>
                                    {!! $setting['payment_terms_condition'] !!}
                                </p>
                            </div>
                        </div>
                        <div class="offset-xl-1 col-xl-5 col-5 offset-1 text-right">
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

                    <div class="row">
                        <div class="col-xl-6 col-6">
                            <br><br>
                            _________________________________ <br>
                            <strong>Customer Signature</strong> <br>

                        </div>

                        <div class="col-xl-3 col-6 offset-xl-3 text-right">
                            <br>
                            <img src="{{ asset('assets/images/' . $signature) }}" alt="" width="150"
                                 style="margin-right: 80px !important">
                            <br>
                            __________________________________ <br>
                            <strong>Authorized Signature & Co. Stamp</strong> <br>
                        </div>
                    </div>
                    <hr>
                    <div class="d-print-none">
                        <div class="pull-right">
                            <a href="javascript:window.print()" class="btn btn-light waves-effect waves-light">
                                <i class="fa fa-print"></i></a>
                            <a href="{{ route('invoice.index') }}"
                               class="btn btn-primary waves-effect waves-light">Back</a>

                            @if ($invoice->client->phone != '')
                                    <?php
                                    $mobile_no = $invoice->client->phone;

                                    $lenght = strlen((string)$mobile_no);
                                    if ($lenght > 11) {
                                        $to = substr_replace($mobile_no, '880', 0, 3) . '';
                                    } elseif ($lenght == 11) {
                                        $to = substr_replace($mobile_no, '880', 0, 1) . '';
                                    } else {
                                        $to = substr_replace($mobile_no, '880', 0, 0) . '';
                                    }
                                    $message = urlencode(URL::signedRoute('invoice.share', $invoice->id));
                                    ?>

                                <a href="{{ 'https://api.whatsapp.com/send/?phone=' . $to . '&text=' . $message }}"
                                   title="Share invoice via Whatsapp" class="btn btn-success waves-effect waves-light"
                                   style="font-size: 18px;" target="_blank">
                                    <i class="fa fa-whatsapp"></i>
                                </a>
                            @endif
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div> <!-- end row -->

            </div> <!-- end card-box -->
        </div><!-- end col -->
    </div>
    <!-- end row -->
@endsection
