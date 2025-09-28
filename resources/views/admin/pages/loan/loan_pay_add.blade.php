@extends ('admin.layouts.master')
@section('title')
    Add Loan Payment
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">


                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" role="form" action="{{ route('loan.pay.add.post') }}"
                                  method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Loan From Account</label>
                                    <div class="col-10 {{ $errors->has('account') ? 'has-error' : '' }}">
                                        <select class="form-control" name="account">
                                            <option value="">Select Account</option>
                                            @foreach($accounts as $account)
                                                <option value="{{$account['id']}}" {{ (collect(old('account'))->contains($account['id'])) ? 'selected':'' }}>{{$account['account_name']}}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('account') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Loan Payer</label>
                                    <div class="col-10 {{ $errors->has('loanPayer') ? 'has-error' : '' }}">
                                        <select class="form-control" name="loanPayer">
                                            <option value="">Select Investor</option>
                                            @foreach($loanPayers as $loanPayer)
                                                <option value="{{$loanPayer['id']}}" {{ (collect(old('loanPayer'))->contains($loanPayer['id'])) ? 'selected':'' }}>{{$loanPayer['name']}}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('loanPayer') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Pay Amount</label>
                                    <div class="col-10 {{ $errors->has('pay_amount') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="pay_amount" value=""
                                               placeholder="Enter Payable Amount">
                                        <span class="text-danger">{{ $errors->first('pay_amount') }}</span>
                                    </div>
                                </div>

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" class="btn btn-info waves-effect waves-light">Add
                                            Loan
                                        </button>
                                        <a href="{{ route('loan.pay.list') }}" class="btn btn-secondary">Cancel</a>
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