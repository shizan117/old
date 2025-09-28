@extends('admin.layouts.master')
@section('title','Recharge Balance')

@section('content')
    <section>
        <div>
            <div class="col-md-12" id="error" style="display: none">
                <div class="script-message"></div>
                <div class="alert alert-danger alert-dismissible"  style="margin-top: 10px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <span id="error_msg"></span>
                </div>

            </div>
        </div>
    </section>
    <div class="row">
        <div class="col-xl-6">
            <div class="card-box" id="paymentData">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="clearfix">
                            <div class="pull-left">
                                <h4>Reseller Name :</h4>
                            </div>
                            <div class="pull-right">
                                <h4>{{ $reseller->resellerName }}</h4>
                            </div>
                        </div>

                        <div class="clearfix">
                            <div class="pull-left">
                                <h4 class="text-danger">Rechargeable:</h4>
                            </div>
                            <div class="pull-right">
                                <h4>{{ $rechargeable }}</h4>
                            </div>
                        </div>
                        <div class="clearfix">
                            <div class="pull-left text-success">
                                <h4 class="text-success">Recharge Now:</h4>
                            </div>
                            <div class="pull-right">
                                <input type="number" value="" min="100" step="100"
                                       class="form-control border-success"
                                       placeholder="Recharge amount"
                                       name="recharge_amount" id="recharge_amount"
                                       onkeypress="setAmount()" autocomplete="off"
                                       autofocus>
                            </div>
                        </div>
                        <div style="display: none;">
                            <span id="amount">0</span>
                            {{--//bKash Report--}}
                            {{--<span id="amount">500</span>--}}
                        </div>
                            @if(!empty($setting['bkash_app_key']))
                                <div class="clearfix">
                                    @if($charge == 'YES')
                                        Note: Charge Will be Include with Mobile Banking Payment.
                                    @endif
                                    <div class="pull-right">
                                        <button id="bKash_button" onclick="BkashPayment()" class="btn">
                                            <img src="{{ asset('assets/images/bkash_payment.png') }}" alt="" width="150">
                                        </button>
                                    </div>
                                </div>
                            @endif
                    </div>

                </div>
            </div>
            <div class="card-box" id="success" style="display: none">
                Thanks for your payment. Your Transaction ID "<span id="tr_id"></span>"
                <span id="tr_data"></span>
            </div>
        </div>
    </div>
@endsection

@php($amount = $rechargeable)
@section('custom_js')
    @include('bkash_script')
@endsection

@section('required_js')
    <script src="{{ $setting['bkash_checkout_script_url'] }}"
            type="text/javascript"></script>
    <script>
        function setAmount(){
            $("#recharge_amount").blur(function(){
                var amount = $("#recharge_amount").val();
                $("#amount").html(amount);
            });
        }
    </script>
@endsection

@section('custom_css')
    <style>
        .hidden {
            display: none !important;
        }

        #full_page_loading {
            background: url('{{ asset("assets/images/loading.gif") }}') no-repeat scroll center center transparent;
            position: fixed;
            background-size: 120px 100px;
            height: 100%;
            width: 100%;
            z-index: 9999;
            /*opacity: 0.5;*/
            top: 0;
            left: 0
        }
    </style>
@endsection
