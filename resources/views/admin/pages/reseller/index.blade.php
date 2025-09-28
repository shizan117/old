@extends ('admin.layouts.master')
@section('title')
    Reseller List
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <h4 class="m-t-0 header-title pull-left">Reseller List</h4>
                @can('reseller_add')
                    <a href="{{ route('reseller.add') }}" class="btn btn-primary pull-right mb-1">Add Reseller</a>
                @endcan
                <div class="table-responsive">
                    <table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Reseller Name</th>
                            <th>Reseller Location</th>
                            {{-- <th>Credit Limit</th> --}}
                            <th>Balance</th>
                            <th>Rechargable </th>
                            {{-- <th>Due</th> --}}

                            <th>Active Clients</th>
                            <th>Inactive Clients</th>

                            @if ($setting['invoice_system'] == 'fixed')
                                <th>Client Expire Date</th>
                            @endif
                            <th class="hidden-print">Manage</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Extra Charge</th>
                            <th class="text-center">PLan Price</th>
                        </tr>
                        </thead>
                        <tbody>

                        @php
                            $totalRechargeableAmounts = 0; // Initialize the variable outside the loop
                        @endphp

                        @foreach ($data as $reseller)
                            @php
                                $totalActiveClients = DB::table('clients')
                                    ->where('server_status', 1)
                                    ->where('status', 'On')
                                    ->where('active', 1)
                                    ->where('resellerId', $reseller->resellerId)
                                    ->count();

                                    $totalInActiveClients = DB::table('clients')
                                    ->where('server_status', 1)
                                    ->where('status', 'Off')
                                    ->where('active', 1)
                                    ->where('resellerId', $reseller->resellerId)
                                    ->count();
                            @endphp


                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $reseller['resellerName'] }}</td>
                                <td>{{ $reseller['resellerLocation'] }}</td>
                                {{--                            <td>{{ $reseller['credit_limit'] }}</td> --}}
                                <td>{{ $reseller['balance'] }}</td>
                                @if (Auth::user()->hasAnyRole(['Super-Admin', 'Admin']))
                                    {{-- @role('Super-Admin') --}}
                                        <?php
                                        $resellerRechagable = 0;
                                        $total_reseller_recharge = \App\ResellerPayment::where('resellerId', $reseller['resellerId'])->sum('recharge_amount');
                                        $totalBuyPrice = \App\Invoice::whereHas('client', function ($query) use ($reseller) {
                                            $query->where('resellerId', $reseller['resellerId'])->where('server_status', 1);
                                        })
                                            ->where('paid_amount', '=', 0)
                                            ->sum('buy_price');
                                        $resellerRechagable = $totalBuyPrice - $reseller['balance'];
                                        if ($resellerRechagable > 0) {
                                            $totalRechargeableAmounts += $resellerRechagable;
                                        }
                                        ?>
                                    <td>{{ $resellerRechagable > 0 ? number_format($resellerRechagable, 2) : 0 }}</td>
                                    {{-- @endrole --}}
                                @endif
                                {{-- <td>{{ $reseller['due'] }}</td> --}}

                                <td>{{ $totalActiveClients }}</td>
                                <td>{{$totalInActiveClients}}</td>

                                @if ($setting['invoice_system'] == 'fixed')
                                    <td>{{ $reseller['c_exp_date'] }}</td>
                                @endif
                                <td class="hidden-print">
                                    @can('reseller_add')
                                        <a href="{{ route('reseller.edit', $reseller->resellerId) }}" class="btn-edit"><i
                                                    class="fa fa-edit"></i></a>
                                    @endcan
                                    @can('reseller_profile')
                                        <a href="{{ route('reseller.view', $reseller->resellerId) }}" target="_blank"
                                           class="btn-show"><i class="fa fa-eye"></i></a>
                                    @endcan
                                    @can('reseller_recharge balance')
                                        <a href="{{ route('reseller.payment', $reseller->resellerId) }}" class="btn-show"
                                           title="Recharge Balance"><i class="fa fa-product-hunt"></i></a>
                                    @endcan
                                    {{-- <a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a> --}}
                                </td>
                                <style>
                                    .btn-fixed-width {
                                        width: 100%;
                                        border-radius: 5px;
                                    }
                                </style>
                                <td>
                                    <form action="{{ route('reseller.deactive', $reseller->resellerId) }}" method="POST" class="centered-form">
                                        @csrf
                                        @method('PUT')
                                        <label class="switch">
                                            <input
                                                    type="checkbox"
                                                    name="is_payment"
                                                    value="0"
                                                    {{ $reseller->is_payment == 0 ? 'checked' : '' }}
                                                    onchange="this.form.submit()"
                                            >
                                            <span class="slider">
        <span class="text-on">ON</span>
        <span class="text-off">OFF</span>
      </span>
                                        </label>
                                    </form>
                                </td>

                                <td>
                                    <form action="{{ route('reseller.extra_charge', $reseller->resellerId) }}" method="POST" class="centered-form">
                                        @csrf
                                        @method('PUT')
                                        <label class="switch">
                                            <input
                                                    type="checkbox"
                                                    name="extra_charge"
                                                    value="1"
                                                    {{ $reseller->extra_charge == 1 ? 'checked' : '' }}
                                                    onchange="this.form.submit()"
                                            >
                                            <span class="slider">
        <span class="text-on">ON</span>
        <span class="text-off">OFF</span>
      </span>
                                        </label>
                                    </form>
                                </td>

                                <td>
                                    <form action="{{ route('reseller.plan_price', $reseller->resellerId) }}" method="POST" class="centered-form">
                                        @csrf
                                        @method('PUT')
                                        <label class="switch">
                                            <input
                                                    type="checkbox"
                                                    name="plan_price"
                                                    value="1"
                                                    {{ $reseller->plan_price == 1 ? 'checked' : '' }}
                                                    onchange="this.form.submit()"
                                            >
                                            <span class="slider">
        <span class="text-on">ON</span>
        <span class="text-off">OFF</span>
      </span>
                                        </label>
                                    </form>
                                </td>


                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total:
                                    <?php
                                    $totalBalance = 0;
                                    foreach ($data as $reseller) {
                                        $totalBalance += $reseller['balance'];
                                    }
                                    echo $totalBalance;
                                    ?>
                            </td>
                            <td>Total:
                                {{ number_format($totalRechargeableAmounts, 2) }}
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection
@section('custom_js')
    @include('admin.layouts.print-js')
