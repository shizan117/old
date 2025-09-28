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
    @foreach ($groupedInvoices as $clientId => $invoices)
        <?php
        // Assuming the client data is needed for each group
        $client = \App\Client::find($clientId);
        $address = $setting['address'];
        $phone = $setting['phone'];
        $logo = $setting['logo'];
        $signature = $setting['company_signature'];
        if ($client->resellerId != null) {
            $address = $client->reseller->resellerLocation;
            $phone = $client->reseller->phone;
            $logo = $client->reseller->logo;
            $signature = $client->reseller->signature;
        }
        ?>
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
                                    <span class="btn btn-outline-secondary"
                                        style="margin-top: 10px; font-size: 22px;">Customer Copy</span>
                                </div>
                                <div class="col-4">
                                    <div class="text-right">
                                        <h4>
                                            Invoices for Client: <strong>{{ $client->client_name }}</strong>
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
                                                <th>{{ $client->client_name }}
                                                </th>
                                            </tr>
                                            <tr>
                                                <td>Username:</td>
                                                <th>{{ $client->username }} <span
                                                        style="padding-left: 15px;">({{ $client->cable_type }})</span></th>
                                            </tr>
                                            <tr>
                                                <td>Address:</td>
                                                @php($house_no = $client->house_no ? 'House:' . $client->house_no : '')
                                                @php($road_no = $client->road_no ? 'Road:' . $client->road_no : '')
                                                <th>
                                                    {{ $house_no }} {{ $road_no }}
                                                    {{ $client->address }}
                                                    {{ $client->thana . ', ' . $client->district }}
                                                    Bangladesh
                                                </th>
                                            </tr>
                                            <tr>
                                                <td>Zone/area:</td>
                                                <th>{{ $client->distribution->distribution }}</th>
                                            </tr>
                                            <tr>
                                                <td>Phone:</td>
                                                <th>{{ $client->phone }}</th>
                                            </tr>
                                            <tr>
                                                <td>NID:</td>
                                                <th>{{ $client->clientNid ?? 'N/A' }}</th>
                                            </tr>
                                        </table>
                                    </address>
                                </div>
                            </div>
                            <div class="col-3 offset-3">
                                <div class="m-t-5 text-right">
                                    <p>Invoice Date: <strong>{{ now()->format('d M Y') }}</strong></p>
                                    <strong>{!! $address !!}</strong><br>
                                    Bangladesh<br>
                                    Helpline: <strong>{{ $phone }}</strong><br>
                                </div>
                            </div>
                        </div>
                        

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
                                                <th>OTC Fee</th>
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
                                                        {{ $invoice->bandwidth }}
                                                    -
                                                    {{ date('F', mktime(0, 0, 0, $invoice['bill_month'], 10)) . '/' . $invoice['bill_year'] }}
                                                    </td>
                                                    <td class="text-right">{{ $invoice->plan_price }}</td>
                                                    <td class="text-right">{{ $invoice->service_charge }}</td>
                                                    <td class="text-right">{{ $invoice->otc_charge }}</td>
                                                    <td class="text-right">{{ $invoice->sub_total }}</td>
                                                    <td class="text-right">{{ $invoice->vat }}</td>
                                                    <td class="text-right">{{ $invoice->total }}</td>
                                                    <td class="text-right">{{ $invoice->discount }}</td>
                                                    <td class="text-right">
                                                        {{ $invoice->vat + $invoice->service_charge + $invoice->plan_price - $invoice->discount }}
                                                    </td>
                                                    <td class="text-right">{{ $invoice->paid_amount }}</td>
                                                    <td class="text-right">{{ $invoice->due }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-6 col-6">
                                <h5>PAYMENT TERMS AND POLICIES</h5>
                                <p>{!! $setting['payment_terms_condition'] !!}</p>
                            </div>
                            <div class="offset-xl-1 col-xl-5 col-5 offset-1 text-right">
                                <table class="table">
                                    <tr>
                                        <td class="text-right" colspan="11"><b>Total Due:</b></td>
                                        <td class="text-right">
                                            <b>{{ $invoices->sum('due') }} {{ $setting['currencyCode'] }}</b>
                                        </td>
                                    </tr>
                                </table>
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
                    </div> 
                </div>

                <div class="card-box mt-5">
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
                                    <span class="btn btn-outline-secondary"
                                        style="margin-top: 10px; font-size: 22px;">Office Copy</span>
                                </div>
                                <div class="col-4">
                                    <div class="text-right">
                                        <h4>
                                            Invoices for Client: <strong>{{ $client->client_name }}</strong>
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
                                                <th>{{ $client->client_name }} ({{ $client->distribution->distribution }})
                                                </th>
                                            </tr>
                                            <tr>
                                                <td>Username:</td>
                                                <th>{{ $client->username }} <span
                                                        style="padding-left: 15px;">({{ $client->cable_type }})</span></th>
                                            </tr>
                                            <tr>
                                                <td>Address:</td>
                                                @php($house_no = $client->house_no ? 'House:' . $client->house_no : '')
                                                @php($road_no = $client->road_no ? 'Road:' . $client->road_no : '')
                                                <th>
                                                    {{ $house_no }} {{ $road_no }}
                                                    {{ $client->address }}
                                                    {{ $client->thana . ', ' . $client->district }}
                                                    Bangladesh
                                                </th>
                                            </tr>
                                            <tr>
                                                <td>Zone/area:</td>
                                                <th>{{ $client->distribution->distribution }}</th>
                                            </tr>
                                            <tr>
                                                <td>Phone:</td>
                                                <th>{{ $client->phone }}</th>
                                            </tr>
                                            <tr>
                                                <td>NID:</td>
                                                <th>{{ $client->clientNid ?? 'N/A' }}</th>
                                            </tr>
                                        </table>
                                    </address>
                                </div>
                            </div>
                            <div class="col-3 offset-3">
                                <div class="m-t-5 text-right">
                                    <p>Invoice Date: <strong>{{ now()->format('d M Y') }}</strong></p>
                                    <strong>{!! $address !!}</strong><br>
                                    Bangladesh<br>
                                    Helpline: <strong>{{ $phone }}</strong><br>
                                </div>
                            </div>
                        </div>
                        

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
                                                <th>OTC Fee</th>
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
                                                        {{ $invoice->bandwidth }}
                                                    -
                                                    {{ date('F', mktime(0, 0, 0, $invoice['bill_month'], 10)) . '/' . $invoice['bill_year'] }}
                                                    </td>
                                                    <td class="text-right">{{ $invoice->plan_price }}</td>
                                                    <td class="text-right">{{ $invoice->service_charge }}</td>
                                                    <td class="text-right">{{ $invoice->otc_charge }}</td>
                                                    <td class="text-right">{{ $invoice->sub_total }}</td>
                                                    <td class="text-right">{{ $invoice->vat }}</td>
                                                    <td class="text-right">{{ $invoice->total }}</td>
                                                    <td class="text-right">{{ $invoice->discount }}</td>
                                                    <td class="text-right">
                                                        {{ $invoice->vat + $invoice->service_charge + $invoice->plan_price - $invoice->discount }}
                                                    </td>
                                                    <td class="text-right">{{ $invoice->paid_amount }}</td>
                                                    <td class="text-right">{{ $invoice->due }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-6 col-6">
                                <h5>PAYMENT TERMS AND POLICIES</h5>
                                <p>{!! $setting['payment_terms_condition'] !!}</p>
                            </div>
                            <div class="offset-xl-1 col-xl-5 col-5 offset-1 text-right">
                                <table class="table">
                                    <tr>
                                        <td class="text-right" colspan="11"><b>Total Due:</b></td>
                                        <td class="text-right">
                                            <b>{{ $invoices->sum('due') }} {{ $setting['currencyCode'] }}</b>
                                        </td>
                                    </tr>
                                </table>
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
                    </div> 
                </div>
            </div> 
        </div>
        @if (!$loop->last)
            <p class="p-break"></p>
        @endif
    @endforeach
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
