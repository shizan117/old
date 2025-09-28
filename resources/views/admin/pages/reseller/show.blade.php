@extends ('admin.layouts.master')
@section('title')
    Reseller Details
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Nav tabs -->
            <ul class="nav nav-tabs nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" href="#profile"
                       role="tab" data-toggle="tab"><i class="fa fa-user"></i> Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#pay_history"
                       role="tab" data-toggle="tab"><i class="fa fa-file-archive-o"></i> Payment History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#receipt" role="tab" data-toggle="tab"><i class="fa fa-money"></i> Money
                        Receipt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#plans" role="tab" data-toggle="tab"><i class="fa fa-cog"></i> Plans</a>
                </li>
            </ul>
            <div class="card-box">
                <!-- Tab panes -->
                <div class="tab-content">
                    <!-- Profile Area -->
                    <div role="tabpanel" class="tab-pane active" id="profile">
                        <div class="panel panel-default panel-hovered panel-stacked mb30">
                            <div class="row">

                                <div class="col-xl-6 row">
                                    <label class="col-5 col-lg-4">Reseller Name :</label>
                                    <div class="col-7 col-lg-8">
                                        {{ $reseller->resellerName }}
                                    </div>
                                    <label class="col-5 col-lg-4">Location :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $reseller->resellerLocation }}
                                    </div>
                                    <label class="col-5 col-lg-4">Balance :</label>
                                    <div class="col-7 col-lg-8" style="text-align: left">
                                        {{ $reseller->balance }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pay History Area -->
                    <div role="tabpanel" class="tab-pane" id="pay_history">

                        <div class="md-whiteframe-z1" style="padding: 10px; margin: 10px 0">

                            <div>
                                <table id="" class="table table-responsive-md table-responsive-lg table-striped table-bordered text-center">
                                    <thead>
                                    <tr>
                                        <th>Total Paid</th>
                                        <th>Last Paid</th>
                                        <th>Current Balance</th>
                                        <th>Last Payment Date</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ \App\ResellerPayment::where('resellerId', $reseller->resellerId)->sum('recharge_amount') }}</td>
                                            @if(!empty($lsPay))
                                                <td>{{ $lsPay->recharge_amount }}</td>
                                                <td>{{ $reseller->balance }}</td>
                                                <td>{{ $lsPay->created_at->format('d-M-Y') }}</td>
                                            @else
                                                <td>-</td>
                                                <td>{{ $reseller->balance }}</td>
                                                <td>-</td>
                                            @endif
                                        </tr>
                                    </tbody>

                                </table>

                            </div>

                            <div style="margin-top: 10px">
                                <h4 style="text-align:center;">Payment By Date</h4>
                                <table id="" class="table table- table-responsive-lg table-sm table-striped table-bordered display text-center">
                                    <thead>
                                    <tr>
                                        <th style="text-align: center;">Payment Date</th>
                                        <th style="text-align: center;">Payment Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($paydetails as $dphd)
                                        <tr>
                                            @php($pamount = \App\ResellerPayment::whereDate('created_at',
                                            $dphd->date)->where('resellerId',$reseller->resellerId)->sum('recharge_amount'))
                                            <td>{{ $dphd->date }}</td>
                                            <td>{{ $pamount }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Money Receipt Area -->
                    <div role="tabpanel" class="tab-pane" id="receipt">
                        <div style="margin-top: 10px">
                            <table id="" class="table table-responsive-md table-responsive-lg table-sm table-striped table-bordered display">
                                <thead>
                                <tr>
                                    <th style="text-align: center;">Payment Date</th>
                                    <th style="text-align: center;">Amount</th>
                                    <th style="text-align: center;">View Money Receipt</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($receipts as $smr)
                                    <tr>
                                        <td>{{ $smr->created_at->format('d-M-Y') }}</td>
                                        <td>{{ $smr->recharge_amount }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('receipt.seller.show', $smr->id) }}" target="_blank" class="btn-show">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Plans Area -->
                    <div role="tabpanel" class="tab-pane" id="plans">
                        <div style="margin-top: 10px">
                            <table id="" class="table table-sm table-responsive-md table-responsive-lg table-striped table-bordered display text-center" width="100%">
                                <thead>
                                <tr>
                                    <th>Plan Name</th>
                                    <th>Type</th>
                                    <td>Main Plan Price</td>
                                    <th>Sell Price</th>
                                    <th>Duration</th>
                                    <th class="hidden-print">Manage</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach ($planData as $data)
                                    @php(($data->plan->duration > 1) ?
                                    (($data->plan->duration_unit == 1) ? $unit = 'Months' : $unit = 'Days') :
                                    (($data->plan->duration_unit == 1) ? $unit = 'Month' : $unit = 'Day'))
                                    <tr>
                                        <td>{{ $data->plan->plan_name }}</td>
                                        <td>{{ $data->plan->type }}</td>
                                        <td>{{ $data->plan->plan_price }}</td>
                                        <td>{{ $data->sell_price }}</td>
                                        <td>{{ $data->plan->duration }} {{ $unit }}</td>
                                        <td class="hidden-print">
                                            <a href="{{ route('reseller.plan.edit', $data->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
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
@endsection

@section('custom_js')
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
@endsection