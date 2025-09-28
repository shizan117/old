@extends ('layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Bill Month</th>
                        {{--<th>Bandwidth</th>--}}
                        <th>Total</th>
                        <th>Vat</th>
                        <th>Grand Total</th>
                        <th>Paid</th>
                        <th>Due (With Vat)</th>
                        <th>Received By</th>
                        <th>TrxID</th>

                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td></td>
                                <td>{{ $payment->client->client_name }}</td>
                                <td>{{ ($payment->bill_month == null) ? '' : date('F', mktime(0, 0, 0, $payment->bill_month, 1)) }} - {{ $payment->bill_year }}</td>
                                <td>{{ $payment->all_total }}</td>
                                <td>{{ $payment->vat }}</td>
                                <td>{{ $payment->sub_total }}</td>
                                <td>{{ $payment->paid_amount }}</td>
                                <td>{{ $payment->due }}</td>
                                @php
                                    $transaction = $payment->transaction; // Access the transaction relation
                                    $user_name = '';
                        
                                    if ($transaction) {
                                        if ($transaction->pay_id != null && $transaction->bkash_trxID != null) {
                                            $user_name = 'Bkash Checkout';
                                        } elseif ($transaction->pay_id == null && $transaction->bkash_trxID != null) {
                                            $user_name = 'Bkash Pay Bill';
                                        } else {
                                            $user_name = $transaction->user->name ?? 'Unknown';
                                        }
                                    } else {
                                        $user_name = 'No Transaction';
                                    }
                                @endphp
                                <td>{{ $user_name }}</td>
                                <td>{{ $transaction->trxId ?? 'N/A' }}</td>
                                <td class="hidden-print">
                                    <a href="{{ route('client.receipt.show', $payment->id) }}" class="btn-show"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        
                </table>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
    @include('admin.layouts.print-js')
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