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
                        <a href="{{ route('loan.pay.add') }}" class="btn btn-primary">Pay Loan</a>
                    </div>
                </div>


                <table id="datatable"
                       class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg text-center"
                       cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Loan Payer</th>
                        <th>Entry By</th>
                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @php($total_amount = 0)
                    @foreach ($loans as $loan)
                        @php($total_amount += $loan->pay_amount )
                        <tr>

                            <td></td>
                            <td>{{ date("d-M-Y", strtotime($loan->date)) }}</td>
                            <td class="text-right">{{ $loan->pay_amount }}</td>
                            <td>{{ $loan->loanPayer->name }}</td>
                            <td>{{ $loan->user->name }}</td>
                            <td class="hidden-print">

                                <a href="{{ route('loan.edit', $loan->id) }}" class="btn-edit"><i
                                            class="fa fa-edit"></i></a>
                                @if($role_id != 5)
                                    {{--<a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a>--}}
                                @endif

                            </td>
                        </tr>

                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <td></td>
                        <td class="text-right">Total:</td>
                        <td class="text-right">{{ number_format($total_amount, 2) }}</td>
                        <td></td>
                        <td></td>
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