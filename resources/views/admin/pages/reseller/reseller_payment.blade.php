@extends ('admin.layouts.master')
@section('title')
    Reseller Recharge Balance
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">

                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" name="invoice" role="form" onsubmit="submitBtn.disabled = true; return true;"
                                  action="{{ route('reseller.payment.post',$reseller->resellerId) }}" method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Reseller Name</label>
                                    <div class="col-10 {{ $errors->has('reseller_name') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" readonly name="reseller_name"
                                               value="{{ $reseller->resellerName }}">
                                        <span class="text-danger">{{ $errors->first('reseller_name') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Recharge Amount</label>
                                    <div class="col-10 {{ $errors->has('recharge_amount') ? 'has-error' : '' }}">
                                        <input class="form-control" type="text" name="recharge_amount" placeholder="0.00">
                                        <span class="text-danger">{{ $errors->first('recharge_amount') }}</span>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Payment Method</label>
                                    <div class="col-10 {{ $errors->has('account_id') ? 'has-error' : '' }}">
                                        <select class="form-control" name="account_id" id="account_id" required>
                                            <option value="">Select Account Name</option>
                                            @foreach($accounts as $account)
                                                <option value="{{$account['id']}}">{{$account['account_name']}}</option>
                                            @endforeach
                                            <span class="text-danger">{{ $errors->first('account_id') }}</span>
                                        </select>
                                    </div>

                                </div>

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" name="submitBtn" class="btn btn-info waves-effect waves-light">
                                            Payment Confirm
                                        </button>
                                        <a href="{{ route('reseller.index') }}" class="btn btn-secondary">Cancel</a>
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
@endsection
