@extends ('admin.layouts.master')
@section('title')
Settings Payment Gateway
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs nav-fill">
                <li class="nav-item">
                    <a class="nav-link active" href="#bkash-settings"
                       role="tab" data-toggle="tab"><i class="fa fa-file-text"></i> Bkash Payment Gateway</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#nagad-settings"
                       role="tab" data-toggle="tab"><i class="fa fa-file-text"></i> Nagad Payment Gateway</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#rocket-settings"
                       role="tab" data-toggle="tab"><i class="fa fa-file-text"></i> Rocket Payment Gateway</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#ssl-settings"
                       role="tab" data-toggle="tab"><i class="fa fa-file-text"></i> SSLCOMMERZ Payment Gateway</a>
                </li>
                {{--<li class="nav-item">--}}
                    {{--<a class="nav-link" href="#nagad-settings"--}}
                       {{--role="tab" data-toggle="tab"><i class="fa fa-cog"></i> Nagad Payment Gateway</a>--}}
                {{--</li>--}}
            </ul>

            <div class="card-box">
                <!-- Tab panes -->
                <div class="tab-content pt-3 pt-md-0 p-0 p-md-4">
                    <!-- BKASH SETTINGS -->
                    <div role="tabpanel" class="tab-pane active" id="bkash-settings">
                        <?php
                                $ac_count = \App\Account::where('resellerId', Auth::user()->resellerId)->where('account_type', 'bKash')->count();
                        ?>
                        @if($ac_count > 0)
                            <div class="row">
                            <div class="col-12">
                                <h4 class="text-center">Bkash Payment Gateway</h4>
                                <div class="card-box table-responsive p-0 p-md-3">
                                   
                                    <form class="form-horizontal" role="form" action="{{ route('config.bkash.api.post') }}" method="POST" enctype="multipart/form-data">
                                        {{ csrf_field() }}

                                        @if( Auth::user()->resellerId == '')
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Username</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_username" value="{{$setting['bkash_username']}}" placeholder="Enter bKash Username">
                                                    <span class="text-danger">{{ $errors->first('bkash_username') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Password</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_password" value="{{$setting['bkash_password']}}" placeholder="Enter bKash Password">
                                                    <span class="text-danger">{{ $errors->first('bkash_password') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Key</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_key" value="{{$setting['bkash_app_key']}}" placeholder="Enter bKash App Key">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_secret" value="{{$setting['bkash_app_secret']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash Script Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_url" value="{{$setting['bkash_checkout_script_url']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Production API Root Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_production_root_url" value="{{$setting['bkash_pr_root_url']}}" placeholder="Enter Production API Root Url">
                                                    <span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">
                                                    <select name="bkash_charge" class="form-control">
                                                        <option value="NO" {{ ($setting['bkash_charge'] == 'NO') ? 'selected':'' }}>NO</option>
                                                        <option value="YES" {{ ($setting['bkash_charge'] == 'YES') ? 'selected':'' }}>YES</option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('bkash_charge') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Username</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_username" value="{{$r->bkash_username}}" placeholder="Enter bKash Username">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_username') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Password</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_password" value="{{$r->bkash_password}}" placeholder="Enter bKash Password">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_password') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_key" value="{{$r->bkash_app_key}}" placeholder="Enter bKash App Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_secret" value="{{ $r->bkash_app_secret }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash Script Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_url" value="{{ $r->bkash_url }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Production API Root Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_production_root_url" value="{{ $r->bkash_production_root_url }}" placeholder="Enter Production API Root Url">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">--}}
                                                    {{--<select name="bkash_charge" class="form-control">--}}
                                                        {{--<option value="NO" {{ ($r->bkash_charge == 'NO') ? 'selected':'' }}>NO</option>--}}
                                                        {{--<option value="YES" {{ ($r->bkash_charge == 'YES') ? 'selected':'' }}>YES</option>--}}
                                                    {{--</select>--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_charge') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        @endif


                                        <div class="form-group mb-0 justify-content-end row">
                                            <div class="col-md-10 col-8">
                                                <button type="submit" class="btn btn-info waves-effect waves-light">Update Settings</button>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div> <!-- end row -->
                        @else
                            <div class="alert alert-danger">Please Create a bKash Account to get payment via bKash</div>
                            <a href="{{ route('account.add') }}" class="btn btn-primary">Add Account</a>
                        @endif
                    </div>

                    <div role="tabpanel" class="tab-pane active" id="nagad-settings">
                        <?php
                                $ac_count = \App\Account::where('resellerId', Auth::user()->resellerId)->where('account_type', 'bKash')->count();
                        ?>
                        @if($ac_count > 0)
                            <div class="row">
                            <div class="col-12">
                                <h4 class="text-center">Nagad Payment Gateway</h4>
                                <div class="card-box table-responsive p-0 p-md-3">
                                  
                                    <form class="form-horizontal" role="form" action="{{ route('config.bkash.api.post') }}" method="POST" enctype="multipart/form-data">
                                        {{ csrf_field() }}

                                        @if( Auth::user()->resellerId == '')
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Username</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_username" value="{{$setting['bkash_username']}}" placeholder="Enter bKash Username">
                                                    <span class="text-danger">{{ $errors->first('bkash_username') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Password</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_password" value="{{$setting['bkash_password']}}" placeholder="Enter bKash Password">
                                                    <span class="text-danger">{{ $errors->first('bkash_password') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Key</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_key" value="{{$setting['bkash_app_key']}}" placeholder="Enter bKash App Key">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_secret" value="{{$setting['bkash_app_secret']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash Script Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_url" value="{{$setting['bkash_checkout_script_url']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Production API Root Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_production_root_url" value="{{$setting['bkash_pr_root_url']}}" placeholder="Enter Production API Root Url">
                                                    <span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">
                                                    <select name="bkash_charge" class="form-control">
                                                        <option value="NO" {{ ($setting['bkash_charge'] == 'NO') ? 'selected':'' }}>NO</option>
                                                        <option value="YES" {{ ($setting['bkash_charge'] == 'YES') ? 'selected':'' }}>YES</option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('bkash_charge') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Username</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_username" value="{{$r->bkash_username}}" placeholder="Enter bKash Username">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_username') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Password</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_password" value="{{$r->bkash_password}}" placeholder="Enter bKash Password">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_password') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_key" value="{{$r->bkash_app_key}}" placeholder="Enter bKash App Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_secret" value="{{ $r->bkash_app_secret }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash Script Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_url" value="{{ $r->bkash_url }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Production API Root Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_production_root_url" value="{{ $r->bkash_production_root_url }}" placeholder="Enter Production API Root Url">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">--}}
                                                    {{--<select name="bkash_charge" class="form-control">--}}
                                                        {{--<option value="NO" {{ ($r->bkash_charge == 'NO') ? 'selected':'' }}>NO</option>--}}
                                                        {{--<option value="YES" {{ ($r->bkash_charge == 'YES') ? 'selected':'' }}>YES</option>--}}
                                                    {{--</select>--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_charge') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        @endif


                                        <div class="form-group mb-0 justify-content-end row">
                                            <div class="col-md-10 col-8">
                                                <button type="submit" class="btn btn-info waves-effect waves-light">Update Settings</button>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div> <!-- end row -->
                        @else
                            <div class="alert alert-danger">Please Create a bKash Account to get payment via bKash</div>
                            <a href="{{ route('account.add') }}" class="btn btn-primary">Add Account</a>
                        @endif
                    </div>

                    <div role="tabpanel" class="tab-pane active" id="rocket-settings">
                        <?php
                                $ac_count = \App\Account::where('resellerId', Auth::user()->resellerId)->where('account_type', 'bKash')->count();
                        ?>
                        @if($ac_count > 0)
                            <div class="row">
                            <div class="col-12">
                                <h4 class="text-center">Rocket Payment Gateway</h4>
                                <div class="card-box table-responsive p-0 p-md-3">
                                    
                                    <form class="form-horizontal" role="form" action="{{ route('config.bkash.api.post') }}" method="POST" enctype="multipart/form-data">
                                        {{ csrf_field() }}

                                        @if( Auth::user()->resellerId == '')
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Username</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_username" value="{{$setting['bkash_username']}}" placeholder="Enter bKash Username">
                                                    <span class="text-danger">{{ $errors->first('bkash_username') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Password</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_password" value="{{$setting['bkash_password']}}" placeholder="Enter bKash Password">
                                                    <span class="text-danger">{{ $errors->first('bkash_password') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Key</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_key" value="{{$setting['bkash_app_key']}}" placeholder="Enter bKash App Key">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_secret" value="{{$setting['bkash_app_secret']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash Script Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_url" value="{{$setting['bkash_checkout_script_url']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Production API Root Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_production_root_url" value="{{$setting['bkash_pr_root_url']}}" placeholder="Enter Production API Root Url">
                                                    <span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">
                                                    <select name="bkash_charge" class="form-control">
                                                        <option value="NO" {{ ($setting['bkash_charge'] == 'NO') ? 'selected':'' }}>NO</option>
                                                        <option value="YES" {{ ($setting['bkash_charge'] == 'YES') ? 'selected':'' }}>YES</option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('bkash_charge') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Username</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_username" value="{{$r->bkash_username}}" placeholder="Enter bKash Username">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_username') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Password</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_password" value="{{$r->bkash_password}}" placeholder="Enter bKash Password">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_password') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_key" value="{{$r->bkash_app_key}}" placeholder="Enter bKash App Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_secret" value="{{ $r->bkash_app_secret }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash Script Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_url" value="{{ $r->bkash_url }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Production API Root Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_production_root_url" value="{{ $r->bkash_production_root_url }}" placeholder="Enter Production API Root Url">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">--}}
                                                    {{--<select name="bkash_charge" class="form-control">--}}
                                                        {{--<option value="NO" {{ ($r->bkash_charge == 'NO') ? 'selected':'' }}>NO</option>--}}
                                                        {{--<option value="YES" {{ ($r->bkash_charge == 'YES') ? 'selected':'' }}>YES</option>--}}
                                                    {{--</select>--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_charge') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        @endif


                                        <div class="form-group mb-0 justify-content-end row">
                                            <div class="col-md-10 col-8">
                                                <button type="submit" class="btn btn-info waves-effect waves-light">Update Settings</button>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div> <!-- end row -->
                        @else
                            <div class="alert alert-danger">Please Create a bKash Account to get payment via bKash</div>
                            <a href="{{ route('account.add') }}" class="btn btn-primary">Add Account</a>
                        @endif
                    </div>

                    <div role="tabpanel" class="tab-pane active" id="ssl-settings">
                        <?php
                                $ac_count = \App\Account::where('resellerId', Auth::user()->resellerId)->where('account_type', 'bKash')->count();
                        ?>
                        @if($ac_count > 0)
                            <div class="row">
                            <div class="col-12">
                                <h4 class="text-center">SSLCOMMERZ Payment Gateway</h4>
                                <div class="card-box table-responsive p-0 p-md-3">
                                    <form class="form-horizontal" role="form" action="{{ route('config.bkash.api.post') }}" method="POST" enctype="multipart/form-data">
                                        {{ csrf_field() }}

                                        @if( Auth::user()->resellerId == '')
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Username</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_username" value="{{$setting['bkash_username']}}" placeholder="Enter bKash Username">
                                                    <span class="text-danger">{{ $errors->first('bkash_username') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Password</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_password" value="{{$setting['bkash_password']}}" placeholder="Enter bKash Password">
                                                    <span class="text-danger">{{ $errors->first('bkash_password') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Key</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_key" value="{{$setting['bkash_app_key']}}" placeholder="Enter bKash App Key">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_app_secret" value="{{$setting['bkash_app_secret']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">bKash Script Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_url" value="{{$setting['bkash_checkout_script_url']}}" placeholder="Enter bKash App Secret">
                                                    <span class="text-danger">{{ $errors->first('bkash_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Production API Root Url</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="bkash_production_root_url" value="{{$setting['bkash_pr_root_url']}}" placeholder="Enter Production API Root Url">
                                                    <span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>
                                                <div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">
                                                    <select name="bkash_charge" class="form-control">
                                                        <option value="NO" {{ ($setting['bkash_charge'] == 'NO') ? 'selected':'' }}>NO</option>
                                                        <option value="YES" {{ ($setting['bkash_charge'] == 'YES') ? 'selected':'' }}>YES</option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('bkash_charge') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Username</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_username') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_username" value="{{$r->bkash_username}}" placeholder="Enter bKash Username">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_username') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">API Password</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_password') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_password" value="{{$r->bkash_password}}" placeholder="Enter bKash Password">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_password') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_key" value="{{$r->bkash_app_key}}" placeholder="Enter bKash App Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash API App Secret</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_app_secret') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_app_secret" value="{{ $r->bkash_app_secret }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_app_secret') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">bKash Script Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_url" value="{{ $r->bkash_url }}" placeholder="Enter bKash App Secret">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Production API Root Url</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_production_root_url') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="bkash_production_root_url" value="{{ $r->bkash_production_root_url }}" placeholder="Enter Production API Root Url">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_production_root_url') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Take bKash Charge</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('bkash_charge') ? 'has-error' : '' }}">--}}
                                                    {{--<select name="bkash_charge" class="form-control">--}}
                                                        {{--<option value="NO" {{ ($r->bkash_charge == 'NO') ? 'selected':'' }}>NO</option>--}}
                                                        {{--<option value="YES" {{ ($r->bkash_charge == 'YES') ? 'selected':'' }}>YES</option>--}}
                                                    {{--</select>--}}
                                                    {{--<span class="text-danger">{{ $errors->first('bkash_charge') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        @endif


                                        <div class="form-group mb-0 justify-content-end row">
                                            <div class="col-md-10 col-8">
                                                <button type="submit" class="btn btn-info waves-effect waves-light">Update Settings</button>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div> <!-- end row -->
                        @else
                            <div class="alert alert-danger">Please Create a bKash Account to get payment via bKash</div>
                            <a href="{{ route('account.add') }}" class="btn btn-primary">Add Account</a>
                        @endif
                    </div>

                    <!-- NAGAD SETTINGS -->
                    {{--<div role="tabpanel" class="tab-pane" id="nagad-settings">--}}
                        {{--<div class="row">--}}
                            {{--<div class="col-12">--}}
                                {{--<div class="card-box table-responsive">--}}
                                    {{--<br><br>--}}
                                    {{--<form class="form-horizontal" role="form" action="{{ route('config.nagad.api.post') }}" method="POST" enctype="multipart/form-data">--}}
                                        {{--{{ csrf_field() }}--}}

                                        {{--@if( Auth::user()->roleId != 4)--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Merchant ID</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_merchant_id') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_merchant_id" value="{{$setting['nagad_merchant_id']}}" placeholder="Enter Nagad Merchant ID">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_merchant_id') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Merchant Number</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_merchant_number') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_merchant_number" value="{{$setting['nagad_merchant_number']}}" placeholder="Enter Nagad Merchant Number">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_merchant_number') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">PG Public Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_pg_public_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_pg_public_key" value="{{$setting['nagad_pg_public_key']}}" placeholder="Enter Nagad PG Public Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_pg_public_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Merchant Private Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_merchant_private_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_merchant_private_key" value="{{$setting['nagad_merchant_private_key']}}" placeholder="Enter Nagad Private Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_merchant_private_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Take Nagad Charge</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_charge') ? 'has-error' : '' }}">--}}
                                                    {{--<select name="nagad_charge" class="form-control">--}}
                                                        {{--<option value="NO" {{ ($setting['nagad_charge'] == 'NO') ? 'selected':'' }}>NO</option>--}}
                                                        {{--<option value="YES" {{ ($setting['nagad_charge'] == 'YES') ? 'selected':'' }}>YES</option>--}}
                                                    {{--</select>--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_charge') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        {{--@else--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Merchant ID</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_merchant_id') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_merchant_id" value="{{$r->nagad_merchant_id}}" placeholder="Enter Nagad Merchant ID">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_merchant_id') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Merchant Number</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_merchant_number') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_merchant_number" value="{{$r->nagad_merchant_number}}" placeholder="Enter Nagad Merchant Number">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_merchant_number') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">PG Public Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_pg_public_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_pg_public_key" value="{{$r->nagad_pg_public_key}}" placeholder="Enter Nagad PG Public Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_pg_public_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Merchant Private Key</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_merchant_private_key') ? 'has-error' : '' }}">--}}
                                                    {{--<input type="text" class="form-control" name="nagad_merchant_private_key" value="{{$r->nagad_merchant_private_key}}" placeholder="Enter Nagad Private Key">--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_merchant_private_key') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                            {{--<div class="form-group row">--}}
                                                {{--<label class="col-md-2 col-4 col-form-label">Take Nagad Charge</label>--}}
                                                {{--<div class="col-md-10 col-8 {{ $errors->has('nagad_charge') ? 'has-error' : '' }}">--}}
                                                    {{--<select name="nagad_charge" class="form-control">--}}
                                                        {{--<option value="NO" {{ ($r->nagad_charge == 'NO') ? 'selected':'' }}>NO</option>--}}
                                                        {{--<option value="YES" {{ ($r->nagad_charge == 'YES') ? 'selected':'' }}>YES</option>--}}
                                                    {{--</select>--}}
                                                    {{--<span class="text-danger">{{ $errors->first('nagad_charge') }}</span>--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        {{--@endif--}}


                                        {{--<div class="form-group mb-0 justify-content-end row">--}}
                                            {{--<div class="col-md-10 col-8">--}}
                                                {{--<button type="submit" class="btn btn-info waves-effect waves-light">Update Settings</button>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}

                                    {{--</form>--}}

                                {{--</div>--}}
                            {{--</div>--}}
                        {{--</div> <!-- end row -->--}}
                    {{--</div>--}}
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

@endsection
