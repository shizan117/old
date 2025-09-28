@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                <div class="btn-group m-b-10">
                    <div class="btn-group m-b-10">
                        @if($role_id == 1 OR $role_id == 2 OR $role_id == 5)
                            <a href="{{ route('invoice.seller.add') }}" class="btn btn-primary">Create New Invoice</a>
                        @elseif($role_id == 4)
                            <a href="{{ route('invoice.seller') }}" class="btn btn-custom">Seller's Invoice List</a>
                            <a href="{{ route('invoice.index') }}" class="btn btn-success">Client's Invoice List</a>
                            <a href="{{ route('invoice.add') }}" class="btn btn-primary">Create New Invoice</a>
                        @endif


                    </div>
                </div>


                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Reseller Name</th>
                        <th>Bill Month</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Vat</th>
                        <th>Discount</th>
                        <th>Sub Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Invoice Date</th>
                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @foreach ($invoiceData as $invoice)
                        <tr class="{{ $invoice->due<=0?'text-success':'' }}">
                            <td></td>
                            <td>{{ $invoice->reseller->resellerName }}</td>
                            <td>{{ date('M', mktime(0, 0, 0, $invoice->bill_month, 1)) }} - {{ $invoice->bill_year }}</td>
                            <td>{{ $invoice->buy_price }}</td>
                            <td>{{ $invoice->total }}</td>
                            <td>{{ $invoice->vat }}</td>
                            <td>{{ $invoice->discount }}</td>
                            <td>{{ $invoice->sub_total }}</td>
                            <td>{{ $invoice->paid_amount }}</td>
                            <td>{{ $invoice->due }}</td>
                            <td>{{ $invoice->created_at->format('d M Y') }}</td>
                            <td class="hidden-print">
                                @if($role_id != 4)
                                    <a href="{{ route('invoice.seller.edit', $invoice->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                    @if($invoice->due>0)
                                    <a href="{{ route('reseller.invoice.pay', $invoice->id) }}" class="btn-view"><i class="fa fa-product-hunt"></i></a>
                                    @endif
                                @else
                                    @if($invoice->due>0)
{{--                                    <a href="{{ route('reseller.pay', $invoice->id) }}" class="btn-view"><i class="fa fa-product-hunt"></i></a>--}}
                                    @endif
                                @endif
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