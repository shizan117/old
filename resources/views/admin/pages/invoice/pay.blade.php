@extends ('admin.layouts.master')
@section('title')
    Pay Invoice
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">

                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" name="invoice" role="form" onsubmit="submitBtn.disabled = true; return true;"
                                  action="{{ route('invoice.pay.post', $invoice->id) }}" method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Client Name</label>
                                    <div class="col-5 {{ $errors->has('client_id') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" readonly name="client_name"
                                               value="{{ $invoice->client->client_name }} ({{$invoice->client->username}})">

                                        <span class="text-danger">{{ $errors->first('client_name') }}</span>
                                    </div>
                                    <label class="col-2 col-form-label">Payment Date</label>
                                    <div class="col-3">
                                        <div class="input-group">
                                            <input type="text" autocomplete="off" name="payment_date" value="{{ old('payment_date')??date("Y-m-d") }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Bill Month</label>
                                    <div class="col-6 {{ $errors->has('bill_month') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" readonly name="bill_month"
                                               value="{{ date('F', mktime(0, 0, 0, $invoice->bill_month, 1)) }}">
                                        <span class="text-danger">{{ $errors->first('bill_month') }}</span>
                                    </div>
                                    <div class="col-4 {{ $errors->has('bill_year') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" readonly name="bill_year"
                                               value="{{ $invoice->bill_year }}">
                                        <span class="text-danger">{{ $errors->first('bill_year') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Bandwidth</label>
                                    <div class="col-10 {{ $errors->has('bandwidth') ? 'has-error' : '' }}"
                                         id="bandwidth">
                                        <input class="form-control" type="text" name="bandwidth" readonly
                                               value="{{ $invoice->bandwidth }}">
                                    </div>
                                </div>


                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Due Amount</label>
                                        <div class="col-10 {{ $errors->has('due') ? 'has-error' : '' }}" id="due">
                                            <input class="form-control" readonly type="text" name="due"
                                                   value="{{ $invoice->due }}">
                                        </div>
                                    </div>


                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Paid Amount</label>
                                    <div class="col-10 {{ $errors->has('paid_amount') ? 'has-error' : '' }}"
                                         id="reseller_paid_amount">
                                        <input class="form-control" type="text" id="paid_id" name="paid_amount"
                                               value="0.00" autofocus>
                                        <span class="text-danger">{{ $errors->first('paid_amount') }}</span>
                                    </div>

                                </div>

                                <div class="form-group row">

                                    <label class="col-2 col-form-label">Pay From Advance</label>
                                    <div class="col-6 {{ $errors->has('pay_from_advance') ? 'has-error' : '' }}">
                                        <input class="form-control" type="text" name="pay_from_advance" value="0.00">
                                        <span class="text-danger">{{ $errors->first('pay_from_advance') }}</span>
                                    </div>
                                    <div class="col-4 col-form-label">

                                            Client's Balance {{ $invoice->client->balance }}
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Payment Method</label>
                                    <div class="col-10 {{ $errors->has('paid_to') ? 'has-error' : '' }}">
                                        <select class="form-control" name="paid_to" id="paid_to">
                                            <option value="">Select Account Name</option>
                                            @foreach($accounts as $account)
                                                <option value="{{$account['id']}}">{{$account['account_name']}}</option>
                                            @endforeach
                                            <span class="text-danger">{{ $errors->first('paid_to') }}</span>
                                        </select>
                                    </div>

                                </div>

                                <div class="form-group row">

                                    <label class="col-2 col-form-label">Charge</label>
                                    <div class="col-10 {{ $errors->has('charge') ? 'has-error' : '' }}">
                                        <input class="form-control" type="text" name="charge" value="0.00">
                                        <span class="text-danger">{{ $errors->first('charge') }}</span>
                                    </div>
                                </div>

                                {{--@if(Auth::user()->resellerId == '')--}}
                                    {{--<div class="form-group row">--}}
                                        {{--<label class="col-2 col-form-label">Next Expire Date</label>--}}

                                        {{--<div class="input-group col-10 {{ $errors->has('exp_date') ? 'has-error' : '' }}">--}}
                                            {{--<input type="text" autocomplete="off" name="exp_date" class="form-control datepicker"--}}
                                                   {{--placeholder="yyyy-mm-dd"--}}
                                                   {{--value="">--}}

                                            {{--<div class="input-group-append">--}}
                                                {{--<span class="input-group-text"><i class="ti-calendar"></i></span>--}}
                                            {{--</div>--}}
                                            {{--<span class="text-danger">{{ $errors->first('exp_date') }}</span>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--@endif--}}

                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" class="btn btn-info waves-effect waves-light" name="submitBtn">Pay
                                            Invoice
                                        </button>
                                        <a href="{{ route('invoice.due') }}" class="btn btn-secondary">Cancel</a>
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

    @include('admin.layouts.print-js')

    <script>
        $(document).ready(function () {
            $(".datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            });
        });
    </script>
    <script>
        window.onload = init;

        function init() {
            document.invoice.month.options[{{ $invoice->bill_month }}-1].selected = true;
        }


        // var time = new Date();
        var year = "{{ $invoice->bill_year }}";
        var old = year - 1;
        var options = "";

        for (var y = old; y < year; y++) {
            options += "<option>" + y + "</option>";
        }
        options += "<option selected>" + year + "</option>";
        for (var x = year + 1; x <= year + 8; x++) {
            options += "<option>" + x + "</option>";
        }

        document.getElementById("year").innerHTML = options;

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
