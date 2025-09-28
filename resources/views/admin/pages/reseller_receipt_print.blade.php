@extends ('admin.layouts.master')
@section('title')
    Print Money Receipt
@endsection
@section('custom_css')
    <style  media="print">
        @media print {
            @page {
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
                                    <h3><strong>{!! $setting['companyName'] !!}</strong></h3>
                                    <strong>{!! $setting['address'] !!}</strong><br>
                                    Phone: <strong>{{ $setting['phone'] }}</strong><br>
                                    {{--<h4>Receipt # <br>--}}
                                        {{--<strong>RRCPT-{{sprintf('%06d',$payment->id)}}</strong>--}}
                                    {{--</h4>--}}
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">

                                <div class="pull-left m-t-5">
                                    <address>
                                        Reseller Name: <strong>{{ $reseller->resellerName }}</strong><br>
                                        Address:  {{ $reseller->resellerLocation }}<br>
                                        Mobile No:  {{ $reseller->phone }}<br>
                                    </address>
                                </div>
                                <div class="pull-right m-t-5 text-right">
                                    <h4><strong>Recharge History</strong></h4>
                                    <p><strong>From: </strong> {{ date('d-M-Y',strtotime($date_range[0])) }}</p>
                                    <p><strong>To: </strong> {{ date('d-M-Y',strtotime($date_range[1])) }}</p>

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
                                                <th>SL</th>
                                                <th>#Receipt</th>
                                                <th>Payment Date</th>
                                                <th>Received By</th>
                                                <th class="text-right">Recharge Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @php($total_recharge_amount=0)
                                            @foreach($payments as $payment)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>RRCPT-{{sprintf('%06d',$payment->id)}}</td>
                                                <td>{{ date('d-M-Y',strtotime($payment->created_at)) }}</td>
                                                @php((empty($payment->user_id)) ? $received = 'Online Paid' : $received = $payment->user->name)
                                                <td>{{ $received }}</td>
                                                <td class="text-right">{{ number_format($payment->recharge_amount,2) }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                                @php($total_recharge_amount += $payment->recharge_amount)
                                            @endforeach
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
                                                <td class="text-right">{{ number_format(($payments[0]->pre_balance),2) }} {{ $setting['currencyCode'] }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><b>Reseller's Current Balance:</b></td>
                                                <td class="text-right"><b>{{ number_format($payments[$payments->count()-1]->pre_balance + $payments[$payments->count()-1]->recharge_amount,2) }} {{ $setting['currencyCode'] }}</b></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-6 offset-xl-4">
                                <div class="table-borderless table-sm">
                                    <table class="table">

                                        <tr>
                                            <td class="text-right"><b>Total Recharge:</b></td>
                                            <td class="text-right">{{ number_format($total_recharge_amount,2) }} {{ $setting['currencyCode'] }}</td>
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
                    </div>                    <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->
        </div>
        <!-- end row -->
@endsection
@section('custom_js')
    <script>
        $(document).ready(function(){
            setTimeout(function(){
                window.print();
                window.onafterprint = window.close();
            },500);
        })
    </script>
@endsection
