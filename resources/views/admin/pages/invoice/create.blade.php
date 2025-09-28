@extends ('admin.layouts.master')
@section('title', 'Create Invoice')

@section('content')
    <style>
        /* .mlFix {
                margin-left: -44px;
            }

            @media screen and (max-width: 768px) {
                .mlFix {
                    margin-left: 0;
                }
            } */
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card-box">


                <div class="row">
                    <div class="col-12">
                        <div class="p-2">
                            <form class="form-horizontal" name="invoice" role="form"
                                  onsubmit="submitBtn.disabled = true; return true;"
                                  action="{{ route('invoice.add.post') }}" method="POST">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-12 col-md-3 col-form-label">Client Name</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('client_id') ? 'has-error' : '' }}">
                                                <select class="form-control select2" name="client_id" id="client">
                                                    <option value="">Select Client Name</option>
                                                    @foreach ($clients as $client)
                                                        <option value="{{ $client['id'] }}">{{ $client['client_name'] . '-' . $client['username'] }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger">{{ $errors->first('client_id') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-12 col-md-3 col-form-label">Bandwidth</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('bandwidth') ? 'has-error' : '' }}"
                                                 id="bandwidth">
                                                <input class="form-control" type="text" name="bandwidth" readonly
                                                       value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-12 col-md-3 col-form-label">Bill Month</label>
                                            <div class="col-12 col-md-5 {{ $errors->has('bill_month') ? 'has-error' : '' }}">
                                                <select id="month" name="bill_month" class="form-control">
                                                    <option value='1'>January</option>
                                                    <option value='2'>February</option>
                                                    <option value='3'>March</option>
                                                    <option value='4'>April</option>
                                                    <option value='5'>May</option>
                                                    <option value='6'>June</option>
                                                    <option value='7'>July</option>
                                                    <option value='8'>August</option>
                                                    <option value='9'>September</option>
                                                    <option value='10'>October</option>
                                                    <option value='11'>November</option>
                                                    <option value='12'>December</option>
                                                </select>
                                                <span class="text-danger">{{ $errors->first('bill_month') }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="{{ $errors->has('bill_year') ? 'has-error' : '' }}">
                                                    <select name="bill_year" id="year" class="form-control"></select>
                                                    <span class="text-danger">{{ $errors->first('bill_year') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-12 col-md-3 col-form-label">Client Plan Price</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('plan_price') ? 'has-error' : '' }}"
                                                 id="plan_price">
                                                <input class="mlFix form-control" id="p_price" onload="total_amount()"
                                                       onchange="total_amount()" readonly type="text" name="plan_price"
                                                       value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $isSuperAdmin = empty(Auth::user()->resellerId); // true if resellerId null/empty
                                @endphp

                                <div class="row">
                                    <div class="col-md-6">
                                        @if ($isSuperAdmin || $reseller_has_extra_charge == 1)
                                            <div class="form-group row">
                                                <label class="col-12 col-md-3 col-form-label">Service Charge</label>
                                                <div class="col-12 col-md-9 {{ $errors->has('service_charge') ? 'has-error' : '' }}"
                                                     id="service_charge">
                                                    <input class="form-control" id="s_charge" onkeyup="total_amount()"
                                                           onkeydown="total_amount()" type="text" name="service_charge"
                                                           value="0.00">
                                                    <span class="text-danger">{{ $errors->first('service_charge') }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <input type="hidden" name="service_charge" value="0.00">
                                        @endif
                                        <div class="form-group row" id="otc_fee_block" style="display: none;">
                                            <label class="col-12 col-md-3 col-form-label">OTC Fee</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('otc_charge') ? 'has-error' : '' }}"
                                                 id="otc_charge">
                                                <input class="form-control" id="o_charge" onkeyup="total_amount()"
                                                       onkeydown="total_amount()" type="text" name="otc_charge"
                                                       value="0.00">

                                                <span class="text-danger">{{ $errors->first('otc_charge') }}</span>
                                            </div>
                                        </div>

                                        @if (Auth::user()->resellerId != '')
                                            <div class="form-group row pt-3 pt-md-0">
                                                <label class="col-12 col-md-3 col-form-label">Buy Plan Price</label>
                                                <div class="col-12 col-md-9 {{ $errors->has('buy_plan_price') ? 'has-error' : '' }}"
                                                     id="buy_plan_price">
                                                    <input class="form-control" readonly type="text"
                                                           name="buy_plan_price" value="">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group pt-3 pt-md-0 row">
                                            <label class="col-12 col-md-3 col-form-label">Total</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('total') ? 'has-error' : '' }}"
                                                 id="total">
                                                <input class="form-control" readonly id="total_price"
                                                       onkeyup="total_amount()" onkeydown="total_amount()" type="text"
                                                       name="total" value="0.00">
                                                <span class="text-danger">{{ $errors->first('total') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 d-none">
                                        <div class="form-group row">
                                            @role('Super-Admin')
                                            <label class="col-12 col-md-3 col-form-label">Discount</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('discount') ? 'has-error' : '' }}"
                                                 id="discount_id">
                                                <input class="form-control" id="discount" onkeyup="total_amount()"
                                                       onkeydown="total_amount()" type="text" name="discount"
                                                       value="0.00">
                                                <span class="text-danger">{{ $errors->first('discount') }}</span>
                                            </div>
                                            @else
                                                <input class="form-control" id="discount" type="hidden" name="discount"
                                                       value="0.00">
                                                @endrole
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-12 col-md-3 col-form-label">Sub Total</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('sub_total') ? 'has-error' : '' }}"
                                                 id="s_total">
                                                <input class="form-control" readonly id="sub_total"
                                                       onkeyup="total_amount()" onkeydown="total_amount()" type="text"
                                                       name="sub_total" value="0.00">
                                                <span class="text-danger">{{ $errors->first('sub_total') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-12 col-md-3 col-form-label">Note</label>
                                            <div class="col-12 col-md-9 {{ $errors->has('charge_for') ? 'has-error' : '' }}">
                                                <input class="form-control" id="charge_for" type="text"
                                                       name="charge_for">
                                                <span class="text-danger">{{ $errors->first('charge_for') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-3 mb-0 row">
                                    <div class="col-12 text-center">
                                        <button type="submit" name="submitBtn"
                                                class="btn btn-info waves-effect waves-light my-1">Create Invoice
                                        </button>
                                        <a href="{{ route('invoice.index') }}" class="btn btn-secondary my-1">Cancel</a>
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
        $(document).ready(function () {
            $('#client').on('change', function () {
                let clientId = $(this).val();
                if (clientId) {
                    $.ajax({
                        url: '{{ route("check.client.payment") }}',
                        method: 'GET',
                        data: { client_id: clientId },
                        success: function (response) {
                            if (response.has_payment) {
                                $('#otc_fee_block').hide();
                            } else {
                                $('#otc_fee_block').show();
                            }
                        }
                    });
                } else {
                    $('#otc_fee_block').hide();
                }
            });
        });
    </script>


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
