<?php
$user = Auth::user();
if (Auth::user()->branchId != null) {
    $_branchName = Auth::user()->branch->branchName;
} else {
    $_branchName = '';
}
?>
@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">


        <div class="col-12">
            @php
                $authUser = Auth::user();
            @endphp

            @if (is_null($authUser->resellerId))
                <form method="GET" action="{{ route('report.onlinePay') }}" id="resellerFilterForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="w-50 pb-3 pb-md-0">
                                <select name="resellerId" id="resellerId" class="form-control bg-secondary">
                                    <option value="">All Resellers</option>
                                    @foreach($resellers as $reseller)
                                        <option value="{{ $reseller->resellerId }}" {{ ($selectedResellerId == $reseller->resellerId) ? 'selected' : '' }}>
                                            {{ $reseller->resellerName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

                <script>
                    // Auto-submit form on change
                    document.getElementById('resellerId').addEventListener('change', function () {
                        document.getElementById('resellerFilterForm').submit();
                    });
                </script>
            @endif


            <script>
                // Automatically submit form when reseller is selected
                document.getElementById('resellerId').addEventListener('change', function () {
                    document.getElementById('resellerFilterForm').submit();
                });
            </script>

            <div class="card-box table-responsive">
                <table id="datatable"
                    class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg text-center"
                    cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Date & Time</th>
                            <th>Sender Number</th>
                            <th>Marcent Number</th>
                            <th>Username</th>
                            <th>TrxId</th>
                            <th class="d-none">Currency</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allBkashWebHookTrans as $payment)
                        <tr style="{{ $payment->user_check == 0 ? 'color: #F1948A;' : '' }}">
                                <td>{{ $loop->index + 1 }}</td> <!-- Adjusted index to start from 1 -->
                                <td>{{$payment->type}}</td>
                                <td>
                                    {{ date('d-M-y h:i:s A', strtotime($payment->dateTime)) }}
                             </td>
                                <td>{{ $payment->debitMSISDN ?? "N/A" }}</td>
                                <td>{{ $payment->creditShortCode ?? $bkash_account_info->account_number }}</td>
                                <td>{{ $payment->transactionReference ?? "N/A" }}</td>
                                <td>{{ $payment->trxID }}</td>
                                <td class="d-none">{{ $payment->currency }}</td>
                                <td>{{ $payment->amount }}</td>
                                <td>{{ $payment->transactionStatus }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-right">Total Amount (Per Page)</th>
                            <th colspan="2" class="text-left pl-4" id="amount-total-per-page"></th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-right">Total Amount (Overall)</th>
                            <th colspan="2" class="text-left pl-4" id="amount-total-overall"></th>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
    <link href='{{ asset('assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}' rel="stylesheet"
        type="text/css" />
@endsection

@section('custom_js')
    <script>
$(document).ready(function() {
    var table = $('#datatable').DataTable({
        dom: 'Bfrtip',
        buttons: [
         'pageLength',   'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        lengthMenu: [ [10, 20, 50, -1], [10, 20, 50, "All"] ], // Dropdown options
        pageLength: 20, // Default number of rows to show
        "footerCallback": function (row, data, start, end, display) {
            var api = this.api();
            
            // Calculate total amount per page
            var totalAmountPerPage = api.column(8, { page: 'current' }).data().reduce(function (a, b) {
                return parseFloat(a) + parseFloat(b);
            }, 0);
            
            // Calculate total amount overall
            var totalAmountOverall = api.column(8).data().reduce(function (a, b) {
                return parseFloat(a) + parseFloat(b);
            }, 0);
            
            // Update footer
            $('#amount-total-per-page').html(totalAmountPerPage.toFixed(2));
            $('#amount-total-overall').html(totalAmountOverall.toFixed(2));
        }
    });

    // Filter by status
    $('#status-filter').on('change', function() {
        var status = $(this).val();
        table.column(8).search(status).draw();
    });
});

    </script>
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
