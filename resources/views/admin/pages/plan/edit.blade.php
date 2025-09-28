@extends ('admin.layouts.master')
@section('title')
    Edit Plan
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" role="form"
                                  action="{{ route('plan.edit.post', $planData->id) }}" method="POST">

                                {{ csrf_field() }}
                                @if(Auth::user()->resellerId == '')
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Name</label>
                                        <div class="col-10 {{ $errors->has('plan_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="plan_name"
                                                   value="{{ $planData->plan_name }}" placeholder="Enter Plan Name">
                                            <span class="text-danger">{{ $errors->first('plan_name') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Type</label>
                                        <div class="col-10">
                                            <input type="text" readonly name="type" class="form-control"
                                                   value="{{ $planData->type }}" placeholder="Enter Plan Name">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server Name</label>
                                        <div class="col-10">
                                            <input type="text" readonly name="server_id" class="form-control"
                                                   value="{{ $serverData->server_name }}" placeholder="Enter Plan Name">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Bandwidth Name</label>
                                        <div class="col-10 {{ $errors->has('bandwidth_id') ? 'has-error' : '' }}">
                                            <select class="form-control" name="bandwidth_id">
                                                <option value="">Select Bandwidth</option>
                                                @foreach($bandwidthData as $bandwidth)
                                                    <option value="{{ $bandwidth->id }}" {{ ($planData->bandwidth_id == $bandwidth->id) ? 'selected':'' }}>{{ $bandwidth->bandwidth_name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('bandwidth_id') }}</span>
                                        </div>
                                    </div>

                                    @if($planData->type == 'IP')
                                        <div class="form-group row" style="display:none;" id="pool_name">
                                    @else
                                        <div class="form-group row" id="pool_name">
                                    @endif

                                        <label class="col-2 col-form-label">Pool Name</label>
                                        <div class="col-10 {{ $errors->has('pool_id') ? 'has-error' : '' }}">
                                            <select class="form-control" id="pool" name="pool_id">
                                                @foreach($poolData as $pool)
                                                    <option value="{{$pool['id']}}" {{ ($planData->pool_id == $pool->id) ? 'selected':'' }}>{{$pool['pool_name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('pool_id') }}</span>
                                        </div>
                                    </div>

                                    @if($planData->type == 'Hotspot')
                                        <div class="form-group row" id="sharedUser">
                                    @else
                                        <div class="form-group row" style="display:none;" id="sharedUser">
                                    @endif


                                        <label class="col-2 col-form-label">Shared User</label>
                                        <div class="col-10 {{ $errors->has('shared_users') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control"
                                                   name="shared_users"
                                                   value="{{ $planData->shared_users }}"
                                                   placeholder="Enter Shared User Number">
                                            <span class="text-danger">{{ $errors->first('shared_users') }}</span>
                                        </div>
                                    </div>

                                    {{--<div class="form-group row">--}}
                                        {{--<label class="col-2 col-form-label">Branch Name</label>--}}
                                        {{--<div class="col-10 {{ $errors->has('branchId') ? 'has-error' : '' }}">--}}
                                            {{--<select class="form-control" name="branchId">--}}
                                                {{--<option value="">Select Branch Name</option>--}}
                                                {{--@foreach($branchData as $branch)--}}
                                                    {{--<option value="{{ $branch['branchId'] }}" {{ ($planData->branchId == $branch['branchId']) ? 'selected':'' }}>{{ $branch['branchName'] }}</option>--}}
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
                                            {{--<input type="text" class="form-control" name="duration"--}}
                                                   {{--value="{{ $planData->duration }}"--}}
                                                   {{--placeholder="Enter Plan Duration">--}}
                                            {{--<span class="text-danger">{{ $errors->first('duration') }}</span>--}}
                                        {{--</div>--}}
                                        {{--<div class="col-2 {{ $errors->has('duration_unit') ? 'has-error' : '' }}">--}}
                                            {{--<select class="form-control" name="duration_unit">--}}
                                                {{--<option value="1" {{ ($planData->duration == 1) ? 'selected':'' }}>--}}
                                                    {{--Month--}}
                                                {{--</option>--}}
                                                {{--<option value="2" {{ ($planData->duration == 2) ? 'selected':'' }}>--}}
                                                    {{--Day--}}
                                                {{--</option>--}}
                                            {{--</select>--}}
                                            {{--<span class="text-danger">{{ $errors->first('duration_unit') }}</span>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Price</label>
                                        <div class="col-10 {{ $errors->has('plan_price') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control"
                                                   name="plan_price"
                                                   value="{{ $planData->plan_price }}"
                                                   placeholder="Enter Plan Price">
                                            <span class="text-danger">{{ $errors->first('plan_price') }}</span>
                                        </div>
                                    </div>

                                @endif

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" class="btn btn-info waves-effect waves-light">
                                            Edit Plan
                                        </button>
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
    {{--<script src="{{ asset('assets/js/custom-js.app.js') }}" type="text/javascript"></script>--}}
    @include('admin.layouts.custom-js')
@endsection