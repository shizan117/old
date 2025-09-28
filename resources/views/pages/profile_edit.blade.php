@extends ('layouts.master')
@section('title')
Edit Profile
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">
                    <br><br>
                    <form class="form-horizontal" role="form" action="{{ route('client.profile.edit.post') }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="form-group row">
                            <label class="col-2 col-form-label">Full Name</label>
                            <div class="col-10 {{ $errors->has('name') ? 'has-error' : '' }}">
                                <input type="text" class="form-control" name="name" value="{{ $profile->client_name }}" readonly>
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-2 col-form-label">User Name</label>
                            <div class="col-10 {{ $errors->has('username') ? 'has-error' : '' }}">
                                <input type="text" class="form-control" name="username" value="{{ $profile->username }}" readonly>
                                <span class="text-danger">{{ $errors->first('username') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-2 col-form-label">Email</label>
                            <div class="col-10 {{ $errors->has('email') ? 'has-error' : '' }}">
                                <input type="text" class="form-control" name="email" value="{{ $profile->email }}" placeholder="Enter Email">
                                <span class="text-danger">{{ $errors->first('email') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-2 col-form-label">Phone No</label>
                            <div class="col-10 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                <input type="text" class="form-control" name="phone" value="{{ $profile->phone }}" placeholder="Enter Phone No">
                                <span class="text-danger">{{ $errors->first('phone') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-2 col-form-label">Old Password</label>
                            <div class="col-10 {{ $errors->has('old_password') ? 'has-error' : '' }}">
                                <input type="password" class="form-control" name="old_password" value="" placeholder="Old Password">
                                <span class="text-danger">{{ $errors->first('old_password') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-2 col-form-label">New Password</label>
                            <div class="col-10 {{ $errors->has('new_password') ? 'has-error' : '' }}">
                                <input type="password" class="form-control" name="new_password" value="" placeholder="New Password">
                                <span class="text-danger">{{ $errors->first('new_password') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-2 col-form-label">Confirm New Password</label>
                            <div class="col-10 {{ $errors->has('new_password_confirmation') ? 'has-error' : '' }}">
                                <input type="password" class="form-control" name="new_password_confirmation" value="" placeholder="Confirm New Password">
                                <span class="text-danger">{{ $errors->first('new_password_confirmation') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-2 col-form-label">Profile Image</label>
                            <div class="col-10 {{ $errors->has('image') ? 'has-error' : '' }}">
                                @if(file_exists('assets/images/clients/'.Auth::user()->user_image))
                                    <img src="{{ asset('assets/images/clients/'.Auth::user()->user_image) }}" width="70" height="70">
                                @else
                                    <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" width="70" height="70">
                                @endif
                                <input type="file" name="image" />
                                <span class="text-danger">{{ $errors->first('image') }}</span>
                            </div>
                        </div>

                        {{--@if($profile->plan->type != 'IP')--}}

                        {{--<div class="form-group row">--}}
                            {{--<label class="col-2 col-form-label">New Server Password</label>--}}
                            {{--<div class="col-10 {{ $errors->has('new_server_password') ? 'has-error' : '' }}">--}}
                                {{--<input type="password" class="form-control" name="new_server_password" value="" placeholder="New Server Password">--}}
                                {{--<span class="text-danger">{{ $errors->first('new_server_password') }}</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}

                        {{--<div class="form-group row">--}}
                            {{--<label class="col-2 col-form-label">Confirm New Server Password</label>--}}
                            {{--<div class="col-10 {{ $errors->has('new_password_confirmation') ? 'has-error' : '' }}">--}}
                                {{--<input type="password" class="form-control" name="new_server_password_confirmation" value="" placeholder="Confirm New Server Password">--}}
                                {{--<span class="text-danger">{{ $errors->first('new_server_password_confirmation') }}</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--@endif--}}

                        {{ $profile->user_image }}



                        <div class="form-group mb-0 justify-content-end row">
                            <div class="col-10">
                                <button type="submit" class="btn btn-info waves-effect waves-light">Update Settings</button>
                            </div>
                        </div>




                    </form>

                </div>
            </div>
        </div> <!-- end row -->
        <!-- end row -->

@endsection
