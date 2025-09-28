<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
    Invoice list
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                {{-- <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div> --}}

                <div class="alert alert-success alert-dismissible" style="margin-top: 10px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card-box table-responsive">
                {{--                <h4 class="m-t-0 header-title">{{ $page_title }}</h4> --}}
                <div class="row">
                    <div class="col-md-5">
                        <div class="text-center text-md-left m-b-10">
                            <div class="d-flex align-items-center justify-content-start">
                                @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('invoice_add'))
                                    <a href="{{ route('invoice.add') }}" class="btn btn-primary">Create New Invoice</a>
                                @endif

                                @if ($user->hasAnyRole('Super-Admin') && Route::currentRouteName() == 'invoice.trash')
                                    <button type="button" id="delete-button-wrapper" class="btn btn-danger btn-block ml-2"
                                        style="display: inline-block; width:fit-content">
                                        <i class="fa fa-print"></i> Delete Invoice
                                    </button>
                                @endif

                                @if ($user->hasAnyRole('Super-Admin', 'Reseller') && Route::currentRouteName() == 'invoice.due')
                                    <button type="button" id="trash-button-wrapper" class="btn btn-danger btn-block ml-2"
                                        style="display: inline-block; width:fit-content">
                                        <i class="fa fa-print"></i> Trash All
                                    </button>
                                @endif

                                {{-- @if ($user->hasAnyRole('Super-Admin') && Route::currentRouteName() == 'invoice.due')
                                <form method="GET" action="{{ route('invoice.due') }}">
                                    @php
                                        $allResellers = DB::table('resellers')
                                            ->select('resellerId', 'resellerName')
                                            ->get();
                                    @endphp
                                    <select class="form-control ml-2" name="reseller_name" id="invoiceByResellers" onchange="this.form.submit()">
                                        <option value="All Invoices">All Invoices</option>
                                        @foreach ($allResellers as $reseller)
                                            <option value="{{ $reseller->resellerId }}" {{ request('reseller_name') == $reseller->resellerId ? 'selected' : '' }}>
                                                {{ $reseller->resellerName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>                                                               
                                @endif --}}

                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('invoice_bulk print'))
                            <button type="button" id="print-button-wrapper" class="btn btn-outline-warning btn-block"
                                onclick="document.getElementById('print-invoice').submit()" style="display: none;">
                                <i class="fa fa-print"></i> Print Invoice
                            </button>
                        @endif
                    </div>
                    <div class="col-md-5">
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
                <form action="{{ route('invoice.bulk.print') }}" method="post" id="print-invoice" target="_blank">
                    @csrf
                    {{-- <div class="row"> --}}
                    {{-- <div class="col-12"> --}}
                    {{-- <div id="payment-button-wrapper" style="display: none;" class="row mb-2"> --}}
                    {{-- <div class="col-3 offset-6"> --}}
                    {{-- <select name="paid_to" required class="form-control"> --}}
                    <?php
                    //                                    if($role_id == 3){
                    //                                        $accounts = \App\Account::where('branchId', Auth::user()->branchId)->orderBY('id', 'ASC')->get();
                    //                                    } else {
                    //                                        $accounts = \App\Account::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();
                    //                                    }
                    ?>
                    {{-- <option value="">Select Account Name</option> --}}
                    {{-- @foreach ($accounts as $account) --}}
                    {{-- <option value="{{$account['id']}}">{{$account['account_name']}}</option> --}}
                    {{-- @endforeach --}}
                    {{-- </select> --}}
                    {{-- </div> --}}
                    {{-- <div class="col-3"> --}}
                    {{-- <button type="submit" class="btn btn-outline-warning btn-block" onclick="return confirm('Do you want to collect payment of selected invoices?');"> --}}
                    {{-- <i class="fa fa-money"></i> Collect Payment</button> --}}
                    {{-- </div> --}}
                    {{-- </div> --}}
                    {{-- </div> --}}
                    {{-- </div> --}}

                    <table id="datatable"
                        class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr class="text-center">
                                <th><input type="checkbox" id="checkedAll" value="all"></th>
                                <th>#Inv No</th>
                                <th>Create Date</th>
                                <th>Client Name</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th>Area</th>
                                <th>Bill Month</th>
                                <th>Bandwidth</th>
                                <th>Total Price</th>
                                <th>Discount</th>
                                {{-- <th>Sub Price</th> --}}
                                <th>Vat</th>
                                <th>Grand Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                {{-- <th>Branch Name</th> --}}
                                <th class="hidden-print" style="width:70px">Manage</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($invoiceData as $invoice)
                                <tr class="{{ $invoice->due > 0 ? ' ' : 'text-success' }}">

                                <td>
                                        @if ($invoice->due > 0)
                                            <input name="invoiceNo[]" class="checkSingle" type="checkbox"
                                                value="{{ $invoice->id }}">
                                        @endif
                                    </td>
                                    <td>{{ $invoice->id }}</td>
                                    <td>{{ $invoice->created_at->format('d-M-y') }}</td>
                                    <td data-toggle="tooltip" data-placement="right"
                                        title="{{ 'Area/Box: ' . $invoice->client->distribution->distribution }}">
                                        <a href="{{ route('client.view', $invoice->client->id) }}" target="_blank">
                                            {{ $invoice->client->client_name }} ({{ $invoice->client->username }})
                                        </a>
                                    </td>
                                    <th>{{ $invoice->client->phone }}</th>
                                    <th>{{ $invoice->client->address }}</th>
                                    <td>{{ $invoice->client->distribution->distribution }}</td>
                                    <td>{{ date('M', mktime(0, 0, 0, $invoice->bill_month, 1)) }} -
                                        {{ $invoice->bill_year }}</td>
                                    <td>{{ $invoice->bandwidth }}</td>
                                    <td>{{ $invoice->total }}</td>
                                    <td>{{ number_format($invoice->discount, 2) }}</td>
                                    {{--                            <td>{{ $invoice->all_total }}</td> --}}
                                    <td>{{ $invoice->vat }}</td>
                                    <td title="{{ Auth::user()->resellerId != '' ? 'Buy Price: ' . $invoice->buy_price : '' }}"
                                        data-toggle="tooltip" data-placement="left">
                                        {{ number_format($invoice->sub_total, 2) }}
                                    </td>
                                    <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td>{{ number_format($invoice->due, 2) }}</td>
                                    {{-- @php($clientData = \App\Client::with('branch')->find($invoice->client->id)->branchName) --}}
                                    {{-- @php((empty($clientData->branchId)) ? $branch_name = '-' : $branch_name = $clientData->branch->branchName) --}}
                                    {{-- <td>{{ $branch_name }}</td> --}}
                                    <td class="hidden-print" style="min-width: 110px;">

                                        @if (\Request::route()->getName() == 'invoice.trash')
                                            <a href="{{ route('invoice.restore', $invoice->id) }}"
                                                onclick="return confirm('Restore Invoice?')" class="btn-view"><i
                                                    class="fa fa-undo"></i></a>
                                        @else
                                            @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('invoice_view'))
                                                <a href="{{ route('invoice.show', $invoice->id) }}" class="btn-show"><i
                                                        class="fa fa-eye"></i></a>
                                            @endif
                                            @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('invoice_view'))
                                                <a href="{{ route('invoice.showAll', $invoice->client_id) }}" class="btn-show">
                                                    <i class="fa fa-copy"></i>
                                            @endif
                                            @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('invoice_edit'))
                                                <a href="{{ route('invoice.edit', $invoice->id) }}" class="btn-edit"><i
                                                        class="fa fa-edit"></i></a>
                                            @endif

                                            @if ($invoice->client->phone != '')
                                                <?php
                                                $mobile_no = $invoice->client->phone;
                                                
                                                $lenght = strlen((string) $mobile_no);
                                                if ($lenght > 11) {
                                                    $to = substr_replace($mobile_no, '880', 0, 3) . '';
                                                } elseif ($lenght == 11) {
                                                    $to = substr_replace($mobile_no, '880', 0, 1) . '';
                                                } else {
                                                    $to = substr_replace($mobile_no, '880', 0, 0) . '';
                                                }
                                                $message = urlencode(URL::signedRoute('invoice.share', $invoice->id));
                                                ?>
                                                <a href="{{ 'https://api.whatsapp.com/send/?phone=' . $to . '&text=' . $message }}"
                                                    title="Share invoice via Whatsapp"
                                                    class="text-success waves-effect waves-light" style="font-size: 18px;"
                                                    target="_blank">
                                                    <i class="fa fa-whatsapp"></i>
                                                </a>
                                            @endif

                                            @if ($invoice->paid_amount <= 0 && ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('invoice_delete')))
                                                <a href="{{ route('invoice.del', $invoice->id) }}"
                                                    onclick="return myFunction()" class="btn-del">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                                {{-- <form id="delete" action="{{ route('invoice.del', $invoice->id) }}" method="POST" style="display: none;"> --}}
                                                {{-- {{ csrf_field() }} --}}
                                                {{-- </form> --}}
                                            @endif
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
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right">Total:</td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
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
    {{-- @include('admin.layouts.print-js') --}}
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
            "columnDefs": [{
                "orderable": false,
                "targets": 0
            }],
            buttons: ['pageLength', 'excel',
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },
                    footer: true

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

                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    },
                    footer: true

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

                var col = [10, 11, 12];
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
                        '' + pageTotal.toFixed(2) + '<br>(Total: ' + Total.toFixed(2) + ')' + ''
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

        function myFunction() {
            if (confirm("After delete you will not be able to create this invoice again.\nAre you sure?")) {
                return true;
                // document.getElementById('delete').submit();
            }
            return false;
        }
    </script>
    <script>
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

    <script>
        $(document).ready(function() {
            $("#delete-button-wrapper").click(function() {
                var selectedInvoiceIds = [];

                $(".checkSingle:checked").each(function() {
                    selectedInvoiceIds.push($(this).val());
                });

                if (selectedInvoiceIds.length > 0) {
                    // Show confirmation dialog
                    var confirmed = confirm(
                        "Are you sure you want to permanently delete the selected invoices? This action cannot be undone."
                    );

                    if (confirmed) {
                        // Send Axios DELETE request
                        axios.delete("{{ route('invoice.delete.bulk') }}", {
                                data: {
                                    _token: "{{ csrf_token() }}", // Include CSRF token
                                    invoice_ids: selectedInvoiceIds
                                }
                            })
                            .then(function(response) {
                                location
                            .reload(); // Reload the page to reflect changes and show the flash message
                            })
                            .catch(function(error) {
                                console.log("An error occurred: " + error.response.status + " " + error
                                    .response.statusText);
                            });
                    } else {
                        // Action was canceled
                        console.log("Deletion canceled.");
                    }
                } else {
                    alert("Please select at least one invoice to delete.");
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#trash-button-wrapper").click(function() {
                var selectedInvoiceIds = [];

                $(".checkSingle:checked").each(function() {
                    selectedInvoiceIds.push($(this).val());
                });

                if (selectedInvoiceIds.length > 0) {
                    // Show confirmation dialog
                    var confirmed = confirm(
                        "Are you sure you want to move to Trash ?"
                    );

                    if (confirmed) {
                        // Send Axios DELETE request
                        axios.delete("{{ route('invoice.trash.bulk') }}", {
                                data: {
                                    _token: "{{ csrf_token() }}", // Include CSRF token
                                    invoice_ids: selectedInvoiceIds
                                }
                            })
                            .then(function(response) {
                                location
                            .reload(); // Reload the page to reflect changes and show the flash message
                            })
                            .catch(function(error) {
                                console.log("An error occurred: " + error.response.status + " " + error
                                    .response.statusText);
                            });
                    } else {
                        // Action was canceled
                        console.log("Deletion canceled.");
                    }
                } else {
                    alert("Please select at least one invoice to move trash.");
                }
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endsection
