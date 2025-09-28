@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <div class="btn-group m-b-10">
                    <div class="btn-group m-b-10">
                        <a href="{{ route('config.user.add') }}" class="btn btn-primary" style="text-transform: uppercase;">Add New User</a>
                    </div>
                </div>
 

                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        {{-- <th>Reseller ID</th> --}}
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        {{--<th>Branch Name</th>--}}
                        <th>Reseller Name</th>
                        <th>Status</th>
                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @foreach ($users as $user)
                        <tr>

{{--                            @php(($user->branchId != "") ? $branchName = $user->branch->branchName : $branchName = '-')--}}
                            @php(($user->resellerId != "") ? $resellerName = $user->reseller->resellerName : $resellerName = '-')
                            @php(($user->active == 1) ? $status = 'Active' : $status = 'Inactive')
                            <td> {{ $loop->iteration }}</td>
                            {{-- <td>{{$user->resellerId}}</td> --}}
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->roles[0]['name'] ?? '' }}</td>
                            {{--<td>{{ $branchName }}</td>--}}
                            <td>{{ $resellerName }}</td>
                            <td>{{ $status }}</td>
                            <td class="hidden-print">
                                @if(Auth::user()->hasRole('Super-Admin') && !$user->hasRole('Super-Admin'))
                                    <a href="{{ route('user.secret.login', encrypt($user->id)) }}" class="btn-show" title="Secret Login"><i class="fa fa-user-secret"></i></a>
                                    <a href="{{ route('config.user.edit', $user->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                               {{--<a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a>--}}
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
