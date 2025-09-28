<?php
    $is_reseller = Auth::user()->resellerId != ''?true:false;
    if (Auth::user()->branchId != null) $_branchName = Auth::user()->branch->branchName;
    else $_branchName = '';
?>
@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <form class="form-row form-horizontal justify-content-center" action="" role="form" method="get">
                    <div class="col-md-3">
                        <h4 class="text-default">{{ (request('from_date') == '')?'This Month Report':'Custom Search Report' }}</h4>
                    </div>
                    <div class="col-md-1">
                        @if(request()->resellerId !='' && $payments->count() > 0)
                            <a href="{{ route('reseller.receipt.print',request()->query()) }}" target="_blank"
                               class="btn btn-outline-warning">Print</a>
                        @endif
                    </div>
                    <div class="col-md-2 pb-3 pb-md-0">
                        @unlessrole('Reseller')
                            <select name="resellerId" id="resellerId" class="form-control bg-secondary">
                                <option value="">All Reseller</option>
                                @foreach($resellers as $reseller)
                                    <option value="{{$reseller->resellerId}}" {{ (request('resellerId')==$reseller->resellerId)?'selected':'' }}>{{$reseller->resellerName}}</option>
                                @endforeach
                            </select>
                        @endunlessrole
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-5">
                                <div class="input-group">
                                    <input type="text" autocomplete="off" name="from_date" value="{{ \request('from_date')??'' }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="ti-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="input-group">
                                    <input type="text" autocomplete="off" name="to_date" value="{{ \request('to_date')??'' }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        <button type="submit"
                                                class="btn btn-info waves-effect waves-light">Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>SL</th>
                        <th>Date</th>
                        @role('Reseller')
                            <th class="text-right">Pre Balance</th>
                            <th class="text-right">Recharge Amount</th>
                            <th class="text-right">After Recharge</th>
                        @else
                            <th>Reseller Name</th>
                            <th class="text-right">Pre Balance</th>
                            <th class="text-right">Recharge Amount</th>
                            <th class="text-right">After Recharge</th>
                            <th>Received By</th>
                        @endrole

                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @php($i=1)
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ date('d-M-y',strtotime($payment->created_at)) }}</td>
                            @role('Reseller')
                                <td class="text-right ">{{ number_format($payment->pre_balance,2) }}</td>
                                <td class="text-right ">{{ number_format($payment->recharge_amount,2) }}</td>
                                <td class="text-right ">{{ number_format($payment->pre_balance+$payment->recharge_amount,2) }}</td>
                            @else
                                <td>  {{ $payment->reseller->resellerName ?? 'Not Found / Deleted User' }} </td>
                                <td class="text-right ">{{ number_format($payment->pre_balance,2) }}</td>
                                <td class="text-right ">{{ number_format($payment->recharge_amount,2) }}</td>
                                <td class="text-right ">{{ number_format($payment->pre_balance+$payment->recharge_amount,2) }}</td>
                             @php($received = empty($payment->user_id) ? 'Online Paid' : ($payment->user?->name ?? 'Online Paid'))
<td>{{ $received }}</td>

                            @endrole

                            <td class="hidden-print">
                                <a href="{{ route('receipt.seller.show', $payment->id) }}" class="btn-show"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        @role('Reseller')
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                        @else
                            <td></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td></td>
                        @endrole
                        <td class="hidden-print"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
    <script>
        $('#datatable').DataTable({
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;

                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };
                // Total over this page
                pageTotal = api
                    .column("{{ $is_reseller?3:4 }}", {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                Total = api
                    .column("{{ $is_reseller?3:4 }}")
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column("{{ $is_reseller?3:4 }}").footer()).html(
                    '' + pageTotal.toFixed(2) + '<br>(Total: '+Total.toFixed(2)+')' +''
                );
            },
            select: {
                style: 'single'
            },
            order: [],

            dom: 'Bfrtip',
            "pageLength": 20,
            "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
            buttons: ['pageLength','excel','pdf',
                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)'],
                        modifier: {
                            page: 'all'
                        },
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

                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: 'Print',
                    autoPrint: true,
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

                    },
                    footer: true,

                }

            ]
        });
    </script>
    <script>
        $(document).ready(function () {
            $(".datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
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
