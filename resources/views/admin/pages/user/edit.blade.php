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
                                <form class="form-horizontal" role="form" action="{{ route('config.user.edit.post',$userData->id) }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <h4>{{ $page_title }}</h4>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Name</label>
                                        <div class="col-10 {{ $errors->has('name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="name" value="{{ $userData->name }}" placeholder="Enter Name">
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Email</label>
                                        <div class="col-10 {{ $errors->has('email') ? 'has-error' : '' }}">
                                            <input type="email" class="form-control" name="email" value="{{ $userData->email }}" placeholder="Enter Client Email Address">
                                            <span class="text-danger">{{ $errors->first('email') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Password</label>
                                        <div class="col-10 {{ $errors->has('password') ? 'has-error' : '' }}">
                                            <input type="password" class="form-control" name="password" value="" placeholder="Enter New Password">
                                            <span class="text-danger">{{ $errors->first('password') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Confirm Password</label>
                                        <div class="col-10 {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                                            <input type="password" class="form-control" name="password_confirmation" value="" placeholder="Confirm New Password">
                                            <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Phone Number</label>
                                        <div class="col-10 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="phone" value="{{ $userData->phone }}" placeholder="Enter Phone Number">
                                            <span class="text-danger">{{ $errors->first('phone') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Role Name</label>

                                        <div class="col-10 {{ $errors->has('role_name') ? 'has-error' : '' }}">
                                            <select class="form-control" name="role_name" id="role_name">
                                                <option value="">Select Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}" {{ $userData->hasRole($role->name)?'selected':''}}>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('role_name') }}</span>
                                        </div>
                                    </div>

                                    <input type="hidden" name="branch_name">
                                    {{--<div style="display:none;" id="branch_name">--}}

                                        {{--<div class="form-group row">--}}
                                            {{--<label class="col-2 col-form-label">Branch Name</label>--}}
                                            {{--<div class="col-10 {{ $errors->has('branch_name') ? 'has-error' : '' }}">--}}
                                                {{--<select class="form-control" name="branch_name">--}}
                                                    {{--<option value="">Select Branch</option>--}}
                                                    {{--@foreach($branches as $branch)--}}
                                                        {{--<option value="{{$branch['branchId']}}" {{($branch['branchId']==$userData->branchId)?'selected':''}}>{{$branch['branchName']}}</option>--}}
                                                    {{--@endforeach--}}
                                                {{--</select>--}}
                                                {{--<span class="text-danger">{{ $errors->first('branch_name') }}</span>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}


                                    <div style="display:none;" id="reseller_name">
                                        <div class="form-group row">
                                            <label class="col-2 col-form-label">Reseller Name</label>
                                            <div class="col-10 {{ $errors->has('reseller_name') ? 'has-error' : '' }}">
                                                <select class="form-control" name="reseller_name">
                                                    <option value="">Select Reseller</option>
                                                    @foreach($resellers as $reseller)
                                                        <option value="{{$reseller['resellerId']}}" {{($reseller['resellerId']==$userData->resellerId)?'selected':''}}>{{$reseller['resellerName']}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger">{{ $errors->first('reseller_name') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Status</label>

                                        <div class="col-10 {{ $errors->has('status') ? 'has-error' : '' }}">
                                            <select class="form-control" name="status" id="status">
                                                <option value="1" {{($userData->active===1)?'selected':''}}>Active</option>
                                                <option value="0" {{($userData->active===0)?'selected':''}}>Inactive</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('status') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Save</button>
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
            var is_reseller = "{{$userData->resellerId }}";
            {{--var is_branch = "{{$userData->branchId }}";--}}

            // if (is_branch != '') {
            //     // $('#branch_name').show();
            //     $('#reseller_name').hide();
            // } else
            if(is_reseller != '') {
                // $('#branch_name').hide();
                $('#reseller_name').show();
            } else {
                // $('#branch_name').hide();
                $('#reseller_name').hide();
            }

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
