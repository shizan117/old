<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
    Receipt List
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <div class="row">
                    <div class="col-md-4">
                        <h4 class="m-t-0 pb-2 pb-md-0 header-title">{{ $page_title }}</h4>
                    </div>
                    <div class="col-md-2">
                        @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('receipt_bulk print'))
                            <button type="button" id="print-button-wrapper" class="btn btn-outline-warning btn-block"
                                onclick="document.getElementById('print-receipt').submit()" style="display: none;">
                                <i class="fa fa-print"></i> Print Receipt
                            </button>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <form class="form-horizontal" action="" role="form" method="get">
                            <div class="form-group row">
                                <div class="col-5">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="from_date"
                                            value="{{ \request('from_date') ?? '' }}" class="form-control datepicker"
                                            placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="to_date"
                                            value="{{ \request('to_date') ?? '' }}" class="form-control datepicker"
                                            placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <form action="{{ route('receipt.bulk.print') }}" method="get" id="print-receipt" target="_blank">
                    @csrf
                    <table id="datatable"
                        class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkedAll" value="all"></th>
                                <th>Date</th>
                                <th>Client Name</th>
                                <th>Bill Month</th>
                                <th>Bandwidth</th>
                                <th>Total</th>
                                <th>Vat</th>
                                <th>Grand Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Received By</th>
                                {{-- @if ($role_id == 1 or $role_id == 2 or $role_id == 5) --}}
                                {{-- <th>Branch Name</th> --}}
                                {{-- @endif --}}

                                <th class="hidden-print">Manage</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>
                                        <input name="receiptId[]" class="checkSingle" type="checkbox"
                                            value="{{ $payment->id }}">
                                    </td>
                                    <td>{{ date('d-M-y', strtotime($payment->payment_date)) }}</td>
                                    <td><a href="{{ route('client.view', $payment->client->id) }}"
                                            target="_blank">{{ $payment->client->client_name }}</a>
                                        ({{ $payment->client->username }})
                                    </td>
                                    <td>{{ $payment->bill_month == null ? '' : date('M', mktime(0, 0, 0, $payment->bill_month, 1)) }}
                                        - {{ $payment->bill_year }}</td>
                                    <td>{{ $payment->bandwidth }}</td>
                                    <td>{{ $payment->all_total }}</td>
                                    <td>{{ $payment->vat }}</td>
                                    <td>{{ $payment->sub_total }}</td>
                                    <td>{{ $payment->paid_amount }}</td>
                                    <td>{{ $payment->due }}</td>


                                    {{-- @php((empty($payment->user_id)) ? $received = 'Online Paid' : $received = $payment->user->name) --}}
                                    {{-- <td>{{ $received }}</td> --}}

                                    <td>
                                        <?php
                                        $transaction = $payment->transaction;
                                        $user_name = '';
                                        if ($transaction) {
                                            if ($transaction->pay_id != null && $transaction->bkash_trxID != null) {
                                                $user_name = 'Bkash Checkout';
                                            } elseif ($transaction->pay_id == null && $transaction->bkash_trxID != null) {
                                                $user_name = 'Bkash Pay Bill';
                                            } else {
                                                $user_name = $transaction->user->name;
                                            }
                                        } else {
                                            // Handle the case when transaction is null
                                            $user_name = 'Bkash Checkout';
                                        }
                                        ?>
                                        {{ $user_name }}
                                    </td>


                                    <td class="hidden-print">
                                        @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('receipt_view'))
                                            <a href="{{ route('receipt.show', $payment->id) }}" class="btn-show"><i
                                                    class="fa fa-eye"></i></a>
                                        @endif
                                        @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('receipt_print'))
                                            <a href="{{ route('receipt.print', $payment->id) }}" class="btn-show"
                                                target="_blank"><i class="fa fa-print"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right">Total:</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
    {{--    @include('admin.layouts.print-js') --}}

    <script>
        //Buttons examples
        $('#datatable').DataTable({
            dom: 'Bfrtip',
            "pageLength": 20,
            "lengthMenu": [
                [20, 50, 100, -1],
                [20, 50, 100, "All"]
            ],
            "aaSorting": [],
            buttons: ['pageLength', 'excel',
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    }

                },

                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },
                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    }
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

                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    }

                }

            ],
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                var col = [7, 8, 9];
                for (var j = 0; j < col.length; j++) {
                    // Total over this page
                    pageTotal = api
                        .column(col[j], {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    Total = api
                        .column(col[j])
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(col[j]).footer()).html(
                        '' + pageTotal.toFixed(2) + '<br>(Total:' + Total.toFixed(2) + ')' + ''
                    );
                }
            },
        });
    </script>
    <script>
        $("#checkedAll").change(function() {
            if (this.checked) {
                $(".checkSingle").each(function() {
                    this.checked = true;
                    $("#print-button-wrapper").show();
                });
            } else {
                $(".checkSingle").each(function() {
                    this.checked = false;
                    $("#print-button-wrapper").hide();
                });
            }
        });



        $("#datatable").on('click', '.checkSingle', function() {
            if ($(this).is(":checked")) {
                var isAllChecked = 0;

                $(".checkSingle").each(function() {
                    if (!this.checked)
                        isAllChecked = 1;
                });

                if (isAllChecked == 0) {
                    $("#checkedAll").prop("checked", true);
                }
                $("#print-button-wrapper").show();
            } else {
                var isAllUnchecked = 0;
                $(".checkSingle").each(function() {
                    if (this.checked)
                        isAllUnchecked = 1;
                });

                if (isAllUnchecked == 0) {
                    $("#print-button-wrapper").hide();
                }
                $("#checkedAll").prop("checked", false);
            }
        });

        $(document).ready(function() {
            $(".datepicker").datepicker({
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });
        });
    </script>
@endsection

@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
    <link href='{{ asset('assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}' rel="stylesheet"
        type="text/css" />
@endsection
@section('custom_css')
    <style>
        .dataTable>thead>tr>th[class*=sort]:after {
            display: none;
        }

        .dataTable>thead>tr>th[class*=sort]:before {
            display: none;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
@endsection
