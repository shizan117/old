@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <div class="row">
                    <div class="col-md-4">
                        <h4 class="m-t-0 header-title" style="color: #ffc107;">{{ $page_title }}</h4>

                    </div>
                    <div class="col-md-8">
                        <form class="form-horizontal" action="" role="form" method="get">
                            <div class="form-group row">
                                <div class="col-md-4 offset-1 pb-3 pb-md-0">
                                    <select name="accountId" id="accountId" class="form-control bg-secondary">
                                        <option value="">Select Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ request('accountId') == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5 col-md-3">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="from_date"
                                            value="{{ \request('from_date') ?? '' }}" class="form-control datepicker"
                                            placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-7 col-md-4">
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


                <table id="datatable"
                    class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg"
                    cellspacing="0" width="100%">
                    <thead>
                        <tr>
                             <th >ID</th>
                            <th>Date & Time</th>
                            <th>Payer</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Vat</th>
                            <th>Total Amount</th>
                            <th>Charge</th>
                            <th>Credit</th>
                            <th>Debit</th>
                            <th>Transaction By</th>
                        </tr>
                    </thead>


                    <tbody>

                        @foreach ($transactions as $transaction)
                            <tr>
{{--                                <td>{{ $loop->iteration }}</td>--}}
                                <td >{{$transaction->id}}</td>
                                <td>{{ date('d-M-y h:i:s A', strtotime($transaction->trans_date)) }}</td>


                                <td>{{ $transaction->payer }}
                                    [ <b>
                                        <a href="{{ $transaction->invoice && $transaction->invoice->client ? route('client.view', $transaction->invoice->client->id) : 'javascript:void(0);' }}"
                                            target="_blank" style="text-decoration: underline; color: #fff;">
                                            {{ $transaction->invoice->client->username ?? 'No Username Found' }}
                                        </a>


                                    </b> ]
                                </td>
                                <td>{{ $transaction->account->account_name ?? "Account Not Found" }}</td>
                                <td>{{ $transaction->tr_type }}</td>
                                <td>{{ $transaction->tr_category }}</td>
                                <td>{{ number_format($transaction->tr_amount - $transaction->tr_vat, 2, '.', '') }}</td>
                                <td>{{ $transaction->tr_vat }}</td>
                                <td>{{ $transaction->tr_amount }}</td>
                                <td>{{ $transaction->charge }}</td>
                                <td>{{ $transaction->cr }}</td>
                                <td>{{ $transaction->dr }}</td>
                                @php
                                    // ($transaction->user_id == null ? ($user_name = 'Online Payment') : ($user_name = $transaction->user->name))
                                    $user_name = '';
                                    if ($transaction->pay_id != null && $transaction->bkash_trxID != null) {
                                        $user_name = 'Bkash Checkout';
                                    } elseif ($transaction->pay_id == null && $transaction->bkash_trxID != null) {
                                        $user_name = 'Bkash Pay Bill';
                                    } else {
                                        $user_name = $transaction->user->name;
                                    }
                                @endphp
                                <td>{{ $user_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="d-none"></td>
                            <td class="d-none"> </td>
                            <td class=""></td>
                            <td></td>
                            <td></td>
                            <td class="text-right">Total:</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td></td>

                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><strong>Opening Balance:</strong></td>
                            <td><strong>{{ number_format($opening_balance, 2) }}</strong></td>
                            <td></td>
                            <td><strong>Closing Balance</strong></td>
                            <td><strong>{{ number_format($closing_balance, 2) }}</strong></td>

                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
    {{-- @include('admin.layouts.print-js') --}}

    <script>
        $(document).ready(function() {
            $(".datepicker").datepicker({
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });



            //Buttons examples
            $('#datatable').DataTable({

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

                    var col = [8, 9, 10];
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
                dom: 'Bfrtip',
                "pageLength": 20,
                "lengthMenu": [
                    [20, 50, 100, -1],
                    [20, 50, 100, "All"]
                ],
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
                                .removeClass(
                                    'table-striped table-responsive-sm table-responsive-lg dataTable'
                                )
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
                                    'table-striped table-responsive-sm table-responsive-lg dataTable'
                                )
                                .addClass('compact')
                                .css('font-size', 'inherit', 'color', '#000');

                        }

                    }

                ]
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
