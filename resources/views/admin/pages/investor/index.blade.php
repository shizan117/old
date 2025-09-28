@extends ('admin.layouts.master')
@section('title')
Investors List
@endsection


@section('content')
        <div class="row">
            <div class="col-8">
                <div class="card-box table-responsive">
                    <a href="{{ route('investor.add') }}" class="btn btn-primary">Add Investor</a>
                    <table id="datatable" class="table table-sm table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Investor Name</th>
                            <th>Investor Balance</th>
                            <th class="hidden-print">Manage</th>
                        </tr>
                        </thead>


                        <tbody>
                        @foreach ($investors as $investor)
                        <tr>
                            <td></td>
                            <td>{{ $investor->name }}</td>
                            <td>{{ $investor->amount }}</td>
                            <td class="hidden-print">
                                <a href="{{ route('investor.edit', $investor->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
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