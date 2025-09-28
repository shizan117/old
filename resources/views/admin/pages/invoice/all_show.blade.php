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
                                @else
                                    <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                                @endif
                            </div>
                            <div class="col-4 text-center">
                                <span class="btn btn-outline-secondary" style="margin-top: 10px; font-size: 22px;">Customer
                                    Copy</span>
                            </div>
                            <div class="col-4">
                                <div class="text-right">
                                    <h4>
                                        Invoice #<br>
                                        <strong>CINV-{{ sprintf('%06d', $invoice->client_id) }}</strong>
                                        <br>
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
                            <div class="pull-right m-t-5 text-right">
                                <strong>{!! $address !!}<br>Bangladesh</strong><br>
                                Helpline: <strong>{{ $phone }}</strong><br>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->


                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive table-sm">
                                <table class="table table-bordered m-t-5">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Description</th>
                                            <th>Plan Price</th>
                                            <th>Service Charge</th>
                                            <th>Sub Total</th>
                                            <th>Vat</th>
                                            <th>Total</th>
                                            <th>Discount</th>
                                            <th>Grand Total</th>
                                            <th>Paid Amount</th>
                                            <th>Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoices as $index => $invoice)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>Monthly Rent</td>
                                                <td>
                                                    <a href="{{ route('invoice.show', $invoice->id) }}"
                                                        style="text-decoration: none;">
                                                        {{ $invoice->bandwidth }}
                                                        -
                                                        {{ date('F', mktime(0, 0, 0, $invoice->bill_month, 10)) . '/' . $invoice->bill_year }}
                                                    </a>
                                                </td>
                                                <td>{{ $invoice->plan_price }} {{ $setting['currencyCode'] }}</td>
                                                <td>{{ $invoice->service_charge }}</td>
                                                <td>{{ $invoice->service_charge + $invoice->plan_price }}</td>
                                                <td>{{ $invoice->vat }}</td>
                                                <td>{{ $invoice->vat + $invoice->service_charge + $invoice->plan_price }}
                                                </td>
                                                <td>{{ $invoice->discount }}</td>
                                                <td>{{ $invoice->vat + $invoice->service_charge + $invoice->plan_price - $invoice->discount }}
                                                </td>
                                                <td>{{ $invoice->paid_amount }}</td>
                                                <td>{{ $invoice->due }}</td>
                                            </tr>
                                        @endforeach
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
                                        <td class="text-right"><b>Total Due Amount (With Vat):</b></td>
                                        <td class="text-right">
                                            {{ $totalDue }} {{ $setting['currencyCode'] }}
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

                </div> <!-- end row -->

            </div> <!-- end card-box -->

            <div class="card-box">
                <div class="panel-body">
                    <div class="clearfix">
                        <div class="row">
                            <div class="col-4">
                                @if (file_exists('assets/images/' . $logo))
                                    <img src="{{ asset('assets/images/' . $logo) }}" height="50px">
                                @else
                                    <img src="{{ asset('assets/images/default-logo.png') }}" height="50px">
                                @endif
                            </div>
                            <div class="col-4 text-center">
                                <span class="btn btn-outline-secondary" style="margin-top: 10px; font-size: 22px;">Office
                                    Copy</span>
                            </div>
                            <div class="col-4">
                                <div class="text-right">
                                    <h4>
                                        Invoice #<br>
                                        <strong>CINV-{{ sprintf('%06d', $invoice->client_id) }}</strong>
                                        <br>
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
                            <div class="pull-right m-t-5 text-right">
                                <strong>{!! $address !!}<br>Bangladesh</strong><br>
                                Helpline: <strong>{{ $phone }}</strong><br>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->


                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive table-sm">
                                <table class="table table-bordered m-t-5">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Description</th>
                                            <th>Plan Price</th>
                                            <th>Service Charge</th>
                                            <th>Sub Total</th>
                                            <th>Vat</th>
                                            <th>Total</th>
                                            <th>Discount</th>
                                            <th>Grand Total</th>
                                            <th>Paid Amount</th>
                                            <th>Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoices as $index => $invoice)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>Monthly Rent</td>
                                                <td>
                                                    <a href="{{ route('invoice.show', $invoice->id) }}"
                                                        style="text-decoration: none;">
                                                        {{ $invoice->bandwidth }}
                                                        -
                                                        {{ date('F', mktime(0, 0, 0, $invoice->bill_month, 10)) . '/' . $invoice->bill_year }}
                                                    </a>
                                                </td>
                                                <td>{{ $invoice->plan_price }} {{ $setting['currencyCode'] }}</td>
                                                <td>{{ $invoice->service_charge }}</td>
                                                <td>{{ $invoice->service_charge + $invoice->plan_price }}</td>
                                                <td>{{ $invoice->vat }}</td>
                                                <td>{{ $invoice->vat + $invoice->service_charge + $invoice->plan_price }}
                                                </td>
                                                <td>{{ $invoice->discount }}</td>
                                                <td>{{ $invoice->vat + $invoice->service_charge + $invoice->plan_price - $invoice->discount }}
                                                </td>
                                                <td>{{ $invoice->paid_amount }}</td>
                                                <td>{{ $invoice->due }}</td>
                                            </tr>
                                        @endforeach
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
                                        <td class="text-right"><b>Total Due Amount (With Vat):</b></td>
                                        <td class="text-right">
                                            {{ $totalDue }} {{ $setting['currencyCode'] }}
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
                    </div>
                </div> <!-- end row -->

            </div> <!-- end card-box -->
        </div><!-- end col -->
    </div>
    <!-- end row -->
@endsection
