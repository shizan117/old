@extends ('admin.layouts.master')
@section('title')
{{ $page_title }}
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">
                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('config.user.add.post') }}" method="POST">

                                    {{ csrf_field() }}

                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <h4 class="text-center" style="text-transform: uppercase; color:#ffc107;">{{ $page_title }}</h4>
                                            </div>
                                        </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Name <span style="color: #ffc107;">*</span></label>
                                                <div class="col-8 {{ $errors->has('name') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Enter Name" required>
                                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Email <span style="color: #ffc107;">*</span></label>
                                                <div class="col-8 {{ $errors->has('email') ? 'has-error' : '' }}">
                                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Enter Client Email Address" required>
                                                    <span class="text-danger">{{ $errors->first('email') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Password <span style="color: #ffc107;">*</span></label>
                                                <div class="col-8 {{ $errors->has('password') ? 'has-error' : '' }}">
                                                    <input type="password" class="form-control" name="password" value="{{ old('password') }}" placeholder="Enter Login Password" required>
                                                    <span class="text-danger">{{ $errors->first('password') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Confirm Password <span style="color: #ffc107;">*</span></label>
                                                <div class="col-8 {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                                    <input type="password" class="form-control" name="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="Enter Confirm Login Password" required>
                                                    <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Phone Number <span style="color: #ffc107;">*</span></label>
                                                <div class="col-8 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="Enter Phone Number" required>
                                                    <span class="text-danger">{{ $errors->first('phone') }}</span>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-6">
                                            
                                    <div class="form-group row">
                                        <label class="col-4 col-form-label">Role Name <span style="color: #ffc107;">*</span></label>

                                        <div class="col-8 {{ $errors->has('role_name') ? 'has-error' : '' }}">
                                            <select class="form-control" name="role_name" id="role_name" required>
                                                <option value="">Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('role_name') }}</span>
                                        </div>
                                    </div>
                                        </div>
                                    </div>


                                    

                                   


                                    <input type="hidden" name="branch_name">
                                    {{--<div style="display:none;" id="branch_name">--}}

                                        {{--<div class="form-group row">--}}
                                            {{--<label class="col-4 col-form-label">Branch Name</label>--}}
                                            {{--<div class="col-10 {{ $errors->has('branch_name') ? 'has-error' : '' }}">--}}
                                                {{--<select class="form-control" name="branch_name">--}}
                                                    {{--<option value="">Select Branch</option>--}}
                                                    {{--@foreach($branches as $branch)--}}
                                                        {{--<option value="{{$branch['branchId']}}">{{$branch['branchName']}}</option>--}}
                                                    {{--@endforeach--}}
                                                {{--</select>--}}
                                                {{--<span class="text-danger">{{ $errors->first('branch_name') }}</span>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                    <div style="display:none;" id="reseller_name">

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Reseller Name <span style="color: #ffc107;">*</span></label>
                                            <div class="col-8 {{ $errors->has('reseller_name') ? 'has-error' : '' }}">
                                                <select class="form-control" name="reseller_name">
                                                    <option value="">Select Reseller</option>
                                                    @foreach($resellers as $reseller)
                                                        <option value="{{$reseller['resellerId']}}">{{$reseller['resellerName']}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger">{{ $errors->first('reseller_name') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Status <span style="color: #ffc107;">*</span></label>

                                        <div class="col-10 {{ $errors->has('status') ? 'has-error' : '' }}" required>
                                            <select class="form-control" name="status" id="status">
                                                <option value="1" {{(old('status')===1)?'selected':''}}>Active</option>
                                                <option value="0" {{(old('status')===0)?'selected':''}}>Inactive</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('status') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add User</button>
                                            <a href="{{ route('config.users') }}" class="btn btn-secondary">Cancel</a>
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
    <script type="text/javascript">
        $(document).ready(function () {

            $("#role_name").change(function () {
                var role_name = $(this).val();
                if(role_name == 'Reseller') {
                    // $('#branch_name').hide();
                    $('#reseller_name').show();
                } else {
                    // $('#branch_name').show();
                    $('#reseller_name').hide();
                }

            });
        });
    </script>
@endsection