@endsection
@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
@endsection
@section('custom_css')
    <style>
        .centered-form {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* Smaller switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 45px;   /* Reduced width */
            height: 20px;  /* Slim height */
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ff4d4d;
            border-radius: 20px;
            transition: background 0.3s;
            overflow: hidden;
        }

        .slider:before {
            content: "";
            position: absolute;
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: transform 0.3s;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            z-index: 2;
        }

        .slider .text-on,
        .slider .text-off {
            position: absolute;
            width: 50%;
            height: 100%;
            line-height: 20px;
            font-size: 8px;
            font-weight: bold;
            color: white;
            text-align: center;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .slider .text-on {
            left: 0;
            opacity: 0;
        }

        .slider .text-off {
            right: 0;
            opacity: 1;
        }

        input:checked + .slider {
            background: #4caf50;
        }

        input:checked + .slider:before {
            transform: translateX(25px);  /* ~half the width minus thumb */
        }

        input:checked + .slider .text-on {
            opacity: 1;
        }

        input:checked + .slider .text-off {
            opacity: 0;
        }

        td form.centered-form {
            padding: 2px 0;
        }

        .dataTable>thead>tr>th[class*=sort]:after,
        .dataTable>thead>tr>th[class*=sort]:before {
            display: none;
        }
    </style>


@endsection

@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            // Calculate the total balance and total rechargeable amount
            var totalBalance = 0;
            var totalRechargeable = 0;
            @foreach ($data as $reseller)
                totalBalance += {{ $reseller['balance'] }};
            @if (Auth::user()->hasAnyRole(['Super-Admin', 'Admin']))
            // @role('Super-Admin')
            var resellerRechargeable = {{ $resellerRechagable }};
            totalRechargeable += resellerRechargeable > 0 ? resellerRechargeable : 0;
            // @endrole
            @endif
            @endforeach
            // Display the calculated totals in the new row
            $('#totalBalance').text(totalBalance);
            $('#totalRechargeable').text(totalRechargeable);
        });
    </script>
@endsection
