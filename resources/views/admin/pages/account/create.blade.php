@extends ('admin.layouts.master')
@section('title')
    Add New Account
@endsection

@section('content')
        <div class="row">
            <div class="col-8">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('account.add.post') }}" method="POST">
                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Account Name</label>
                                        <div class="col-9 {{ $errors->has('account_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="account_name" value="" placeholder="Enter Account Name">
                                            <span class="text-danger">{{ $errors->first('account_name') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Account Type</label>
                                        <div class="col-9 {{ $errors->has('account_type') ? 'has-error' : '' }}">

                                            <select class="form-control" name="account_type" required>
                                                <option value="Cash">Cash Account</option>
                                                <option value="Bank">Bank Account</option>
                                                <option value="bKash">bKash</option>
                                                <option value="Rocket">Rocket</option>
                                                <option value="Nagad">Nagad</option>
                                                <option value="Others">Others</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('account_type') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Account Number</label>
                                        <div class="col-9 {{ $errors->has('account_number') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="account_number" value="" placeholder="Enter Account Number">
                                            <span class="text-danger">{{ $errors->first('account_number') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Account Balance</label>
                                        <div class="col-9 {{ $errors->has('account_balance') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="account_balance"
                                                   value="0" placeholder="Enter Account Balance" required>
                                            <span class="text-danger">{{ $errors->first('account_balance') }}</span>
                                        </div>
                                    </div>
                                    @if(Auth::user()->branchId != '' && Auth::user()->hasRole('Reseller'))
                                        {{--<div class="form-group row">--}}
                                            {{--<label class="col-2 col-form-label">Branch Name</label>--}}
                                            {{--<div class="col-10 {{ $errors->has('branchId') ? 'has-error' : '' }}">--}}
                                                {{--<select class="form-control" name="branchId">--}}
                                                    {{--<option value="">Select Branch</option>--}}
                                                    {{--@foreach($branchs as $branch)--}}
                                                        {{--<option value="{{$branch['branchId']}}">{{$branch['branchName']}}</option>--}}
                                                    {{--@endforeach--}}
                                                {{--</select>--}}
                                                {{--<span class="text-danger">{{ $errors->first('branchId') }}</span>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    @else
                                        <input type="hidden" name="branchId" value="{{ Auth::user()->branchId }}">
                                    @endif

                                    @role('Reseller')
                                        <input type="hidden" name="resellerId" value="{{ Auth::user()->resellerId }}">
                                    @endrole

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-9">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Account</button>
                                            <a href="{{ route('account.index') }}" class="btn btn-secondary">Cancel</a>
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
