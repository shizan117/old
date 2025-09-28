@extends('layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Bill Month</th>
                        {{--<th>Bandwidth</th>--}}
                        <th>Total Price</th>
                        <th>Discount</th>
                        <th>Sub Price</th>
                        <th>Vat</th>
                        <th>Grand Total</th>
                        <th>Paid</th>
                        <th>Due (With Vat)</th>

                        <!--<th class="hidden-print">Manage</th>-->
                    </tr>
                    </thead>


                    <tbody>
                    @php($i = 0)
                    @foreach ($invoices as $invoice)
                        @php($i = $i+1)
                        <tr class="{{ $invoice->due>0?'text-danger':'text-success' }}">
                            <td>{{ $i }}</td>
                            <td>{{ $invoice->client->client_name }}</td>
                            <td>{{ date('M', mktime(0, 0, 0, $invoice->bill_month, 1)) }} - {{ $invoice->bill_year }}</td>
                            {{--<td>{{ $invoice->bandwidth }}</td>--}}
                            <td>{{ $invoice->total }}</td>
                            <td>{{ $invoice->discount }}</td>
                            <td>{{ $invoice->all_total }}</td>
                            <td>{{ $invoice->vat }}</td>
                            <td>{{ $invoice->sub_total }}</td>
                            <td>{{ $invoice->paid_amount }}</td>
                            <td>{{ $invoice->due }}</td>
                            <!--<td class="hidden-print">-->
                            <!--    @if($invoice->due>0)-->
                            <!--        <a href="{{ route('client.pay', $invoice->id) }}" class="btn-view"><i class="fa fa-product-hunt"></i></a>-->
                            <!--    @endif-->
                            <!--</td>-->
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
    <!-- DataTable Css -->
    <link href='{{ asset("assets/plugins/datatables/dataTables.bootstrap4.min.css") }}' rel="stylesheet" type="text/css" />
    <link href='{{ asset("assets/plugins/datatables/buttons.bootstrap4.min.css") }}' rel="stylesheet" type="text/css" />
@endsection
@section('custom_css')
    <style>
        .dataTable > thead > tr > th[class*=sort]:after{
            display:none;
        }
        .dataTable > thead > tr > th[class*=sort]:before{
            display:none;
        }
    </style>
@endsection


@section('required_js')
    <!-- Required datatable js -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap4.min.js') }}" type="text/javascript"></script>

    <!-- Buttons examples -->
    <script src="{{ asset('assets/plugins/datatables/dataTables.buttons.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/datatables/buttons.bootstrap4.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/datatables/jszip.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/datatables/pdfmake.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/datatables/vfs_fonts.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/datatables/buttons.html5.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/datatables/buttons.print.min.js') }}" type="text/javascript"></script>
@endsection