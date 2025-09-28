@extends ('admin.layouts.master')
@section('title')
    Create Reseller Invoice
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">


                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" name="invoice" role="form" onsubmit="submitBtn.disabled = true; return true;"
                                  action="{{ route('invoice.seller.add.post') }}" method="POST">

                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Reseller Name</label>
                                    <div class="col-10 {{ $errors->has('reseller_id') ? 'has-error' : '' }}">
                                        <select class="form-control select2" name="reseller_id" id="reseller">
                                            <option value="">Select Reseller Name</option>
                                            @foreach($resellers as $reseller)
                                                <option value="{{$reseller['resellerId']}}">{{ $reseller['resellerName'] }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('client_id') }}</span>
                                    </div>
                                </div>

                                {{--<div class="form-group row">--}}
                                    {{--<label class="col-2 col-form-label">Bill Month</label>--}}
                                    {{--<div class="col-6 {{ $errors->has('bill_month') ? 'has-error' : '' }}">--}}
                                        {{--<select id="month" name="bill_month" class="form-control">--}}
                                            {{--<option value='1'>January</option>--}}
                                            {{--<option value='2'>February</option>--}}
                                            {{--<option value='3'>March</option>--}}
                                            {{--<option value='4'>April</option>--}}
                                            {{--<option value='5'>May</option>--}}
                                            {{--<option value='6'>June</option>--}}
                                            {{--<option value='7'>July</option>--}}
                                            {{--<option value='8'>August</option>--}}
                                            {{--<option value='9'>September</option>--}}
                                            {{--<option value='10'>October</option>--}}
                                            {{--<option value='11'>November</option>--}}
                                            {{--<option value='12'>December</option>--}}
                                        {{--</select>--}}
                                        {{--<span class="text-danger">{{ $errors->first('bill_month') }}</span>--}}
                                    {{--</div>--}}
                                    {{--<div class="col-4 {{ $errors->has('bill_year') ? 'has-error' : '' }}">--}}
                                        {{--<select name="bill_year" id="year" class="form-control"></select>--}}
                                        {{--<span class="text-danger">{{ $errors->first('bill_year') }}</span>--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                <input type="hidden" id="month" name="bill_month" value="{{ date('m') }}">
                                <input type="hidden" name="bill_year" id="year" value="{{ date('Y') }}">

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Credit Limit</label>
                                    <div class="col-10 {{ $errors->has('credit_limit') ? 'has-error' : '' }}" id="credit">
                                        <input class="form-control" type="text" name="credit_limit" readonly value="">
                                        <span class="text-danger">{{ $errors->first('credit_limit') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Security Amount</label>
                                    <div class="col-10 {{ $errors->has('amount') ? 'has-error' : '' }}" id="amount_id">
                                        <input class="form-control" type="text" name="amount" value="" id="amount" onchange="reseller_total_amount()">
                                        <span class="text-danger">{{ $errors->first('amount') }}</span>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Discount</label>
                                    <div class="col-10 {{ $errors->has('discount') ? 'has-error' : '' }}" id="discount_id">
                                        <input class="form-control" id="discount" onchange="reseller_total_amount()" type="text" name="discount" value="0.00">
                                        <span class="text-danger">{{ $errors->first('discount') }}</span>
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Sub Total</label>
                                    <div class="col-10 {{ $errors->has('sub_total') ? 'has-error' : '' }}" id="s_total">
                                        <input class="form-control" readonly id="sub_total" type="text" name="sub_total" value="0.00">
                                        <span class="text-danger">{{ $errors->first('sub_total') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Paid</label>
                                    <div class="col-10 {{ $errors->has('paid') ? 'has-error' : '' }}">
                                        <input class="form-control" type="text" id="paid_id" name="paid" value="0.00">
                                        <span class="text-danger">{{ $errors->first('paid') }}</span>
                                    </div>

                                </div>

                                <div id="payment" style="display: none">

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


                                </div>


                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" name="submitBtn" class="btn btn-info waves-effect waves-light">Create
                                            Invoice
                                        </button>
                                        <a href="{{ route('invoice.seller') }}" class="btn btn-secondary">Cancel</a>
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
    <script>
        $('#paid_id').keyup(function () {
            if ($(this).val() > 0) {
                $('#payment').show();
            } else {
                $('#payment').hide();
            }
        });

        $(document).ready(function () {
            // Select2
            $(".select2").select2();

        });

        window.onload = init;

        function init() {
            document.invoice.month.options[new Date().getMonth()].selected = true;
        }


        var time = new Date();
        var year = time.getFullYear();
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