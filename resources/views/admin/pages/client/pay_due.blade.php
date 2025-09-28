<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
    Pay Client Due
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <div class="card-box">
                <h3 class="text-center">Payment Collection</h3>

                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" role="form" onsubmit="submitBtn.disabled = true; return true;"
                                  action="{{ route('client.pay.due.post', $client->id) }}" method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-md-4 col-12 col-form-label">Client Name</label>
                                    <div class="col-md-8 col-12 {{ $errors->has('client_id') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" readonly name="client_name"
                                               value="{{ $client->client_name }} ({{$client->username}})">

                                        <span class="text-danger">{{ $errors->first('client_name') }}</span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-md-4 col-form-label">Payment Date</label>
                                    <div class="col-12 col-md-8">
                                        <div class="input-group">
                                            <input type="text" autocomplete="off" name="payment_date" value="{{ old('payment_date')??date("Y-m-d") }}"
                                                   data-date-end-date="0d" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-12 col-md-4 col-form-label">Due Amount</label>
                                    <div class="col-12 col-md-8{{ $errors->has('due') ? 'has-error' : '' }}" id="due">
                                        <input class="form-control" readonly type="text" name="due"
                                               value="{{ $client->due }}">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-12 col-md-4 col-form-label">Paid Amount *</label>
                                    <div class="col-12 col-md-8 {{ $errors->has('paid_amount') ? 'has-error' : '' }}"
                                         id="reseller_paid_amount">
                                        <input class="form-control border-warning" type="text" id="paid_id" name="paid_amount"
                                               value="{{ old('paid_amount')??$client->due }}" autofocus onFocus="this.select()">
                                        <span class="text-danger">{{ $errors->first('paid_amount') }}</span>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-12 col-md-4 col-form-label">Payment Method *</label>
                                    <div class="col-12 col-md-8 {{ $errors->has('paid_to') ? 'has-error' : '' }}">
                                        <select class="form-control" name="paid_to" id="paid_to" required>
                                            <option value="">Select Account Name</option>
                                            @foreach($accounts as $account)
                                            <option value="{{$account['id']}}" {{ $account['account_name'] == "Cash" ? 'selected' : '' }}>{{$account['account_name']}}</option>
                                            @endforeach
                                            <span class="text-danger">{{ $errors->first('paid_to') }}</span>
                                        </select>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    @if($user->hasRole('Super-Admin') || $user->can('client_increase expire date'))
                                    <label class="col-12 col-md-4 col-form-label" title="**if empty current month">
                                        Custom Expire Date
                                    </label>

                                    <div class="input-group col-12 col-md-8 {{ $errors->has('exp_date') ? 'has-error' : '' }}">
                                        <input type="text" autocomplete="off" name="exp_date" class="form-control datepicker"
                                               placeholder="** keep it blank to setup automatically" value=''>

                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                        <span class="text-danger">{{ $errors->first('exp_date') }}</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-12 col-md-8">
                                        <button type="submit" class="btn btn-info waves-effect waves-light"
                                                name="submitBtn" onclick="return confirm('Confirm payment collection?')">
                                            Collect Payment
                                        </button>
                                        <a href="{{ route('client.view', $client->id) }}" class="btn btn-secondary">Cancel</a>
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
    <script type="text/javascript">
        $(document).ready(function () {
            $(".datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            });
        });
    </script>
    @include('admin.layouts.custom-js')
@endsection
@section('required_css')
    <link href='{{ asset("assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css") }}'
          rel="stylesheet" type="text/css"/>
@endsection
@section('required_js')
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
@endsection
