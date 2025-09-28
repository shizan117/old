@extends ('admin.layouts.master')
@section('title')
Add New Role
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">
                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('role.store') }}" method="POST">

                                    {{ csrf_field() }}
                                    {{--<div class="form-group mb-0 justify-content-end row">--}}
                                        {{--<div class="col-10">--}}
                                            {{--<h4>Add New Role</h4>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Role Name</label>
                                        <div class="col-10 {{ $errors->has('name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter Role Name">
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        @foreach($permission_groups as $key => $permission_group)
                                            <div class="col-6 mb-2">

                                                <div class="card">
                                                    <div class="card-header" style="font-weight: 700;font-size: 20px;">
                                                        {{ ucfirst($key) }}
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            @foreach($permission_group as $permission)
                                                            <div class="col-6">
                                                                <input type="checkbox" name="permission[]"
                                                                       value="{{ $permission->name }}"
                                                                       id="{{ $permission->name }}"
                                                                style="font-size: 20px;">
                                                                @php
                                                                    $_temp = explode('_',$permission->name);
                                                                    $_label = end($_temp);
                                                                @endphp
                                                                <label for="{{ $permission->name }}" style="font-size: 20px;cursor: pointer;">
                                                                    {{ ucwords($_label) }}
                                                                </label>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                {{--<h4>{{ ucfirst($key) }}</h4>--}}

                                                {{--@foreach($permission_group as $permission)--}}
                                                    {{--<input type="checkbox" name="permission[]" value="{{ $permission->name }}" id="{{ $permission->name }}">--}}
                                                    {{--<label for="{{ $permission->name }}">{{ $permission->name }}</label>--}}
                                                {{--@endforeach--}}
                                            </div>
                                        @endforeach
                                    </div>


                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Role</button>
                                            <a href="{{ route('roles') }}" class="btn btn-secondary">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                    <!-- end row -->

                </div> <!-- end card-box -->
            </div><!-- end col -->
        </div>
        <!-- end row -->

@endsection

@section('custom_js')
@endsection
