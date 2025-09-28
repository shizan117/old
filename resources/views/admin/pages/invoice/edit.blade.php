@extends ('admin.layouts.master')
@section('title')
    Edit Invoice
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" name="invoice" role="form" action="{{ route('invoice.edit.post', $invoice->id) }}" method="POST">

                                    {{ csrf_field() }}
                                    <input type="hidden" id="client_id_no" name="client_id_no" value="{{ $invoice->client_id }}">

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Client Name</label>
                                        <div class="col-10 {{ $errors->has('client_id') ? 'has-error' : '' }}">
                                            <input class="form-control" readonly type="text" name="client_id" value="{{ $invoice->client->client_name.' ('.$invoice->client->username.')' }}">
                                            <span class="text-danger">{{ $errors->first('client_id') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Bill Month</label>
                                        <div class="col-6 {{ $errors->has('bill_month') ? 'has-error' : '' }}">
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
                                        <div class="col-4 {{ $errors->has('bill_year') ? 'has-error' : '' }}">
                                            <select name="bill_year" id="year" class="form-control"></select>
                                            <span class="text-danger">{{ $errors->first('bill_year') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Bandwidth</label>
                                        <div class="col-10 {{ $errors->has('bandwidth') ? 'has-error' : '' }}" id="bandwidth" >
                                            <input class="form-control" type="text" name="bandwidth" readonly value="{{ $invoice->bandwidth }}">
                                        </div>
                                    </div>

                                    @if(Auth::user()->resellerId != "")

                                        <div class="form-group row">
                                            <label class="col-2 col-form-label">Buy Plan Price</label>
                                            <div class="col-10 {{ $errors->has('buy_plan_price') ? 'has-error' : '' }}" id="buy_plan_price" >
                                                <input class="form-control" readonly type="text" name="buy_plan_price" value="{{ $invoice->plan_price }}">
                                            </div>
                                        </div>
                                    @endif
                                        <div class="form-group row">
                                            <label class="col-2 col-form-label">Client Plan Price</label>
                                            <div class="col-10 {{ $errors->has('plan_price') ? 'has-error' : '' }}" id="plan_price" >
                                                <input class="form-control" readonly type="text" id="p_price" name="plan_price" value="{{ $invoice->plan_price }}">
                                            </div>
                                        </div>
                                    @php
                                        $isSuperAdmin = empty(Auth::user()->resellerId); // true if resellerId null/empty
                                    @endphp

                                    @if ($isSuperAdmin || $reseller_has_extra_charge == 1)

                                        <div class="form-group row">
                                            <label class="col-2 col-form-label">Service Charge</label>
                                            <div class="col-4 {{ $errors->has('service_charge') ? 'has-error' : '' }}" id="service_charge" >
                                                <input class="form-control" id="s_charge" onkeyup="total_amount()" onkeydown="total_amount()" type="text" name="service_charge" value="{{ $invoice->service_charge }}">
                                                <span class="text-danger">{{ $errors->first('service_charge') }}</span>
                                            </div>


                                            <label class="col-2 col-form-label">Note</label>
                                            <div class="col-4 {{ $errors->has('charge_for') ? 'has-error' : '' }}">
                                                <input class="form-control" id="charge_for" type="text" name="charge_for" value="{{ $invoice->charge_for }}">
                                                <span class="text-danger">{{ $errors->first('charge_for') }}</span>
                                            </div>

                                        </div>

                                    @else
                                        <input type="hidden" name="service_charge" value="{{ $invoice->service_charge }}">
                                        <input type="hidden" name="charge_for" value="{{ $invoice->charge_for }}">

                                    @endif
                                    <div class="form-group row" id="otc_fee_block" style="display: none;">
                                        <label class="col-2 col-form-label">OTC Fee</label>
                                        <div class="col-10 {{ $errors->has('otc_charge') ? 'has-error' : '' }}"
                                             id="otc_charge">
                                            <input class="form-control" id="o_charge" onkeyup="total_amount()"
                                                   onkeydown="total_amount()" type="text" name="otc_charge"
                                                   value="{{ $invoice->otc_charge }}">

                                            <span class="text-danger">{{ $errors->first('otc_charge') }}</span>
                                        </div>
                                    </div>
                                        <div class="form-group row">
                                            <label class="col-2 col-form-label">Total</label>
                                            <div class="col-10 {{ $errors->has('total') ? 'has-error' : '' }}" id="total" >
                                                <input class="form-control" readonly id="total_price" onkeyup="total_amount()" onkeydown="total_amount()" type="text" name="total" value="{{ $invoice->total }}">
                                                <span class="text-danger">{{ $errors->first('total') }}</span>
                                            </div>
                                        </div>

                                        {{--@role('Super-Admin')--}}


                                @if ($isSuperAdmin || $reseller_has_extra_charge == 1)
                                        <div class="form-group row">
                                            <label class="col-2 col-form-label">Discount</label>
                                            <div class="col-10 {{ $errors->has('discount') ? 'has-error' : '' }}" >
                                                <input class="form-control" id="discount" onkeyup="total_amount()" onkeydown="total_amount()" type="text" name="discount" value="{{ $invoice->discount }}">
                                                <span class="text-danger">{{ $errors->first('discount') }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <input type="hidden" name="discount" value="{{ $invoice->discount }}">
                                    @endif
                                        {{--@else--}}
                                            {{--<input class="form-control" id="discount" type="hidden" name="discount" value="{{ $invoice->discount }}">--}}
                                        {{--@endrole--}}

                                        <div class="form-group row">
                                            <label class="col-2 col-form-label">Sub Total</label>
                                            <div class="col-10 {{ $errors->has('sub_total') ? 'has-error' : '' }}">
                                                <input class="form-control" readonly id="sub_total" onkeyup="total_amount()" onkeydown="total_amount()" type="text" name="sub_total" value="{{ $invoice->all_total }}">
                                                <span class="text-danger">{{ $errors->first('sub_total') }}</span>
                                            </div>

                                        </div>


                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Update Invoice</button>
                                            <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Cancel</a>
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
            let clientId = $('#client_id_no').val(); // value is actually client_id from DB

            if (clientId) {
                $.ajax({
                    url: '{{ route("check.client.payment") }}',
                    method: 'GET',
                    data: { client_id: clientId }, // ✅ send key as client_id
                    success: function (response) {
                        if (response.has_payment) {
                            $('#otc_fee_block').hide();
                        } else {
                            $('#otc_fee_block').show();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX error:', error);
                        $('#otc_fee_block').hide();
                    }
                });
            } else {
                $('#otc_fee_block').hide();
            }
        });
    </script>



    <script>
        $('#paid_id').keyup(function()
        {
            if( $(this).val() > 0 ) {
                $('#pay_to').show();
                $('#charge').show();
            } else {
                $('#pay_to').hide();
                $('#charge').hide();
            }
        });

        $(document).ready(function() {
            // Select2
            $(".select2").select2();

        });


        window.onload = init;

        function init() {
            document.invoice.month.options[{{ $invoice->bill_month }}-1].selected = true;
        }


        var year = "{{ $invoice->bill_year }}";
        var old = year - 1;
        var now = old +1;
        var options = "";

        for (var y = old; y < year; y++) {
            options += "<option>" + y + "</option>";
        }
        options += "<option selected>" + now + "</option>";
        for (var x = now + 1; x <= now+8; x++) {
            options += "<option>" + x + "</option>";
        }

        document.getElementById("year").innerHTML = options;

    </script>
    @include('admin.layouts.custom-js')
@endsection
