@extends ('admin.layouts.master')
@section('title')
    Role Permission
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <div class="btn-group m-b-10">
                    <div class="btn-group m-b-10">
                        <a href="{{ route('role.create') }}" class="btn btn-primary" style="text-transform: uppercase;">Add New Role</a>
                    </div>
                </div>


                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td> {{ $loop->iteration }}</td>
                            <td>{{ $role->name }}</td>
                            <td class="hidden-print">
{{--                                    <a href="{{ route('role.show', $role->id) }}" class="btn-show" title="View"><i class="fa fa-eye"></i></a>--}}
                                    <a href="{{ route('role.edit', $role->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
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
