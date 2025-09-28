@extends ('admin.layouts.master')
@section('title')
Pool List
@endsection


@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">
                    <div class="row mb-2">
                        <div class="col-6">
                            <h4 class="m-t-0 header-title">Pool List</h4>
                        </div>
                        <div class="col-6 text-right">
                            @can('pool_add')
                            <a href="{{ route('pool.add') }}" class="btn btn-primary" style="text-transform: uppercase;">Add New Pool</a>
                            @endcan
                        </div>
                    </div>
                    <table id="datatable" class="table table-sm table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Pool Name</th>
                            <th>IP Range</th>
                            <th>Server Name</th>
                            <th class="hidden-print">Manage</th>
                        </tr>
                        </thead>


                        <tbody>
                        @foreach ($poolData as $dataPool)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $dataPool->pool_name }}</td>
                            <td>{{ $dataPool->range_ip }}</td>
                            <td>{{ $dataPool->server->server_name }}</td>
                            <td class="hidden-print">
                                @can('pool_edit')
                                <a href="{{ route('pool.edit', $dataPool->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;&nbsp;
                                @endcan

                                @can('pool_delete')
                                <a href="{{ route('pool.delete', $dataPool->id) }}" onclick="return confirm('Are you sure to delete this pool?')"  class="btn-del"> <i class="fa fa-trash-o"></i></a>
                                @endcan


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
