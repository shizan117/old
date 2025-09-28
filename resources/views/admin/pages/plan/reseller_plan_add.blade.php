@extends ('admin.layouts.master')
@section('title')
    Add New Reseller Plan
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">

                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('reseller.plan.add.post') }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Reseller Name*</label>
                                        <div class="col-10 {{ $errors->has('resellerId') ? 'has-error' : '' }}">
                                            <select class="form-control" name="resellerId" required>
                                                <option value="">Select Reseller Name</option>
                                                @foreach($resellers as $reseller)
                                                    <option value="{{$reseller['resellerId']}}" {{ (collect(old('resellerId'))->contains($reseller['resellerId'])) ? 'selected':'' }}>{{$reseller['resellerName']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('resellerId') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Service Type*</label>
                                        <div class="col-10 {{ $errors->has('type') ? 'has-error' : '' }}">
                                            <select class="form-control" id="type" name="type" required>
                                                <option value="">Select Plan Type</option>
                                                <option value="PPPOE">PPPOE</option>
                                                <option value="Hotspot">Hotspot</option>
                                                <option value="IP">IP</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('type') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Server Name*</label>
                                        <div class="col-10 {{ $errors->has('server_id') ? 'has-error' : '' }}">
                                            <select class="form-control" id="server" name="server_id" required>
                                                <option value="">Select Server Name</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('server_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Plan Name*</label>
                                        <div class="col-10 {{ $errors->has('plan_id') ? 'has-error' : '' }}">
                                            <select class="form-control" id="plan" name="plan_id" required>
                                                <option value="">Select Plan Name</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('plan_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Selling Price*</label>
                                        <div class="col-10 {{ $errors->has('sell_price') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="sell_price" value="{{ old('sell_price') }}" placeholder="Enter Sell Price" required>
                                            <span class="text-danger">{{ $errors->first('sell_price') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Reseller Plan</button>
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