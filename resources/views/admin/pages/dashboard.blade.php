<?php $user = Auth::user(); ?>
@extends('admin.layouts.master')
@section('title')
    Dashboard
@endsection



@section('content')
    <style>
        @media screen and (max-width: 700px) {
            .singleWidget i {
                font-size: 2rem;
            }

            .singleWidget {
                text-align: center !important;
                margin-bottom: 15px;
            }

            .singleWidget .header-title {
                font-size: 12px !important;
            }

            .singleWidget .widget-detail-1 h3 {
                font-size: 16px !important;
                font-weight: 700 !important;
                margin: 0 !important;
                text-align: center !important;
                padding: 0 !important;
            }

            .singleWidget .widget-detail-1 {
                margin: 0 !important;
                height: auto !important;
            }

            .singleWidget .widget-chart-box-1 {
                float: none !important;
            }

            .singleWidget .widget-chart-1 .widget-detail-1 {
                min-height: auto !important;
            }

            .singleWidget .card-box {
                height: 100%;
            }

            #quickMenuOnMobile {
                display: block !important;
                text-align: center !important;
                text-align-last: center !important;
                position: fixed;
                z-index: 1;
                width: 100%;
                background: #253138;
                left: 0;
                top: 67px;
                padding: 5px 0;
            }
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <ul class="quick-menu text-center d-md-none" id="quickMenuOnMobile">
                <li><a class="btn btn-outline-info btn-sm" href="{{route('client.index')}}"><i class="fa fa-users"></i>
                        All Client</a></li>
                <li><a class="btn btn-outline-info btn-sm" href="{{route('client.add')}}"><i
                                class="fa fa-user-circle"></i> New Client</a></li>
                <li><a class="btn btn-outline-info btn-sm" href="{{route('invoice.add')}}"><i class="fa fa-money"></i>
                        Create Invoice</a></li>
                {{-- <li><a class="btn btn-outline-info btn-sm" href="{{route('expanse.add')}}"><i class="fa fa-clipboard"></i> New Expense</a></li> --}}
            </ul>
            <div class="row justify-content-center pt-3 pt-md-0">
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_revenue today'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-purple" style="background: #14d4a8; color: #fff;">
                            <h4 class="header-title mt-0 m-b-10">Revenue Today</h4>

                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ number_format($today_income,2) }} </h3>
                                    <!--<p class="mb-0">Revenue today</p>-->
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_revenue this month'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-success">

                            <a href="{{ route('receipt.index') }}">
                                <h4 class="header-title mt-0 m-b-10">Revenue This Month</h4>
                            </a>
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ $this_month_income }} </h3>
                                    <!--<p class="mb-0">Revenue This Month</p>-->
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-success">

                            <a href="{{ route('receipt.index') }}">
                                <h4 class="header-title mt-0 m-b-10">OTC Fee This Month</h4>
                            </a>
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ $this_month_otc_charge }} </h3>

                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->can('dashboard_reseller revenue'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-success">

                            <h4 class="header-title mt-0 m-b-10">Reseller Rev Monthly</h4>

                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ number_format($this_month_reseller_revenue,2) }} </h3>
                                    <!--<p class="mb-0">Revenue This Month</p>-->
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_total dues'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-danger">
                            <a href="{{ route('client.due') }}">
                                <h4 class="header-title mt-0 m-b-10">TOTAL DUES</h4>
                            </a>

                            <div class="widget-chart-1">
                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-money"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ number_format($total_due,2) }} </h3>
                                        {{--<p class="mb-0">Due Clients - {{ $total_due_client }}</p>--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif


                @if($user->hasAnyRole('Reseller'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-primary">
                            <a href="{{route('report.reseller.profit')}}">
                                <h4 class="header-title mt-0 m-b-10">Profit This Month</h4>
                            </a>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-money"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{$total_this_month_reseller_profit, 2}} </h3>
                                        <!--<p class="mb-0">Active Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif


                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_total clients'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-default" style="background: #fa8231; color: #fff;">
                            <a href="{{ route('client.index') }}">
                                <h4 class="header-title mt-0 m-b-10">Total Clients</h4>
                            </a>
                            <div class="widget-chart-1">
                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-users"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ $total_client }} </h3>
                                        <!--<p class="mb-0">Total Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_active clients'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-primary">
                            <a href="{{ route('active.client') }}">
                                <h4 class="header-title mt-0 m-b-10">Active Clients</h4>
                            </a>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-user-circle-o"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ $active_client }} </h3>
                                        <!--<p class="mb-0">Active Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_old clients'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-info" style="background: #2bcbba;">
                            <a href="{{ route('old.client') }}">
                                <h4 class="header-title mt-0 m-b-10">OLD CLIENTS</h4>
                            </a>

                            <div class="widget-chart-1">
                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-user-times"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ $old_client }} </h3>
                                        <!--<p class="mb-0">Old Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif

                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_total due clients'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-danger">

                            <a href="{{ route('client.due') }}">
                                <h4 class="header-title mt-0 m-b-10">Due Clients</h4>
                            </a>

                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1" style="color: #fff;">
                                    <i class="fa fa-4x fa-users"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ $total_due_client }} </h3>
                                    <!--<p class="mb-0">Pending Complain</p>-->
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif



                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_total paid clients'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-success">
                            <a href="{{ route('receipt.index') }}">
                                <h4 class="header-title mt-0 m-b-10">Paid Clients</h4>
                            </a>
                            <div class="widget-chart-1">
                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-users"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ $total_paid_client }} </h3>
                                        <!--<p class="mb-0">Total Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_total discount client'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-info" style="background: #F97F51;">
                            <h4 class="header-title mt-0 m-b-10">Discount Clients</h4>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-users"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ $total_discount_client }} </h3>
                                        <!--<p class="mb-0">Active Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_total charged client'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-info" style="background: #fa8231;">
                            <h4 class="header-title mt-0 m-b-10">Extra Charge Clients</h4>

                            <div class="widget-chart-1">
                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-users"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ $total_charged_client }} </h3>
                                        <!--<p class="mb-0">Old Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif

                {{--</div>--}}

                {{--<div class="row justify-content-center">--}}

                @role('Reseller')
                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-success">
                        <h4 class="header-title mt-0 m-b-10">Available Balance</h4>

                        <div class="widget-chart-1">
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ number_format($reseller_balance,2) }} </h3>
                                    {{--<p class="mb-0">Total Client</p>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->
                @endrole


                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-danger">
                        <a href="{{ route('expanse.list') }}">
                            <h4 class="header-title mt-0 m-b-10">Mismatch Client</h4>
                        </a>

                        <div class="widget-chart-1">
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-users"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> Loading.. </h3>
                                    <!--<p class="mb-0">Old Clients</p>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->
                <!-- Online Clients Card -->
                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-success">
                        <a href="{{ route('pppoe.client.connected') }}">
                            <h4 class="header-title mt-0 m-b-10">Online Client</h4>
                        </a>
                        <div class="widget-chart-1">
                            <div class="widget-chart-box-1">
                                <i class="fa fa-4x fa-users"></i>
                            </div>
                            <div class="widget-detail-1">
                                <h3 id="online-client-count" class="p-t-10 mb-0">Loading..</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Offline Clients Card -->
                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-danger">
                        <a href="{{ route('pppoe.client.connected') }}">
                            <h4 class="header-title mt-0 m-b-10">Offline Client</h4>
                        </a>
                        <div class="widget-chart-1">
                            <div class="widget-chart-box-1">
                                <i class="fa fa-4x fa-user-times"></i>
                            </div>
                            <div class="widget-detail-1">
                                <h3 id="offline-client-count" class="p-t-10 mb-0">Loading..</h3>
                            </div>
                        </div>
                    </div>
                </div>

                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_expense this month'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-purple" style="background: #14d4a8; color: #fff;">
                            <a href="{{ route('expanse.list') }}">
                                <h4 class="header-title mt-0 m-b-10">Expense This Month</h4>
                            </a>

                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ number_format($this_month_expanse,2) }} </h3>
                                    <!--<p class="mb-0">Revenue This Month</p>-->
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif

                @role('Reseller')
                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-danger">
                        <a href="{{ route('receipt.seller') }}">
                            <h4 class="header-title mt-0 m-b-10">Total Recharge</h4>
                        </a>
                        <div class="widget-chart-1">
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ number_format($total_reseller_recharge,2) }} </h3>
                                    <!--<p class="mb-0">Total Clients</p>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->
                @endrole

                @if($user->can('dashboard_total reseller'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-inverse" style="background: #4b6584; color: #fff;">
                            <a href="{{ route('reseller.index') }}">
                                <h4 class="header-title mt-0 m-b-10">Total Resellers</h4>
                            </a>
                            <div class="widget-chart-1">
                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-user-circle"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ \App\Reseller::count() }} </h3>
                                        <!--<p class="mb-0">Total Resellers</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif


                @role('Super-Admin')
                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-inverse" style="background: #4b6584; color: #fff;">
                        <a href="{{ route('reseller.index') }}">
                            <h4 class="header-title mt-0 m-b-10"> Total Reseller Clients</h4>
                        </a>
                        <div class="widget-chart-1">
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-user-circle"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0">
                                        {{ \App\Client::where('active', 1)
                                                      ->where('server_status', 1)
                                                      ->where('status', 'On')
                                                      ->whereNotNull('resellerId')
                                                      ->count() }}
                                    </h3>

                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->
                @endrole
                {{-- @if($sms_balance == '' && ($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_total package')))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-primary">
                            @role('Reseller')
                                <a href="{{ route('reseller.plan.index') }}">
                            @else
                                <a href="{{ route('plan.index') }}">
                            @endrole
                                <h4 class="header-title mt-0 m-b-10">Total Package</h4>
                            </a>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-server"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0"> {{ $total_plans }} </h3>
                                        <!--<p class="mb-0">Active Clients</p>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif --}}

                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_pending complains'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-purple" style="background: #4b6584;">

                            <a href="{{ route('complain.pending') }}">
                                <h4 class="header-title mt-0 m-b-10">Pending Tickets</h4>
                            </a>

                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1" style="color: #fff;">
                                    <i class="fa fa-4x fa-headphones"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ $pendingComplain->count() }} </h3>
                                    <!--<p class="mb-0">Pending Complain</p>-->
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif

                @if($sms_balance != '' && $user->hasAnyRole('Super-Admin','Reseller'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-primary">
                            <a href="{{ route('config.sms.content') }}#settings">
                                <h4 class="header-title mt-0 m-b-10">
                                    SMS Balance
                                </h4>
                            </a>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-envelope"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        @if($sms_balance == "Not configured" || $sms_balance == "Checking balance…")
                                            <h4 class="p-t-10 mb-0" id="sms_balance">{{ $sms_balance }}</h4>
                                        @else
                                            <h3 class="p-t-10 mb-0" id="sms_balance">{{ $sms_balance }}</h3>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif


                @if($sms_balance != '' && $user->hasAnyRole('Super-Admin','Reseller'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-info">
                            <a href="{{ route('plan.index') }}">
                                <h4 class="header-title mt-0 m-b-10">
                                    Dummy Card
                                </h4>
                            </a>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-envelope"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0" id="sms_balance">   </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif

                @if($sms_balance != '' && $user->hasAnyRole('Super-Admin','Reseller'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-info" style="background: #F97F51;">
                            <a href="{{ route('plan.index') }}">
                                <h4 class="header-title mt-0 m-b-10">
                                    Dummy Card
                                </h4>
                            </a>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-envelope"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0" id="sms_balance">  </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif

                @if($sms_balance != '' && $user->hasAnyRole('Super-Admin','Reseller'))
                    <div class="col-xl-3 col-md-6 col-6 singleWidget">
                        <div class="card-box badge-primary">
                            <a href="{{ route('plan.index') }}">
                                <h4 class="header-title mt-0 m-b-10">
                                   Dummy Card
                                </h4>
                            </a>

                            <div class="widget-chart-1">

                                <div class="widget-chart-1">
                                    <div class="widget-chart-box-1">
                                        <i class="fa fa-4x fa-envelope"></i>
                                    </div>

                                    <div class="widget-detail-1">
                                        <h3 class="p-t-10 mb-0" id="sms_balance">   </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif


                @role('Reseller')
                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-inverse">
                        <h4 class="header-title mt-0 m-b-10">Rechargeable</h4>

                        <div class="widget-chart-1">
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <h3 class="p-t-10 mb-0"> {{ ($resellerRechagable>0)?$resellerRechagable:0 }} </h3>
                                    {{--<p class="mb-0">Total Client</p>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->
                @endrole

                @role('Super-Admin')
                <div class="col-xl-3 col-md-6 col-6 singleWidget">
                    <div class="card-box badge-warning" style="background: #f7b731; color: #fff;">
                        <h4 class="header-title mt-0 m-b-10">Deelko Bill</h4>

                        <div class="widget-chart-1">
                            <div class="widget-chart-1">
                                <div class="widget-chart-box-1">
                                    <i class="fa fa-4x fa-money"></i>
                                </div>

                                <div class="widget-detail-1">
                                    <?php
                                    $deelko_bill = \App\Client::where('server_status', 1)->count() * env('PER_CLIENT_BILL');
                                    $deelko_bill = ($deelko_bill > 500) ? $deelko_bill : 500;
                                    ?>
                                    <h3 class="p-t-10 mb-0"> {{ $deelko_bill }} Tk </h3>
                                    {{--<p class="mb-0">Total Client</p>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- end col -->
                @endrole
            </div>
            <!-- end row -->

            <div class="row">
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_expire client list'))
                    <div class="col-xl-7">
                        <div class="card-box">
                            <h4 class="header-title mt-0 m-b-20" style="font-weight: 400; color: #ffc107;">
                                @if($setting['expire_client_days']>0)
                                    Next {{ $setting['expire_client_days'] }} days's
                                @else
                                    Today's
                                @endif
                                Expire Clients ({{ count($exp_clients) }})
                            </h4>
                            <div class="table-responsive">
                                <table class="table mb-0 datatable">
                                    <thead>
                                    <tr>
                                        <th style="padding-right: 8px!important;">#</th>

                                        <th style="padding: 10px; margin: 0; width: 150px; white-space: nowrap;">
                                            Client Info
                                        </th>

                                        <th>Area</th>
                                        <th>Exp</th>
                                        <th>Bal</th>
                                        <th>Due</th>
                                        {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                                        {{--<th>Branch Name</th>--}}
                                        {{--@endif--}}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($i = 0)
                                    @foreach ($exp_clients as $exp_client)
                                        @php($i = $i+1)
                                        <tr>
                                            <td>{{$i}}</td>
                                            <td>
                                                <a href="{{ route('client.view', $exp_client->id) }}" target="_blank">
                                                    {{$exp_client->client_name}}
                                                    ({{ $exp_client->username }})
                                                    @if ($exp_client->phone)
                                                        <br>
                                                        <a href="tel:{{ $exp_client->phone }}">{{ $exp_client->phone }}</a>
                                                    @endif
                                                </a>
                                            </td>
                                            <td>{{ $exp_client->distribution->distribution }}</td>
                                            <td data-toggle="tooltip" data-placement="right"
                                                title="{{ date('h:i A', strtotime($exp_client->expiration)) }}">
                                                {{ date('d-M-y', strtotime($exp_client->expiration)) }}
                                            </td>
                                            <td>{{ number_format($exp_client->balance,2) }}</td>
                                            <td>{{ number_format($exp_client->due,2) }}</td>
                                            {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                                            {{--@php($clientData = \App\Client::with('branch')->find($exp_client->id)->branchName)--}}
                                            {{--@php((empty($clientData->branchId)) ? $branch_name = '-' : $branch_name = $clientData->branch->branchName)--}}
                                            {{--<td>{{ $branch_name }}</td>--}}
                                            {{--@endif--}}
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('dashboard_new client list'))
                    <div class="col-xl-5">
                        <div class="card-box">

                            <h4 class="header-title mt-0 m-b-20" style="font-weight: 400; color: #ffc107;">This Month's
                                New Clients ({{ count($newClients) }})</h4>

                            <div class="table-responsive">
                                <table class="table mb-0 datatable">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Client Name</th>
                                        <th>Reg Date</th>
                                        <th>Area/Box</th>
                                        {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                                        {{--<th>Branch Name</th>--}}
                                        {{--@endif--}}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($i = 0)
                                    @foreach ($newClients as $newClient)
                                        @php($i = $i+1)
                                        <tr>
                                            <td>{{ $i }}</td>
                                            <td><a href="{{ route('client.view', $newClient->id) }}"
                                                   target="_blank">{{ $newClient->client_name }}
                                                    ({{$newClient->username}})</a></td>
                                            <td>{{ $newClient->created_at->format('d M Y') }}</td>
                                            <td>{{ $newClient->distribution->distribution}}</td>
                                            {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                                            {{--@php($clientData = \App\Client::with('branch')->find($newClient->id)->branchName)--}}
                                            {{--@php((empty($clientData->branchId)) ? $branch_name = '-' : $branch_name = $clientData->branch->branchName)--}}
                                            {{--<td>{{ $branch_name }}</td>--}}
                                            {{--@endif--}}
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><!-- end col -->
                @endif
            </div>
        </div><!-- end col -->
    </div>
    <!-- end row -->
@endsection
<?php
if (Auth::user()->branchId != null) {
    $_branchName = Auth::user()->branch->branchName;
} else {
    $_branchName = '';
}
?>
@section('custom_js')
    <script type="text/javascript">
        $(document).ready(function () {

            //Buttons examples
            $('.datatable').DataTable({

                dom: 'Bfrtip',
                order: [],
                "pageLength": 20,
                "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    //debugger;
                    var index = iDisplayIndexFull + 1;
//            $("td:first", nRow).html(index);
                    return nRow;
                },
                buttons: ['pageLength',
                    {
                        extend: 'print',
                        text: 'Print',
                        autoPrint: true,
                        className: 'btn btn-info',
                        exportOptions: {
                            columns: [':not(.hidden-print)'],
                            modifier: {
                                page: 'current'
                            }
                        },

                        messageTop: function () {
                            return '<h2 class="text-center">{{ $_branchName }}</h2>'
                        },
                        messageBottom: 'Print: {{ date("d-M-Y") }}',
                        customize: function (win) {

                            $(win.document.body).find('h1').css('text-align', 'center');
                            $(win.document.body).find('table')
                                .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                                .addClass('compact')
                                .css('font-size', 'inherit', 'color', '#000');

                        }

                    }

                ]
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            function fetchDashboardData() {
                $.ajax({
                    url: "{{ route('pppoe.client.connected.data') }}",
                    type: "GET",
                    data: {
                        resellerId: "{{ Auth::check() ? Auth::user()->resellerId : 'all' }}",
                        stage: 'fast'
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'error') {
                            $('#online-client-count').text("0");
                            $('#offline-client-count').text("0");
                            $('#ajax-message').html(`<div class="alert alert-danger">${res.message}</div>`);
                            return;
                        }

                        //  Split counts
                        let onlineCount = res.clientData.filter(c => c.uptime !== 'router_off' && c.uptime !== 'using_no_microtik').length;
                        let offlineCount = res.clientData.filter(c => c.uptime === 'router_off' || c.uptime === 'using_no_microtik').length;

                        // Update cards
                        $('#online-client-count').text(onlineCount);
                        $('#offline-client-count').text(offlineCount);
                    },
                    error: function (xhr, status, error) {
                        console.error('Dashboard load failed:', error);
                    }
                });
            }

            // First load
            fetchDashboardData();


            // Refresh every 10 seconds
            setInterval(fetchDashboardData, 300000);
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let smsClientId = "{{ $sms_client_id }}"; // Get from Blade variable
            let url = `/get-sms-balance?client=${smsClientId}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.Balance !== undefined) {
                        document.getElementById("sms_balance").innerText = data.Balance;
                    }
                })
                .catch(error => console.error("Error fetching SMS balance:", error));
        });
    </script>

    {{--    <script>--}}
    {{--        document.addEventListener("DOMContentLoaded", function() {--}}
    {{--            let smsClientId = "info@siamtrading.com.bd"; // Directly assign the client ID--}}
    {{--            let url = `/Deelko/isp/get-sms-balance?client=${encodeURIComponent(smsClientId)}`; // Update path if needed--}}

    {{--            fetch(url)--}}
    {{--                .then(response => response.json())--}}
    {{--                .then(data => {--}}
    {{--                 //   console.log("SMS API Response:", data);--}}
    {{--                    // Check if Balance property exists at the top level--}}
    {{--                    if (data.Balance !== undefined) {--}}
    {{--                        document.getElementById("sms_balance").innerText = data.Balance + " TK"; // Corrected to access Balance directly--}}
    {{--                    } else {--}}
    {{--                        document.getElementById("sms_balance").innerText = "Balance not available";--}}
    {{--                    }--}}
    {{--                })--}}
    {{--                .catch(error => console.error("Error fetching SMS balance:", error));--}}
    {{--        });--}}


    {{--    </script>--}}

@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
@endsection
@section('custom_css')
    <style>
        .dataTable > thead > tr > th[class*=sort]:after {
            display: none;
        }

        .dataTable > thead > tr > th[class*=sort]:before {
            display: none;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
@endsection
