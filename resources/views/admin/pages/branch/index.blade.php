@extends ('admin.layouts.master')
@section('title')
Branch List
@endsection


@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">

                    <h4 class="m-t-0 header-title">Branch List</h4>
                    <a href="{{ route('branch.add') }}" class="btn btn-primary">Add Branch</a>

                    <table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Branch Name</th>
                            <th>Branch Location</th>
                            <th class="hidden-print">Manage</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($data as $branch)
                        <tr>
                            <td></td>
                            <td>{{ $branch['branchName'] }}</td>
                            <td>{{ $branch['branchLocation'] }}</td>
                            <td class="hidden-print">
                                <a href="{{ route('branch.edit', $branch->branchId) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
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