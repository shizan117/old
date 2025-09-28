<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
    Client Details
@endsection
@section('content')
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" href="#profile"
                       role="tab" data-toggle="tab"><i class="fa fa-user"></i> Profile</a>
                </li>
                {{--                <li class="nav-item">--}}
                {{--                    <a class="nav-link" href="#pay_history"--}}
                {{--                       role="tab" data-toggle="tab"><i class="fa fa-file-archive-o"></i> Payment History</a>--}}
                {{--                </li>--}}
                <li class="nav-item">
                    <a class="nav-link" href="#all_invoice"
                       role="tab"
                       data-toggle="tab"><i class="fa fa-file"></i> All Invoice</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#receipt" role="tab" data-toggle="tab"><i class="fa fa-money"></i>
                        Payment History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#complain" role="tab" data-toggle="tab"><i class="fa fa-cog"></i> Complain</a>
                </li>
            </ul>
            <div class="card-box p-0">
                <!-- Tab panes -->
                <div class="tab-content">
                    <!-- Profile Area -->
                    <div role="tabpanel" class="tab-pane active" id="profile">
                        <div class="panel panel-default panel-hovered panel-stacked mb30">
                            <div class="row">
                                <div class="col-lg-11 col-md-10 col-sm-10 col-xs-12 text-center">
                                    <div class="d-flex justify-content-center">
                                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_edit'))
                                            <a href="{{ route('client.edit', $client->id) }}"
                                               class="btn btn-outline-warning me-2"><i class="fa fa-edit"></i> Edit
                                                Profile</a>
                                        @endif
                                        @if($client->status == 'On' && setting('using_mikrotik') &&
                                            ($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_bandwidth graph')))
                                            <a href="javascript:void(0)" class="btn btn-outline-warning me-2"
                                               id="chartShow" data-toggle="modal" data-name="{{ $client->client_name }}"
                                               data-target="#trafficView" data-id="{{ $client->id}}">
                                                <i class="fa fa-bar-chart"></i> Bandwidth Graph
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-1 col-md-2 col-sm-2 col-xs-12 text-end">
                                    <a href="javascript:void(0)" onclick="printClientDetails()"
                                       class="btn btn-outline-primary">
                                        <i class="fa fa-print"></i> Print
                                    </a>
                                </div>
                                <div class="col-xl-6 row">

                                    <label class="col-5 col-lg-4">Full Name :</label>
                                    <div class="col-7 col-lg-8">
                                        {{ $client->client_name }}
                                    </div>

                                    <label class="col-5 col-lg-4">Username :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->username }}
                                    </div>

                                    <label class="col-5 col-lg-4">House No :</label>
                                    <div class="col-7 col-lg-8">
                                        {{ $client->house_no }}
                                    </div>

                                    <label class="col-5 col-lg-4">Road No :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->road_no }}
                                    </div>

                                    <label class="col-5 col-lg-4">Address Area :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->address}} {{$client->thana.', '.$client->district }} Bangladesh
                                    </div>

                                    <label class="col-5 col-lg-4">Email :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->email }}
                                    </div>

                                    <label class="col-5 col-lg-4">Phone :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->phone }}
                                    </div>
                                </div>
                                <div class="col-xl-6 row">

                                    <label class="col-5 col-lg-4">User Type :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->plan->type ?? ''}}
                                    </div>
                                    <label class="col-5 col-lg-4">OLT Type :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->olt_type }}
                                    </div>
                                    <label class="col-5 col-lg-4">Plan Name :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->plan->plan_name }}
                                    </div>

                                    @php(($client->server_status == 1) ? $s_status = 'Active' : $s_status = 'Inactive')
                                    <label class="col-5 col-lg-4">Server Status :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $s_status }}
                                    </div>
                                    @php(($client->status == 'On') ? $status = 'Active' : $status = 'Inactive')
                                    <label class="col-5 col-lg-4">Active Status :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $status }}
                                    </div>

                                    <label class="col-5 col-lg-4">Register Date :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $client->created_at->format('d M Y') }}
                                    </div>
                                    <label class="col-5 col-lg-4">Expire Date :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ date('d-M-Y h:i:s A', strtotime($client->expiration)) }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="panel-body form-horizontal">

                                        @can('client_increase expire date')
                                            <h4>Increase Expire Date</h4>
                                            <form class="form-horizontal"
                                                  action="{{ route('client.increase.exp.date', $client->id) }}"
                                                  role="form"
                                                  method="POST">
                                                {{ csrf_field() }}
                                                <div class="form-group row">
                                                    <label class="col-md-4 col-form-label">New Expire Date:</label>
                                                    <div class="col-md-7" style="text-align: left">
                                                        <div class="input-group {{ $errors->has('exp_date') ? 'has-error' : '' }}">
                                                            <input type="text" autocomplete="off" name="exp_date"
                                                                   class="form-control"
                                                                   placeholder="yyyy-mm-dd"
                                                                   id="datepicker">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text"><i
                                                                            class="ti-calendar"></i></span>
                                                                <button type="submit"
                                                                        onclick="return confirm('Do you want to increase expire date?')"
                                                                        class="btn btn-info waves-effect waves-light">
                                                                    Submit
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <span class="text-danger">{{ $errors->first('exp_date') }}</span>
                                                    </div>
                                                </div>
                                            </form>
                                        @endcan
                                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_change plan'))
                                            <h4>
                                                Change Plan
                                                @if($client->plan_changed_at != '')
                                                    <small class="text-warning">- (Last Changed
                                                        - {{ date('d-M-Y',strtotime($client->plan_changed_at)) }}
                                                        )</small>
                                                @endif
                                            </h4>
                                            <form class="form-horizontal"
                                                  action="{{ route('client.change.plan', $client->id) }}" role="form"
                                                  method="POST">
                                                {{ csrf_field() }}
                                                <div class="form-group row">
                                                    <label class="col-md-4 col-form-label">New Plan Name :</label>
                                                    <div class="col-md-7">
                                                        <div class="input-group {{ $errors->has('plan_name') ? 'has-error' : '' }}">
                                                            <select class="form-control" name="plan_id">
                                                                <option value="">Select Plan Name</option>
                                                                @foreach($planDatas as $plan)
                                                                    <option value="{{ $plan->id }}" {{ ($plan->id == $client->plan_id) ? 'selected':'' }}>
                                                                        {{ $plan->plan_name }}
                                                                        @unlessrole('Reseller')
                                                                        - {{ $plan->type }}
                                                                        - {{ $plan->server->server_name }}
                                                                        @endunlessrole
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="input-group-append">
                                                                <button type="submit"
                                                                        class="btn btn-info waves-effect waves-light"
                                                                        onclick="return confirm('Do you want to update the plan?')">
                                                                    Submit
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <span class="text-danger">{{ $errors->first('plan_name') }}</span>
                                                    </div>
                                                </div>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="panel-body form-horizontal">

                                        @can('client_transfer client')
                                            <h4>Client Transfer</h4>
                                            <form class="form-horizontal" action="{{ route('client.transfer') }}"
                                                  role="form" method="POST">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id" value="{{ $client->id }}">
                                                <div class="form-group row">
                                                    <label class="col-md-4 col-form-label">Transfer Client To:</label>
                                                    <div class="col-md-7">
                                                        <div class="input-group {{ $errors->has('transfer_client') ? 'has-error' : '' }}">
                                                            <select class="form-control" name="resellerId">
                                                                <option value="">Super Admin</option>
                                                                @foreach($resellers as $reseller)
                                                                    <option value="{{$reseller->resellerId }}" {{ ($reseller->resellerId == $client->resellerId) ? 'selected':'' }}>{{$reseller->resellerName}}</option>
                                                                @endforeach
                                                            </select>
                                                            <div class="input-group-append">
                                                                <button type="submit"
                                                                        class="btn btn-info waves-effect waves-light"
                                                                        onclick="return confirm('Do you want to transfer the client?')">
                                                                    Transfer
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <span class="text-danger">{{ $errors->first('transfer_client') }}</span>
                                                    </div>
                                                </div>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--                    <!-- Pay History Area -->--}}
                    {{--                    <div role="tabpanel" class="tab-pane" id="pay_history">--}}

                    {{--                        <div class="md-whiteframe-z1" style="padding: 10px; margin: 10px 0">--}}

                    {{--                            <div>--}}
                    {{--                                <table id="" class="table table-responsive-md table-responsive-lg table-striped table-bordered text-center">--}}
                    {{--                                    <thead>--}}
                    {{--                                    <tr>--}}

                    {{--                                        <th>Total Bill</th>--}}
                    {{--                                        <th>Total Vat</th>--}}
                    {{--                                        <th>All Total</th>--}}
                    {{--                                        <th>Total Paid</th>--}}
                    {{--                                        <th>Total Due</th>--}}
                    {{--                                        <th>Last Paid</th>--}}
                    {{--                                        <th>Advance Balance</th>--}}
                    {{--                                        <th>Last Payment Date</th>--}}
                    {{--                                    </tr>--}}
                    {{--                                    </thead>--}}
                    {{--                                    <tbody>--}}
                    {{--                                    <tr>--}}

                    {{--                                        <td>{{ \App\Invoice::where('client_id', $client->id)->sum('all_total') }}</td>--}}
                    {{--                                        <td>{{ \App\Invoice::where('client_id', $client->id)->sum('vat') }}</td>--}}
                    {{--                                        <td>{{ \App\Invoice::where('client_id', $client->id)->sum('sub_total') }}</td>--}}
                    {{--                                        <td>{{ \App\Invoice::where('client_id', $client->id)->sum('paid_amount') }}</td>--}}
                    {{--                                        <td>{{ \App\Invoice::where('client_id', $client->id)->sum('due') }}</td>--}}
                    {{--                                        @if(!empty($lsPay))--}}
                    {{--                                            <td>{{ $lsPay->new_paid }}</td>--}}
                    {{--                                            <td>{{ $client->balance }}</td>--}}
                    {{--                                            <td>{{ date('d-M-Y',strtotime($lsPay->payment_date)) }}</td>--}}
                    {{--                                        @else--}}
                    {{--                                            <td>-</td>--}}
                    {{--                                            <td>{{ $client->balance }}</td>--}}
                    {{--                                            <td>-</td>--}}
                    {{--                                        @endif--}}

                    {{--                                    </tr>--}}

                    {{--                                    </tbody>--}}

                    {{--                                </table>--}}

                    {{--                            </div>--}}

                    {{--                            <div style="margin-top: 10px">--}}
                    {{--                                <h4 style="text-align:center;">Payment By Date</h4>--}}
                    {{--                                <table id="" class="table table- table-responsive-lg table-sm table-striped table-bordered display text-center">--}}
                    {{--                                    <thead>--}}
                    {{--                                    <tr>--}}

                    {{--                                        <th style="text-align: center;">Payment Date</th>--}}
                    {{--                                        <th style="text-align: center;">Payment Amount</th>--}}
                    {{--                                    </tr>--}}
                    {{--                                    </thead>--}}
                    {{--                                    <tbody>--}}
                    {{--                                    @foreach($paydetails as $dphd)--}}
                    {{--                                        <tr>--}}
                    {{--                                            @php($pamount = \App\ClientPayment::whereDate('payment_date',--}}
                    {{--                                            $dphd->date)->where('client_id',$client->id)->sum('new_paid'))--}}
                    {{--                                            <td>{{ $dphd->date }}</td>--}}
                    {{--                                            <td>{{ $pamount }}</td>--}}

                    {{--                                        </tr>--}}
                    {{--                                    @endforeach--}}
                    {{--                                    </tbody>--}}
                    {{--                                </table>--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}

                    {{--                    </div>--}}

                    <!-- Invoice Area -->
                    <div role="tabpanel" class="tab-pane" id="all_invoice">

                        <div style="margin-top: 10px">
                            <table id=""
                                   class="table table-sm table-responsive-md table-responsive-lg table-striped table-bordered display text-center">
                                <thead>
                                <tr>
                                    <th style="min-width: 80px;">Invoice No</th>
                                    <th style="min-width: 120px;">Bill Month</th>
                                    <th>Bandwidth</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th style="min-width: 90px;">Create Date</th>
                                    <th style="min-width: 90px;">Paid Date</th>
                                    {{--                                    <th style="min-width: 125px;">Paid Time</th>--}}

                                    {{--<th>Manage</th>--}}
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($invoices as $ins)
                                    <tr>
                                        <td><a href="{{ route('invoice.show', $ins->id) }}"
                                               target="_blank">{{$ins->id}}</a></td>
                                        <td>{{ date('M', mktime(0, 0, 0, $ins->bill_month, 1)) }}
                                            - {{ $ins->bill_year }}</td>
                                        <td>{{ $ins->bandwidth }}</td>
                                        <td>{{ $ins->sub_total }}</td>
                                        <td>{{ $ins->paid_amount ??'0.00'}}</td>
                                        <td>{{ $ins->due }}</td>
                                        <td>{{ $ins->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            @if ($ins->updated_at->diffInSeconds($ins->created_at) >= 3 ||
                                                $ins->paid_amount > 0)

                                                {{ $ins->updated_at->format('d-M h:i A') }}
                                            @else

                                                Pending
                                            @endif
                                        </td>
                                        {{--                                        <td>{{ $ins->updated_at->format('d-m-Y h:i A') }}</td>--}}


                                        {{--                                        <td><a href="{{ route('invoice.pay', $ins->id) }}" target="_blank" class="btn-view"><i class="fa fa-product-hunt"></i></a></td>--}}
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                    <!-- Money Receipt Area -->
                    <div role="tabpanel" class="tab-pane" id="receipt">
                        <div>
                            <h4 style="text-align:center;">Payment Summary</h4>
                            <table id=""
                                   class="table table-responsive-md table-responsive-lg table-striped table-bordered text-center">
                                <thead>
                                <tr>
                                    {{--                                    <th>Total Vat</th>--}}
                                    {{--                                    <th>All Total</th>--}}
                                    <th>Total Bill</th>
                                    <th>Total Paid</th>
                                    <th>Total Due</th>
                                    <th>Last Paid</th>
                                    <th>Advance Balance</th>
                                    <th>Last Payment Date</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    {{--                                    <td>{{ \App\Invoice::where('client_id', $client->id)->sum('all_total') }}</td>--}}
                                    {{--                                    <td>{{ \App\Invoice::where('client_id', $client->id)->sum('vat') }}</td>--}}
                                    <td>{{ \App\Invoice::where('client_id', $client->id)->sum('sub_total') }}</td>
                                    <td>{{ \App\ClientPayment::where('client_id', $client->id)->sum('new_paid') }}</td>
                                    <td>{{ \App\Invoice::where('client_id', $client->id)->sum('due') }}</td>
                                    @if(!empty($lsPay))
                                        <td>{{ $lsPay->new_paid }}</td>
                                        <td>{{ $client->balance }}</td>
                                        <td>{{ date('d-M-Y',strtotime($lsPay->payment_date)) }}</td>
                                    @else
                                        <td>-</td>
                                        <td>{{ $client->balance }}</td>
                                        <td>-</td>
                                    @endif
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 10px">
                            <h4 style="text-align:center;">Payment History</h4>
                            <table id=""
                                   class="table table-responsive-md table-responsive-lg table-sm table-striped table-bordered display">
                                <thead>
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Entry Date</th>
                                    <th>New Paid</th>
                                    <th>Pay From Advance</th>
                                    <th>Total Paid</th>
                                    <th>Paid Type</th>
                                    <th>Money Receipt</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($receipts as $smr)
                                    <tr>
                                        <td>{{ date('d-M-Y',strtotime($smr->payment_date)) }}</td>
                                        <td>{{ date('d-M-Y',strtotime($smr->created_at)) }}</td>
                                        <td>{{ $smr->new_paid }}</td>
                                        <td>{{ $smr->paid_from_advance ?? '0.00' }}</td>
                                        <td>{{ number_format($smr->new_paid + $smr->paid_from_advance,2,'.','') }}</td>
                                        <td>
                                            @if($smr->paid_from_advance > 0)
                                                Paid from Advance
                                            @else
                                                {{ $smr->transaction->account->account_name ?? 'Online Payment' }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('receipt.show', $smr->id) }}"
                                               target="_blank" class="btn-show"><i
                                                        class="fa fa-eye"></i></a>
                                            @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('receipt_print'))
                                                <a href="{{ route('receipt.print', $smr->id) }}" class="btn-show"
                                                   target="_blank"><i class="fa fa-print"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Complain Area -->
                    <div role="tabpanel" class="tab-pane" id="complain">

                        <div style="margin-top: 10px">
                            <table id=""
                                   class="table table-sm table-responsive-md table-responsive-lg table-striped table-bordered display">
                                <thead>
                                <tr>
                                    <th>Complain No</th>
                                    <th>Complain Date</th>
                                    <th>Complain Title</th>
                                    <th>Description</th>
                                    <th>Solved Date</th>
                                    <th>Assign To</th>
                                    <th>Status</th>
                                </tr>
                                </thead>


                                <tbody>
                                @foreach ($complainData as $complain)
                                    <tr>
                                        <td>{{ $complain->id }}</td>
                                        <td>{{ date('d-M-y (h:i:a)',strtotime($complain->complain_date))}}</td>
                                        <td>{{ $complain->title }}</td>
                                        <td>{{ $complain->description }}</td>
                                        <td>{{ $complain->solved_date?date('d-M-y (h:i:a)',strtotime($complain->solved_date)):'-'}}</td>
                                        <td>{{ $complain->assignTo->name??'-' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $complain->is_solved==0? 'danger':'success' }}">{{ $complain->is_solved==0? 'Not Solved':'Solved' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Add traffic modal -->
    <div class="modal fade text-left" id="trafficView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
         aria-hidden="true">
        <div class="modal-dialog traffic-graph modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <label class="modal-title text-text-bold-600 text-warning" id="myModalLabel33"
                           style="font-size: 20px;">
                        <span class="fa fa-bar-chart"></span> Traffic View</label>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="container">
                    <div id="container" style="min-width: 67%; margin: 0 auto"></div>
                    <input type=hidden name="interface" id="interface"/>
                    <div id="traffic" class="pl-3 mt-3 pb-3"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End -->

    {{--    new client info print page--}}
    <div id="printArea" style="display:none; font-family: kalpurush, sans-serif; font-size: 14px;">
        <div class="header" style="padding:10px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                <div style="display: flex; align-items: center;">
                    <img src="{{ asset("assets/images/".$config['logo']) }}" alt="Logo"
                         style="height: 50px; margin-right: 15px;">
                    <div>
                        <h2 style="margin: 0; font-size: 24px;">{!! $config['companyName'] ?? '' !!}</h2>
                        <small style="font-size: 14px;">Internet Service Provider</small>
                    </div>
                </div>
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; border: 1px solid #000; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{  asset("assets/uploads/client_photos/".$client->client_photo) }}" alt="গ্রাহকের ছবি"
                             style="max-width: 100%; max-height: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
            <hr>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <p style="margin: 0; font-weight: bold;">ক্রম নং {{$client->username }}</p>
                <div style="border: 1px solid #000; padding: 5px; text-align: center;">
                    <p style="margin: 0; font-weight: bold; font-size: 15px;">গ্রাহক নিবন্ধন ফরম</p>
                </div>
                <p style="margin: 0; font-weight: bold;">তারিখ:  {{ $client->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <div class="print-body" style="padding: 10px;">
            <p class="line-item" style="margin-bottom: 5px;"><strong>১. গ্রাহকের নাম
                    (Name):</strong> {{ $client->client_name ?? 'N/A' }}</p>
            <p class="line-item" style="margin-bottom: 5px;"><strong>২. গ্রাহকের ঠিকানা
                    (Address):</strong> {{ $client->address ?? 'N/A' }}</p>
            <p class="line-item" style="margin-bottom: 5px;"><strong>৩. গ্রাহকের জাতীয় পরিচয়
                    (NID):</strong> {{ $client->clientNid ?? 'N/A' }}</p>
            <p class="line-item" style="margin-bottom: 5px;"><strong>৪. গ্রাহকের মোবাইল নম্বর
                    (Cell):</strong> {{ $client->phone ?? 'N/A' }}</p>
            <p class="line-item" style="margin-bottom: 5px;"><strong>৫. গ্রাহকের আইপি
                    (IP):</strong> {{ $client->client_ip ?? 'N/A' }}</p>
            <p class="line-item" style="margin-bottom: 5px;"><strong>৬. সার্ভিস চার্জ
                    (OTC):</strong> {{ $client->otc_charge ?? 'N/A' }}</p>
            <p class="line-item" style="margin-bottom: 5px;"><strong>৭. মাসিক প্যাকেজ (Package
                    Type):</strong> {{ $client->plan->plan_name ?? 'N/A' }}</p>
            <p class="line-item" style="margin-bottom: 0;"><strong>৮. মাসিক বিল (Monthly Pre-Paid
                    Bill):</strong> {{ $client->plan->plan_price ?? 'N/A' }}</p>
        </div>

        <div class="terms" style="padding: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 5px;">
                <p style="margin: 0; font-weight: bold;"></p>
                <div style="border: 1px solid #000; padding: 5px; text-align: center;">
                    <p style="margin: 0; font-weight: bold; font-size: 15px;">শর্তাবলী</p>
                </div>
                <p style="margin: 0; font-weight: bold;"></p>
            </div>
            <div style="line-height: 1.5;">
                {!! $config['new_client_terms_condition'] ?? '' !!}
            </div>
        </div>

        <div class="signatures-section"
             style="display: flex; justify-content: space-between; padding: 10px; margin-top: 30px;">
            <div style="text-align: center; width: 18%;">
                <div style="border-bottom: 1px solid #000; height: 60px;"></div>
                <p style="margin-bottom: 5px; font-weight: bold;">গ্রাহকের স্বাক্ষর</p>
            </div>
            <div style="text-align: center; width: 18%;">
                <div style="border-bottom: 1px solid #000; height: 60px;"></div>
                <p style="margin-bottom: 5px; font-weight: bold;">কর্তৃপক্ষের স্বাক্ষর</p>
            </div>
        </div>

        <div class="print-footer">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 20px; padding: 20px 20px; box-sizing: border-box;">

                <div style="display: flex; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                         fill="currentColor" style="margin-right: 5px;">
                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.24 1.02l-2.2 2.2z"/>
                    </svg>
                    <span>{!! $config['phone'] ?? '' !!}</span>
                </div>

                <div style="display: flex; align-items: flex-start; text-align: left;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                         fill="currentColor" style="margin-right: 5px; margin-top: 2px;">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                    </svg>
                    <span>{!! $config['address'] ?? '' !!}</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media screen {
            #printArea {
                margin: 20px auto;
                border: 1px solid #ccc;
                width: 210mm;
                min-height: 297mm;
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
                position: relative;
                padding-bottom: 100px;
            }

            .signatures-section {
                position: relative;
                margin-top: 50px;
            }

            .print-footer {
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                background-color: white;
                z-index: 100;
            }
        }

        @media print {
            html, body {
                height: auto !important;
                margin: 0;
                padding: 0;
                overflow: visible;
            }

            @page {
                size: A4;
                margin: 0;
            }

            .page {
                page-break-after: always;
                width: 210mm;
                min-height: 297mm;
                box-sizing: border-box;
                padding: 10mm;
                position: relative;
                padding-bottom: 55mm;
            }

            .print-body-content {
                margin-bottom: 10px;
            }

            .terms {
                margin-top: 10px;
                margin-bottom: 5px;
            }

            .signatures-section {
                page-break-inside: avoid;
                margin-top: 20px;
                margin-bottom: 20px;
            }

            .print-footer-container {
                position: absolute;
                bottom: 15mm;
                left: 0;
                width: 100%;
                background-color: white;
            }

            .print-footer-container hr {
                margin: 0;
            }
        }
    </style>

@endsection


@section('custom_js')

    <script>
        function printClientDetails() {
            const maxLinesPerPage = 26;
            const printArea = document.getElementById("printArea");


            const clonedPrintArea = printArea.cloneNode(true);


            const lines = Array.from(clonedPrintArea.querySelectorAll(".print-body .line-item"));


            const termsHtml = clonedPrintArea.querySelector(".terms").outerHTML;
            const signaturesHtml = clonedPrintArea.querySelector(".signatures-section").outerHTML;
            const headerHtml = clonedPrintArea.querySelector(".header").outerHTML;

            const footerInnerHtml = `
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 16px; padding: 20px 50px; box-sizing: border-box;">


                <div style="display: flex; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"
                         fill="currentColor" style="margin-right: 5px;">
                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.24 1.02l-2.2 2.2z"/>
                    </svg>
                    <span>{!! $config['phone'] ?? '' !!}</span>
                </div>



            <div style="display: flex; align-items: flex-start; text-align: left;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16"
                     fill="currentColor" style="margin-right: 5px; margin-top: 2px;">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                </svg>
                <span>{!! $config['address'] ?? '' !!}</span>
            </div>
        </div>
    `;

            let pages = [];
            let currentPageLines = [];


            lines.forEach((line, idx) => {
                currentPageLines.push(line.outerHTML);
                if ((idx + 1) % maxLinesPerPage === 0) {
                    pages.push(currentPageLines);
                    currentPageLines = [];
                }
            });
            if (currentPageLines.length > 0) {
                pages.push(currentPageLines);
            }


            let htmlPages = pages.map((pageLines, pageIndex) => {
                const isFirstPage = (pageIndex === 0);
                const isLastPage = (pageIndex === pages.length - 1);

                return `
        <div class="page" style="page-break-after: always; padding: 10mm; box-sizing: border-box; width: 210mm; min-height: 297mm; font-family: kalpurush, sans-serif; font-size: 14px;">
            ${isFirstPage ? headerHtml : ''}
            <div class="print-body-content">
                ${pageLines.join('')}
            </div>
            ${isLastPage ? termsHtml : ''}
            ${isLastPage ? signaturesHtml : ''}
            <div class="print-footer-container" style="position: absolute; bottom: 0; left: 0; width: 100%;">
                <hr style="margin: 0; border-top: 1px solid #eee;">
                ${footerInnerHtml}
            </div>
        </div>
    `;
            }).join('');


            const WinPrint = window.open('', '', 'width=900,height=650');
            WinPrint.document.write(`
    <html>
    <head>
        <title>Client Info</title>
        <style>
            body { margin: 0; padding: 0; }
            @media print {
                @page {
                    size: A4;
                    margin: 0;
                }
                body { margin: 0; padding: 0; }
                .page {
                    page-break-after: always;
                    width: 210mm;
                    min-height: 297mm;
                    box-sizing: border-box;
                    padding: 10mm;
                    position: relative;

                    padding-bottom: 55mm;
                }
                .print-body-content {
                    margin-bottom: 10px;
                }
                .terms {
                    margin-top: 10px;
                    margin-bottom: 5px;
                }
                .signatures-section {
                    margin-top: 20px;

                    margin-bottom: 20px;
                    page-break-inside: avoid;
                }
                .print-footer-container {
                    position: absolute;

                    bottom: 15mm;
                    left: 0;
                    width: 100%;
                    background-color: white;
                }

                .print-footer-container hr {
                    margin: 0;
                }
            }
        </style>
    </head>
    <body>
        ${htmlPages}
        <script>
            window.onload = function() {
                window.focus();
                window.print();
                window.close();
            };
        <\/script>
    </body>
    </html>
`);
            WinPrint.document.close();
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            });
            $('table.display').DataTable({
                dom: 'Bfrtip',
                "pageLength": 20,
                "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                buttons: ['pageLength'],
                "order": []
            });
        });


        var interVal;
        var chart;

        function requestDatta(id) {
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "{{ route('get.pppoe.traffic') }}",
                data: {id: id, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    var midata = data;
                    if (midata.length > 0) {
                        var TX = parseInt(midata[0].data);
                        var RX = parseInt(midata[1].data);
                        var x = (new Date()).getTime();
                        shift = chart.series[0].data.length > 19;
                        chart.series[0].addPoint([x, TX], true, shift);
                        chart.series[1].addPoint([x, RX], true, shift);
                        if (isNaN(TX)) {
                            TX = '-';
                        } else {
                            if (TX >= 1024) {
                                TX = parseFloat(TX / 1024).toFixed(2) + 'Mbps';
                            } else {
                                TX = parseFloat(TX).toFixed(2) + 'Kbps';
                            }
                        }
                        if (isNaN(RX)) {
                            RX = '-';
                        } else {
                            if (RX >= 1024) {
                                RX = parseFloat(RX / 1024).toFixed(2) + 'Mbps';
                            } else {
                                RX = parseFloat(RX).toFixed(2) + 'Kbps';
                            }
                        }
                        document.getElementById("traffic").innerHTML = TX + " / " + RX;
                    } else {
                        document.getElementById("traffic").innerHTML = "- / -";
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.error("Status: " + textStatus + " request: " + XMLHttpRequest);
                    console.error("Error: " + errorThrown);
                }
            });
        }

        $(document).ready(function () {
            $(document).on("click", "#chartShow", function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                var c_name = $(this).data('name');
                $('#interface').val(id);
                Highcharts.setOptions({
                    global: {
                        useUTC: false
                    }
                });


                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'container',
                        animation: Highcharts.svg,
                        type: 'area', //line,//area
                        events: {
                            load: function () {
                                interVal = setInterval(function () {
                                    requestDatta(document.getElementById("interface").value);
                                }, 1000);
                            }
                        }
                    },
                    title: {
                        text: 'Monitoring (' + c_name + ')'
                    },
                    xAxis: {
                        type: 'datetime',
                        tickPixelInterval: 150,
                        maxZoom: 20 * 1000
                    },
                    yAxis: {
                        minPadding: 0.2,
                        maxPadding: 0.2,
                        title: {
                            text: 'Traffic',
                            margin: 50
                        },
                        labels: {
                            formatter: function () {
                                if (this.value >= 1024) {
                                    return parseFloat(this.value / 1024).toFixed(0) + 'Mbps';
                                }
                                return parseFloat((this.value / 128) * 100).toFixed(0) + 'Kbps';
                            }
                        }
                    },
                    series: [{
                        name: 'TX',
                        data: []
                    }, {
                        name: 'RX',
                        data: []
                    }]
                });
            });
            $("#trafficView").click(function () {
                clearInterval(interVal);
            })

        })
    </script>
@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
    <link href='{{ asset("assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css") }}'
          rel="stylesheet" type="text/css"/>
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
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/highchart/js/highcharts.js') }}"></script>
    <script src="{{ asset('assets/highchart/js/themes/grid.js') }}"></script>
@endsection
