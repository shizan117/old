@extends ('admin.layouts.master')
@section('title')
    Add Invest
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">


                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" role="form" action="{{ route('invest.add.post') }}"
                                  method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Invest To Account</label>
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
                                    <label class="col-2 col-form-label">Investor</label>
                                    <div class="col-10 {{ $errors->has('investor') ? 'has-error' : '' }}">
                                        <select class="form-control" name="investor">
                                            <option value="">Select Investor</option>
                                            @foreach($investors as $investor)
                                                <option value="{{$investor['id']}}" {{ (collect(old('investor'))->contains($investor['id'])) ? 'selected':'' }}>{{$investor['name']}}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('investor') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Invest Amount</label>
                                    <div class="col-10 {{ $errors->has('amount') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="amount" value=""
                                               placeholder="Enter Invest Amount">
                                        <span class="text-danger">{{ $errors->first('amount') }}</span>
                                    </div>
                                </div>

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" class="btn btn-info waves-effect waves-light">Add
                                            Invest
                                        </button>
                                        <a href="{{ route('invest.list') }}" class="btn btn-secondary">Cancel</a>
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