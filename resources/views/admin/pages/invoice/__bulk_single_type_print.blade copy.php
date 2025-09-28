@extends ('admin.layouts.master')
@section('title')
    Print Invoices
@endsection

@section('custom_css')
    <style>
        @media print {
            .p-break {
                page-break-after: always;
            }
        }
    </style>
@endsection

@section('content')
    @foreach ($invoices as $invoice)
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
            $clientNidNumber = DB::table('clients')
                ->where('client_name', $invoice->client->client_name)
                ->where('username', $invoice->client->username)
                ->value('clientNid');
        @endphp

        @php
            $usedCableType = DB::table('clients')
                ->where('client_name', $invoice->client->client_name)
                ->where('username', $invoice->client->username)
                ->value('cable_type');
        @endphp
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
                                    <span class="btn btn-outline-secondary"
                                        style="margin-top: 10px; font-size: 22px;">Office Copy</span>
                                </div>
                                <div class="col-4">
                                    <div class="text-right">
                                        <h4>
                                            Invoice #<br>
                                            <strong>CINV-{{ sprintf('%06d', $invoice->id) }}</strong>
                                            <br>
                                            Status: <strong>DUE</strong>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="m-t-5 table-borderless table-sm">
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

                                            <tr>
                                                <td>NID:</td>
                                                <th>{{ $clientNidNumber ?? 'N/A' }}</th>
                                            </tr>
                                        </table>
                                    </address>
                                </div>
                            </div>
                            <div class="col-3 offset-3">
                                <div class="m-t-5 text-right">
                                    <p>Invoice Date: <strong>{{ $invoice->created_at->format('d M Y') }}</strong></p>
                                    <strong>{!! $address !!}</strong><br>
                                    Bangladesh<br>
                                    Helpline: <strong>{{ $phone }}</strong><br>
                                </div>
                            </div><!-- end col -->
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
                                                <th class="text-right">Amount</th>
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
                                                <td>-</td>
                                                <td class="text-right">{{ $invoice->service_charge }}
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
                                            <td class="text-right"><b>Paid:</b></td>
                                            <td class="text-right">
                                                {{ $invoice->paid_amount }} {{ $setting['currencyCode'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>Due:</b></td>
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
                                __________________________ <br>
                                <strong>Customer Signature</strong> <br>

                            </div>

                            <div class="col-xl-3 col-6 offset-xl-3 text-right">
                                <br>
                                <img src="{{ asset('assets/images/' . $signature) }}" alt="" width="150"
                                    style="margin-right: 80px !important">
                                <br>___________________________ <br>
                                <strong>Authorized Signature</strong> <br>
                            </div>
                        </div>
                    </div> <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->

            <div class="col-md-12 mt-5">
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
                                    <span class="btn btn-outline-secondary" style="margin-top: 10px;font-size: 22px;">Customer
                                        Copy</span>
                                </div>
                                <div class="col-4">
                                    <div class="text-right">
                                        <h4>
                                            Invoice #<br>
                                            <strong>CINV-{{ sprintf('%06d', $invoice->id) }}</strong>
                                            <br>
                                            Status: <strong>DUE</strong>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="m-t-5 table-borderless table-sm">
                                    <address>
                                        <table class="table">
                                            <tr>
                                                <td>Client Name:</td>
                                                <th>{{ $invoice->client->client_name }} ({{$invoice->client->distribution->distribution}})</th>
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
                                            <tr>
                                                <td>NID:</td>
                                                <th>{{ $clientNidNumber ?? 'N/A' }}</th>
                                            </tr>
                                        </table>
                                    </address>
                                </div>
                            </div>
                            <div class="col-3 offset-3">
                                <div class="m-t-5 text-right">
                                    <p>Invoice Date: <strong>{{ $invoice->created_at->format('d M Y') }}</strong></p>
                                    <strong>{!! $address !!}</strong><br>
                                    Bangladesh<br>
                                    Helpline: <strong>{{ $phone }}</strong><br>
                                </div>
                            </div><!-- end col -->
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
                                                <th class="text-right">Amount</th>
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
                                                <td>-</td>
                                                <td class="text-right">{{ $invoice->service_charge }}
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
                                    <h5>PAYMENT TERMS & POLICIES</h5>

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
                                            <td class="text-right"><b>Paid:</b></td>
                                            <td class="text-right">
                                                {{ $invoice->paid_amount }} {{ $setting['currencyCode'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>Due:</b></td>
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
                                __________________________ <br>
                                <strong>Customer Signature</strong> <br>

                            </div>

                            <div class="col-xl-3 col-6 offset-xl-3 text-right">
                                <br>
                                <img src="{{ asset('assets/images/' . $signature) }}" alt="" width="150"
                                    style="margin-right: 80px !important">
                                <br>___________________________ <br>
                                <strong>Authorized Signature</strong> <br>
                            </div>
                        </div>
                    </div> <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->
        </div>
        @if (!$loop->last)
            <p class="p-break"></p>
        @endif
    @endforeach
    <!-- end row -->
@endsection

@section('custom_js')
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                window.print()
            }, 1000)

            window.onafterprint = window.close;
        })
    </script>
@endsection
