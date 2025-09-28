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
                                <th><input class="d-none" type="checkbox" id="checkedAll" value="all"></th>
                                <th>Date</th>
                                <th>Client Name</th>
                                <th>Bill Month</th>
                                <th>Bandwidth</th>
                                <th>Total</th>
                                <th>OTC Fee</th>
                                <th>Vat</th>
                                <th>Grand Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Received By</th>
                                <th class="hidden-print">Manage</th>
                            </tr>
                        </thead>

                        <tbody>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tbody>

                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total:</td>
                                <td ></td>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

    <script>
        $(document).ready(function() {
            // Get today's date and the first day of the current month
            var today = moment().format('YYYY-MM-DD');
            var firstDayOfMonth = moment().startOf('month').format('YYYY-MM-DD');

            // Get the query parameters from the URL, or set default values
            var urlParams = new URLSearchParams(window.location.search);
            var fromDate = urlParams.get('from_date') || firstDayOfMonth;
            var toDate = urlParams.get('to_date') || today;

            $('#datatable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route('receipt.all_data.index') }}',
                    data: function(d) {
                        d.from_date = fromDate;
                        d.to_date = toDate;
                        d.length = d.length || 20; // Ensure length is defined
                        d.draw = d.draw || 1; // Ensure draw is defined

                    },

                    //eta paid advance skip kore
                    dataSrc: function(json) {
                        // Filter out rows where paid_from_advance > 0
                        return json.data.filter(function(row) {
                            return !(row.paid_from_advance > 0);
                        });
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id',
                        render: function(data) {
                            return '<input name="receiptId[]" class="checkSingle" type="checkbox" value="' +
                                data + '">';
                        }
                    },
                    {
                        data: 'payment_date',
                        name: 'payment_date',
                        render: function(data) {
                            return moment(data).format('DD-MMM-YY');
                        }
                    },
                    {
                        data: 'client.client_name',
                        name: 'client.client_name',
                        render: function(data, type, row) {
                            if (row.client && row.client.id) {
                                return '<a href="clients/client-view/' + row.client.id +
                                    '" target="_blank">' +
                                    data + ' (' + row.client.username + ')</a>';
                            } else {
                                return '<a href="#" onclick="return false;">(Deleted client)</a>';
                            }
                        }

                    },
                    {
                        data: 'bill_month',
                        name: 'bill_month',
                        render: function(data, type, row) {
                            return data ? moment().month(data - 1).format('MMM') + ' - ' + row
                                .bill_year : '';
                        }
                    },
                    {
                        data: 'bandwidth',
                        name: 'bandwidth'
                    },
                    {
                        data: 'all_total',
                        name: 'all_total'
                    },
                    {
                        data: 'otc_charge',
                        name: 'otc_charge'
                    },
                    {
                        data: 'vat',
                        name: 'vat'
                    },
                    {
                        data: 'sub_total',
                        name: 'sub_total'
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount'
                    },
                    {
                        data: 'due',
                        name: 'due'
                    },
                    {
                        data: 'received_by',
                        name: 'received_by',
                        render: function(data, type, row) {
                            console.log(row)
                            let transaction = row.transaction;
                            let checkPaidFromAdvance = row.paid_from_advance;

                            let user_name = 'Bkash Checkout';
                            if (transaction) {
                                if (transaction.pay_id != null && transaction.bkash_trxID != null) {
                                    user_name = 'Bkash Checkout';
                                } else if (transaction.pay_id == null && transaction.bkash_trxID !=
                                    null) {
                                    user_name = 'Bkash Pay Bill';
                                } else if (transaction.user?.name) {
                                    user_name = transaction.user.name;
                                } else {
                                    $.ajax({
                                        url: '/get-user-name/' + transaction.user_id,
                                        method: 'GET',
                                        async: false,
                                        success: function(response) {
                                            user_name = response.name ||
                                                'User Not Found';
                                        }
                                    });
                                }
                            } else if(checkPaidFromAdvance > 0){
                                user_name = "Paid Advance"
                            }
                            return user_name;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let viewButton = '<a href="receipt/view/' + row.id +
                                '" class="btn-show"><i class="fa fa-eye"></i></a>';
                            let printButton = '<a href="receipt/print/' + row.id +
                                '" class="btn-show" target="_blank"><i class="fa fa-print"></i></a>';

                            // Check if row.client exists and row.client.id is not null
                            if (row.client && row.client.id) {
                                // Show the buttons with active links if client.id exists
                                return viewButton + ' ' + printButton;
                            } else {
                                // Show the buttons without active links if client.id is null
                                return '<a href="#" class="btn-show" onclick="return false;"><i class="fa fa-eye"></i></a>' +
                                    ' <a href="#" class="btn-show" onclick="return false;"><i class="fa fa-print"></i></a>';
                            }
                        }


                    },
                ],
                dom: 'Bfrtip',
                buttons: [
                    'pageLength',
                    'excel', 'pdf',
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
                                .removeClass(
                                    'table-striped table-responsive-sm table-responsive-lg')
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
                                .removeClass(
                                    'table-striped table-responsive-sm table-responsive-lg')
                                .addClass('compact')
                                .css('font-size', 'inherit', 'color', '#000');
                        }
                    }
                ],
                pageLength: 20,
                lengthMenu: [
                    [20, 50, 100, 200, 500, 1000, 2000, 5000],
                    [20, 50, 100, 200, 500, 1000, 2000, 5000]
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Helper function to sum the data in a column
                    function sumColumn(index) {
                        return api.column(index, {
                            page: 'current'
                        }).data().reduce(function(a, b) {
                            return parseFloat(a) + parseFloat(b);
                        }, 0);
                    }

                    // Calculate sums for the page
                    var total = sumColumn(5);
                    var vat = sumColumn(6);
                    var grandTotal = sumColumn(7);
                    var paid = sumColumn(8);
                    var due = sumColumn(9);

                    // Update footer with totals for the current page
                    $(api.column(5).footer()).html(total.toFixed(2));
                    $(api.column(6).footer()).html(vat.toFixed(2));
                    $(api.column(7).footer()).html(grandTotal.toFixed(2));
                    $(api.column(8).footer()).html(paid.toFixed(2));
                    $(api.column(9).footer()).html(due.toFixed(2));

                    // Calculate totals for all pages
                    var totalAll = api.column(5).data().reduce(function(a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    var vatAll = api.column(6).data().reduce(function(a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    var grandTotalAll = api.column(7).data().reduce(function(a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    var paidAll = api.column(8).data().reduce(function(a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);
                    var dueAll = api.column(9).data().reduce(function(a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                    // Update footer with totals for all pages
                    $('.total-all').html(totalAll.toFixed(2));
                    $('.vat-all').html(vatAll.toFixed(2));
                    $('.grand-total-all').html(grandTotalAll.toFixed(2));
                    $('.paid-all').html(paidAll.toFixed(2));
                    $('.due-all').html(dueAll.toFixed(2));
                }
            });
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
