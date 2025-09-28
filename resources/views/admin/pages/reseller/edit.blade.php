@extends ('admin.layouts.master')
@section('title')
    Edit Reseller
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" href="#profile"
                       role="tab" data-toggle="tab"><i class="fa fa-edit"></i> Edit Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#payment"
                       role="tab" data-toggle="tab"><i class="fa fa-credit-card"></i> Bkash Payment Setup</a>
                </li>
            </ul>
            <form class="form-horizontal" role="form"
                  action="{{ route('reseller.edit.post', $reseller->resellerId) }}" method="POST">
                {{ csrf_field() }}
                <div class="card-box">
                    <!-- Tab panes -->
                    <div class="tab-content">
                            {{-- Reseller Profile--}}
                            <div role="tabpanel" class="tab-pane active" id="profile">

                                <div class="row">
                                    <div class="col-12">
                                        <div class="p-20">
                                            {{ csrf_field() }}
                                            <div class="form-group row">
                                                <label class="col-2 col-form-label">Reseller Name</label>
                                                <div class="col-10 {{ $errors->has('resellerName') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="resellerName"
                                                           value="{{ $reseller->resellerName }}" placeholder="Enter Reseller Name">
                                                    <span class="text-danger">{{ $errors->first('resellerName') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-2 col-form-label">Reseller Location</label>
                                                <div class="col-10 {{ $errors->has('resellerLocation') ? 'has-error' : '' }}">
                                                    <textarea class="form-control" name="resellerLocation">{{ $reseller->resellerLocation }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('resellerLocation') }}</span>
                                                </div>
                                            </div>

                                            @if($setting['invoice_system'] == 'fixed')
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">Client Expire Date</label>
                                                    <div class="col-10 {{ $errors->has('c_exp_date') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="c_exp_date" value="{{ $reseller->c_exp_date }}" placeholder="Client Expire Date">
                                                        <span class="text-danger">{{ $errors->first('c_exp_date') }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                                <!-- end row -->
                            </div>

                            <!-- Payment -->
                            <div role="tabpanel" class="tab-pane" id="payment">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="p-20">

                                            @if($reseller->accounts->where('account_type', 'bKash')->count() > 0)
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">API Username</label>
                                                    <div class="col-10 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="bkash_username" value="{{$reseller->bkash_username}}" placeholder="Enter bKash Username">
                                                        <span class="text-danger">{{ $errors->first('bkash_username') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">API Password</label>
                                                    <div class="col-10 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="bkash_password" value="{{$reseller->bkash_password}}" placeholder="Enter bKash Password">
                                                        <span class="text-danger">{{ $errors->first('bkash_password') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">bKash API App Key</label>
                                                    <div class="col-10 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="bkash_app_key" value="{{$reseller->bkash_app_key}}" placeholder="Enter bKash App Key">
                                                        <span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">bKash API App Secret</label>
                                                    <div class="col-10 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="bkash_app_secret" value="{{ $reseller->bkash_app_secret }}" placeholder="Enter bKash App Secret">
                                                        <span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">bKash Script Url</label>
                                                    <div class="col-10 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="bkash_url" value="{{ $reseller->bkash_url }}" placeholder="Enter bKash App Secret">
                                                        <span class="text-danger">{{ $errors->first('bkash_url') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">Production API Root Url</label>
                                                    <div class="col-10 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="bkash_production_root_url" value="{{ $reseller->bkash_production_root_url }}" placeholder="Enter Production API Root Url">
                                                        <span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-2 col-form-label">Take bKash Charge</label>
                                                    <div class="col-10 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">
                                                        <select name="bkash_charge" class="form-control">
                                                            <option value="NO" {{ ($reseller->bkash_charge == 'NO') ? 'selected':'' }}>NO</option>
                                                            <option value="YES" {{ ($reseller->bkash_charge == 'YES') ? 'selected':'' }}>YES</option>
                                                        </select>
                                                        <span class="text-danger">{{ $errors->first('bkash_charge') }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-danger">
                                                    Please create a bKash account in reseller panel to get payment via bKash.
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                                <!-- end row -->
                            </div>

                        </div>


                    <div class="form-group mb-0 justify-content-end row">
                        <div class="col-7">
                            <button type="submit" class="btn btn-info waves-effect waves-light">Update Reseller</button>
                            <a href="{{ route('reseller.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
                <!-- end card-box -->

            </form>
        </div><!-- end col -->
    </div>
    <!-- end row -->
@endsection
