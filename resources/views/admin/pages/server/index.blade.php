@extends ('admin.layouts.master')
@section('title')
Server List
@endsection


@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">
                    <div class="row mb-2">
                        <div class="col-6">
                            <h4 class="m-t-0 header-title">Server List</h4>
                        </div>
                        <div class="col-6 text-right">
                            @can('server_add')
                            <a href="{{ route('server.add') }}" class="btn btn-primary" style="text-transform: uppercase;">Add New Server</a>
                            @endcan
                        </div>
                    </div>
                    <table id="datatable" class="table table-sm table-striped table-bordered table-sm" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Server Name</th>
                            <th>Server IP</th>
                            <th>Server Port</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th class="hidden-print text-right">Manage</th>
                        </tr>
                        </thead>


                        <tbody>
                        @foreach ($servers as $server)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $server->server_name }}</td>
                            <td>{{ $server->server_ip }}</td>
                            <td>{{ $server->server_port }}</td>
                            <td>{{ $server->username }}</td>
                            <td>
                                @if($server->status == 1)
                                <span class="badge badge-success">
                                    Active
                                </span>
                                @else
                                <span class="badge badge-danger">
                                    Inactive
                                </span>
                                @endif

                            </td>
                            <td class="hidden-print text-right">
                                @can('server_edit')
                                <a class="btn btn-sm btn-outline-success" href="{{ route('server.edit', $server->id) }}" title="Edit"><i class="fa fa-edit"></i></a>
                                @endcan

                                @can('server_delete')
                                <a href="{{ route('server.delete', $server->id) }}" onclick="return confirm('Are you sure to delete this server?')"  class="btn-del"> <i class="fa fa-trash-o"></i></a>
                                @endcan
                                
                                @if(setting('using_mikrotik'))
                                <a class="btn btn-sm btn-outline-warning" href="javascript:" onclick="checkServerConnection({{ $server->id }})" title="Check Connection"><i class="fa fa-wifi"></i></a>
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
    <script>
        function checkServerConnection(serverID){
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ route('server.check.connection') }}",
                data: {id: serverID, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    window.location.reload();
                }
            });
        }
    </script>
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
