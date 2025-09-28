@extends('layouts.master')
@section('title')
    {{ $page_title }}
@endsection

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
                                <h4>Client Name :</h4>
                            </div>
                            <div class="pull-right">
                                <h4>{{ $client->client_name }}</h4>
                            </div>
                        </div>

                        {{--<span style="display:none" id="number">CINV-{{ $client->id }}</span>--}}

                        <div class="clearfix">
                            <div class="pull-left">
                                <h4>Due Amount :</h4>
                            </div>
                            <div class="pull-right">
                                <h4>{{ $client->due }}</h4>
                                {{--//bKash Report--}}
                                {{--<h4>500</h4>--}}
                            </div>
                        </div>
                        <div style="display: none">
                            <span id="amount">{{ $client->due + $ch }}</span>
                            {{--//bKash Report--}}
                            {{--<span id="amount">500</span>--}}
                        </div>
                        @if($client->resellerId == null)
                            @if(!empty($setting['bkash_app_key']) && $client->due > 0)

                                <div class="clearfix">

                                    @if($charge == 'YES')
                                        Note: Charge Will be Include with Mobile Banking Payment.
                                    @endif
                                    <div class="pull-right">
                                        {{--<button id="bKash_button" disabled="disabled" class="btn" style="background: #E3126E;color:#fff" >--}}
                                            {{--Pay With bKash--}}
                                        {{--</button>--}}
                                        <button id="bKash_button" onclick="BkashPayment()" class="btn">
                                            <img src="{{ asset('assets/images/bkash_payment.png') }}" alt="" width="150">
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @else
                            @if(!empty($client->reseller->bkash_app_key) && $client->due > 0  && $resellerPlan->isNotEmpty())
                             @foreach($resellerPlan as $plan)
                            @if ($plan->sell_price <= $client->reseller->balance)
                                <div class="clearfix">

                                    @if($charge == 'YES')
                                        Note: Charge Will be Include with Mobile Banking Payment.
                                    @endif
                                    <div class="pull-right">
                                        {{--<button id="bKash_button" disabled="disabled" class="btn" style="background: #E3126E;color:#fff">--}}
                                            {{--Pay With bKash--}}
                                        {{--</button>--}}

                                        <button id="bKash_button" onclick="BkashPayment()" class="btn">
                                            <img src="{{ asset('assets/images/bkash_payment.png') }}" alt="" width="150">
                                        </button>
                                    </div>
                                </div>
                                   @break
                                @endif
                            @endforeach
                            @endif
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


@section('custom_js')
    @include('bkash_script')
@endsection

@section('required_js')
    <script src="{{ $setting['bkash_checkout_script_url'] }}"
            type="text/javascript"></script>
@endsection
