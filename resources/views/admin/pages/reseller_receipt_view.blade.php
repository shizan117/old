@extends ('admin.layouts.master')
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
                                        <strong>RRCPT-{{sprintf('%06d',$payment->id)}}</strong>
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">

                                <div class="pull-left m-t-5">
                                    <address>
                                        Reseller Name: <strong>{{ $payment->reseller->resellerName }}</strong><br>
                                        {{ $payment->reseller->resellerLocation }}<br>
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
                                                <td>Recharge Balance</td>
                                                <td>-</td>
                                                <td class="text-right">{{ $payment->recharge_amount }} {{ $setting['currencyCode'] }}</td>
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
                                                <td class="text-right"><b>Reseller's Previous Balance:</b></td>
                                                <td class="text-right">{{ number_format(($payment->pre_balance),2,'.','') }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Reseller's Current Balance:</b></td>
                                                <td class="text-right"><b>{{ $payment->pre_balance + $payment->recharge_amount }} {{ $setting['currencyCode'] }}</b></td>
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
                                            <td class="text-right">{{ $payment->recharge_amount }} {{ $setting['currencyCode'] }}</td>
                                        </tr>

                                        <tr>
                                            <td class="text-right"><b>VAT:</b></td>
                                            <td class="text-right">{{ $payment->vat }} {{ $setting['currencyCode'] }}</td>
                                        </tr>

                                        <tr>
                                            <td class="text-right"><b>Sub Total:</b></td>
                                            <td class="text-right">{{ $payment->recharge_amount }} {{ $setting['currencyCode'] }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-right"><b>Recharge Amount:</b></td>
                                            <td class="text-right">{{ $payment->recharge_amount }} {{ $setting['currencyCode'] }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-6 col-6">
                                </br></br>
                                _________________________________ <br>
                                <strong>Reseller Signature</strong> </br>

                            </div>

                            <div class="col-xl-3 col-6 offset-xl-3 text-right">
                                </br>
                                <img src="{{ asset("assets/images/".$setting['company_signature']) }}" alt="" width="150"
                                     style="margin-right: 80px !important">
                                </br>__________________________________ <br>
                                <strong>Authorized Signature & Co. Stamp</strong> </br>
                            </div>
                        </div>
                        <hr>
                        <div class="d-print-none">
                            <div class="pull-right">
                                <a href="javascript:window.print()" class="btn btn-light waves-effect waves-light"><i class="fa fa-print"></i></a>
                                <a href="{{ route('receipt.seller') }}" class="btn btn-primary waves-effect waves-light">Back</a>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>                    <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->
        </div>
        <!-- end row -->
@endsection
