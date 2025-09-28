@extends ('admin.layouts.master')
@section('title')
    Add New Plan
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('plan.add.post') }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Name</label>
                                        <div class="col-10 {{ $errors->has('plan_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="plan_name" value="{{ old('plan_name') }}" placeholder="Enter Plan Name">
                                            <span class="text-danger">{{ $errors->first('plan_name') }}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Type</label>
                                        <div class="col-10 {{ $errors->has('type') ? 'has-error' : '' }}">
                                            <select class="form-control" id="type" name="type">
                                                <option value="">Select Plan Type</option>
                                                    <option value="PPPOE" {{ (collect(old('type'))->contains('PPPOE')) ? 'selected':'' }}>PPPOE</option>
                                                    <option value="Hotspot" {{ (collect(old('type'))->contains('Hotspot')) ? 'selected':'' }}>Hotspot</option>
                                                    <option value="IP" {{ (collect(old('type'))->contains('IP')) ? 'selected':'' }}>IP</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('type') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server Name</label>
                                        <div class="col-10 {{ $errors->has('server_id') ? 'has-error' : '' }}">
                                            <select class="form-control" id="server" name="server_id">
                                                <option value="">Select Server Name</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('server_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Bandwidth Name</label>
                                        <div class="col-10 {{ $errors->has('bandwidth_id') ? 'has-error' : '' }}">
                                            <select class="form-control" name="bandwidth_id">
                                                <option value="">Select Bandwidth</option>
                                                @foreach($bandwidths as $bandwidth)
                                                    <option value="{{$bandwidth['id']}}" {{ (collect(old('bandwidth_id'))->contains($bandwidth['id'])) ? 'selected':'' }}>{{$bandwidth['bandwidth_name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('bandwidth_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row" style="display:none;" id="pool_name">
                                        <label class="col-2 col-form-label">Pool Name</label>
                                        <div class="col-10 {{ $errors->has('pool_id') ? 'has-error' : '' }}">
                                            <select class="form-control" id="pool" name="pool_id">
                                                <option value="">Select Pool Name</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('pool_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row" style="display:none;" id="sharedUser">
                                        <label class="col-2 col-form-label">Shared User</label>
                                        <div class="col-10 {{ $errors->has('shared_users') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="shared_users" value="{{ old('shared_users') }}" placeholder="Enter Shared User Number">
                                            <span class="text-danger">{{ $errors->first('shared_users') }}</span>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row">--}}
                                        {{--<label class="col-2 col-form-label">Branch Name</label>--}}
                                        {{--<div class="col-10 {{ $errors->has('branchId') ? 'has-error' : '' }}">--}}
                                            {{--<select class="form-control" name="branchId">--}}
                                                {{--<option value="">Select Branch Name</option>--}}
                                                {{--@foreach($branches as $branch)--}}
                                                    {{--<option value="{{$branch['branchId']}}" {{ (collect(old('branchId'))->contains($branch['branchId'])) ? 'selected':'' }}>{{$branch['branchName']}}</option>--}}
                                                {{--@endforeach--}}
                                            {{--</select>--}}
                                            {{--<span class="text-danger">{{ $errors->first('branchId') }}</span>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                        <input type="hidden" name="duration" value="1">
                                        <input type="hidden" name="duration_unit" value="1">
                                    {{--<div class="form-group row">--}}
                                        {{--<label class="col-2 col-form-label">Duration</label>--}}
                                        {{--<div class="col-8 {{ $errors->has('duration') ? 'has-error' : '' }}">--}}
                                            {{--<input type="text" class="form-control" name="duration" value="{{ old('duration') }}" placeholder="Enter Plan Duration">--}}
                                            {{--<span class="text-danger">{{ $errors->first('duration') }}</span>--}}
                                        {{--</div>--}}
                                        {{--<div class="col-2 {{ $errors->has('duration_unit') ? 'has-error' : '' }}">--}}
                                            {{--<select class="form-control" name="duration_unit">--}}
                                                {{--<option value="1" {{ (collect(old('duration_unit'))->contains(1)) ? 'selected':'' }}>Month</option>--}}
                                                {{--<option value="2" {{ (collect(old('duration_unit'))->contains(2)) ? 'selected':'' }}>Day</option>--}}
                                            {{--</select>--}}
                                            {{--<span class="text-danger">{{ $errors->first('duration_unit') }}</span>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Price</label>
                                        <div class="col-10 {{ $errors->has('plan_price') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="plan_price" value="{{ old('plan_price') }}" placeholder="Enter Plan Price">
                                            <span class="text-danger">{{ $errors->first('plan_price') }}</span>
                                        </div>
                                    </div>



                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Plan</button>
                                            <a href="{{ route('plan.index') }}" class="btn btn-secondary">Cancel</a>
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
    @include('admin.layouts.custom-js')
@endsection