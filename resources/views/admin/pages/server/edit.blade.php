@extends ('admin.layouts.master')
@section('title')
    Add New Server
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('server.edit.post', $serverData->id ) }}" method="POST">
                                    {{ csrf_field() }}

                                    <input type="hidden" name="id" id="id" value="{{ $serverData->id }}">
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server Name</label>
                                        <div class="col-10 {{ $errors->has('server_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="server_name" value="{{ $serverData->server_name }}" placeholder="Enter Server Name">
                                            <span class="text-danger">{{ $errors->first('server_name') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server IP</label>
                                        <div class="col-10 {{ $errors->has('server_ip') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="server_ip" value="{{ $serverData->server_ip }}" placeholder="Enter Server IP">
                                            <span class="text-danger">{{ $errors->first('server_ip') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server API Port</label>
                                        <div class="col-10 {{ $errors->has('server_port') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="server_port" value="{{ $serverData->server_port }}" placeholder="Enter Server API Port">
                                            <span class="text-danger">{{ $errors->first('server_port') }}</span>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row">--}}
                                        {{--<label class="col-2 col-form-label">Server Web Port</label>--}}
                                        {{--<div class="col-10 {{ $errors->has('web_port') ? 'has-error' : '' }}">--}}
                                            {{--<input type="text" class="form-control" name="web_port" value="{{ $serverData->web_port }}" placeholder="Enter Server Web Port">--}}
                                            {{--<span class="text-danger">{{ $errors->first('web_port') }}</span>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">User Name</label>
                                        <div class="col-10 {{ $errors->has('username') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="username" value="{{ $serverData->username }}" placeholder="Enter Server User Name">
                                            <span class="text-danger">{{ $errors->first('username') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Update Password</label>
                                        <div class="col-10 {{ $errors->has('password') ? 'has-error' : '' }}">
                                            <input type="password" class="form-control" value="" name="password" placeholder="Type password, if you want to change">
                                            <span class="text-danger">{{ $errors->first('password') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Status</label>
                                        <div class="col-10 {{ $errors->has('status') ? 'has-error' : '' }}">
                                            <select name="status" class="form-control">
                                                <option value="1" {{ ($serverData->status == 1) ? 'selected':'' }}>Active</option>
                                                <option value="0" {{ ($serverData->status == 0) ? 'selected':'' }}>Inactive</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('status') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Update Server</button>
                                            <a href="{{ route('server.index') }}" class="btn btn-secondary">Cancel</a>
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
