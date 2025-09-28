@extends ('admin.layouts.master')
@section('title')
Loan Payers List
@endsection


@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">
                    <div class="btn-group m-b-10">
                        <div class="btn-group m-b-10">
                            <a href="{{ route('loan.payer.add') }}" class="btn btn-primary">Add Loan Payer</a>
                        </div>
                    </div>

                    <table id="datatable" class="table table-sm table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Loan Payer Name</th>
                            <th>Loan Amount</th>
                            <th>Payable Amount</th>
                            <th>Remain Amount</th>
                            <th class="hidden-print">Manage</th>
                        </tr>
                        </thead>


                        <tbody>
                        @foreach ($loanPayers as $loanPayer)
                        <tr>
                            <td></td>
                            <td>{{ $loanPayer->name }}</td>
                            <td>{{ $loanPayer->loan_amount }}</td>
                            <td>{{ $loanPayer->pay_amount }}</td>
                            <td>{{ $loanPayer->remain }}</td>
                            <td class="hidden-print">
                                <a href="{{ route('loan.payer.edit', $loanPayer->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                {{--<a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a>--}}

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