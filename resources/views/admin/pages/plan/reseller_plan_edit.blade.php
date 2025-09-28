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
                                  action="{{ route('reseller.plan.edit.post', $planData->id) }}" method="POST">

                                {{ csrf_field() }}
                                @role('Reseller')
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Name</label>
                                        <div class="col-10">
                                            <input type="text" readonly class="form-control"
                                                   value="{{ $planData->plan->plan_name }}({{ $planData->plan->type }})">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Duration</label>
                                        <div class="col-10">
                                            @php(($planData->plan->duration > 1) ?
                                            (($planData->plan->duration_unit == 1) ? $unit = 'Months' : $unit = 'Days') :
                                            (($planData->plan->duration_unit == 1) ? $unit = 'Month' : $unit = 'Day'))
                                            <input type="text" readonly class="form-control"
                                                   value="{{ $planData->plan->duration.' '.$unit }}">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Buy Price</label>
                                        <div class="col-10">
                                            <input type="text" readonly class="form-control" value="{{ $planData->sell_price }}">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Sell Price</label>
                                        <div class="col-10 {{ $errors->has('reseller_sell_price') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control"
                                                   name="reseller_sell_price"
                                                   value="{{ $planData->reseller_sell_price }}"
                                                   placeholder="Enter Plan Price">
                                            <span class="text-danger">{{ $errors->first('reseller_sell_price') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Reseller Name</label>
                                        <div class="col-10 {{ $errors->has('resellerId') ? 'has-error' : '' }}">
                                            <select class="form-control" name="resellerId">
                                                <option value="">Select Reseller Name</option>
                                                @foreach($resellerData as $reseller)
                                                    <option value="{{$reseller['resellerId']}}" {{ ($planData->resellerId == $reseller['resellerId']) ? 'selected':'' }}>{{$reseller['resellerName']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('resellerId') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Service Type</label>
                                        <div class="col-10 {{ $errors->has('type') ? 'has-error' : '' }}">
                                            <select class="form-control" id="type" name="type">
                                                <option value="">Select Plane Type</option>
                                                <option value="PPPOE" {{ ($planData->plan->type == 'PPPOE') ? 'selected':'' }}>
                                                    PPPOE
                                                </option>
                                                <option value="Hotspot" {{ ($planData->plan->type == 'Hotspot') ? 'selected':'' }}>
                                                    Hotspot
                                                </option>
                                                <option value="IP" {{ ($planData->plan->type == 'IP') ? 'selected':'' }}>
                                                    IP
                                                </option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('type') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server Name</label>
                                        <div class="col-10 {{ $errors->has('server_id') ? 'has-error' : '' }}">
                                            <select class="form-control" id="server" name="server_id">
                                                <option value="">Select Server Name</option>
                                                @foreach($serverData as $server)
                                                    <option value="{{$server['id']}}" {{ ($planData->plan->server->id == $server->id) ? 'selected':'' }}>{{$server['server_name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('server_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Name</label>
                                        <div class="col-10 {{ $errors->has('plan_id') ? 'has-error' : '' }}">
                                            <select class="form-control" id="plan" name="plan_id">
                                                <option value="">Select Plan Name</option>
                                                @foreach($plans as $plan)
                                                    <option value="{{$plan['id']}}" {{ ($planData->plan_id == $plan->id) ? 'selected':'' }}>{{$plan['plan_name']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('plan_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Selling Price</label>
                                        <div class="col-10 {{ $errors->has('sell_price') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="sell_price" value="{{ $planData->sell_price }}"
                                                   placeholder="Enter Sell Price">
                                            <span class="text-danger">{{ $errors->first('sell_price') }}</span>
                                        </div>
                                    </div>
                                @endrole

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" class="btn btn-info waves-effect waves-light">
                                            Save Plan
                                        </button>
                                        <a href="{{ route('reseller.plan.index') }}" class="btn btn-secondary">Cancel</a>
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
