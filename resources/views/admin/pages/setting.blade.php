@extends ('admin.layouts.master')
@section('title','Settings')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <h4 class="m-t-0 header-title">Settings</h4>
               <div class="d-none d-md-block">
                <br><br>
               </div>
                <form class="form-horizontal" role="form" action="{{ route('config.store') }}" method="POST"
                      enctype="multipart/form-data">
                    {{ csrf_field() }}

                    @if( Auth::user()->resellerId == '')
                        <div class="form-group row">
                            <div class="col-md-6 {{ $errors->has('companyName') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Company Name *</label>
                                <input type="text" class="form-control" name="companyName"
                                       value="{{$setting['companyName']}}" placeholder="Enter Company Name" required>
                                <span class="text-danger">{{ $errors->first('companyName') }}</span>
                            </div>
                            <div class="col-md-6 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Phone No *</label>
                                <input type="text" class="form-control" name="phone" value="{{ $setting['phone'] }}"
                                       placeholder="Enter Phone No" required>
                                <span class="text-danger">{{ $errors->first('phone') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <div class="col-12 {{ $errors->has('logo') ? 'has-error' : '' }}">
                                    <label class="mt-2 mt-md-0">Company Logo</label>
                                    @if(file_exists("assets/images/".$setting['logo']))
                                        <img src="{{ asset("assets/images/".$setting['logo']) }}" width="70">
                                    @else
                                        <img src="{{ asset('assets/images/default-logo.png') }}" width="70">
                                    @endif
                                    <input type="file" class="form-control" name="logo"/>
                                    <span class="text-danger">{{ $errors->first('logo') }}</span>
                                </div>

                                <div class="col-12 {{ $errors->has('company_signature') ? 'has-error' : '' }}">
                                    <label class="mt-2 mt-md-0">Company Signature</label>
                                    @if(file_exists("assets/images/".$setting['company_signature']))
                                        <img src="{{ asset("assets/images/".$setting['company_signature']) }}" width="70">
                                    @endif
                                    <input type="file" class="form-control" name="company_signature"/>
                                    <span class="text-danger">{{ $errors->first('company_signature') }}</span>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="col-12 {{ $errors->has('address') ? 'has-error' : '' }}">
                                    <label class="mt-2 mt-md-0">Address</label>
                                    <textarea class="form-control" name="address" rows="5"
                                              placeholder="Enter Address">{{$setting['address']}}</textarea>
                                    <span class="text-danger">{{ $errors->first('address') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-3 {{ $errors->has('invoice_system') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Client Invoice System</label>
                                <select name="invoice_system" id="invoiceSystem" class="form-control">
                                    <option value="fixed" {{ ($setting['invoice_system']=='fixed')?'selected':'' }}>Fixed Date</option>
                                    <option value="dynamic" {{ ($setting['invoice_system']=='dynamic')?'selected':'' }}>Dynamic Date</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('invoice_system') }}</span>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group" id="expDate">
                                    <div class="{{ $errors->has('exp_date') ? 'has-error' : '' }}">
                                        <label class="mt-2 mt-md-0">Client Expire Date</label>
                                        <input type="text" class="form-control" name="exp_date"
                                               value="{{ $setting['exp_date'] }}" placeholder="Enter Exp Date">
                                        <span class="text-danger">{{ $errors->first('exp_date') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 {{ $errors->has('exp_date') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Client Expire Time *</label>
                                <?php
//                                 $expire_times = [
//                                        '12 AM','01 AM','02 AM','03 AM','04 AM','05 AM','06 AM','07 AM','08 AM','09 AM','10 AM','11 AM',
//                                        '12 PM','01 PM','02 PM','03 PM','04 PM','05 PM','06 PM','07 PM','08 PM','09 PM','10 PM','11 PM',
//                                     ];
//                                 ?>

                                <select name="exp_time" id="exp_time" class="form-control">
                                    <option value="" disabled {{ !isset($setting['exp_time']) ? 'selected' : '' }}>Select Hour</option>
                                    <option value="00:00" {{ $setting['exp_time'] == '00:00' ? 'selected' : '' }}>12:00 AM</option>
                                    <option value="00:30" {{ $setting['exp_time'] == '00:30' ? 'selected' : '' }}>12:30 AM</option>
                                    <option value="05:00" {{ $setting['exp_time'] == '05:00' ? 'selected' : '' }}>05:00 AM</option>
                                    <option value="05:30" {{ $setting['exp_time'] == '05:30' ? 'selected' : '' }}>05:30 AM</option>
                                    <option value="10:00" {{ $setting['exp_time'] == '10:00' ? 'selected' : '' }}>10:00 AM</option>
                                    <option value="10:30" {{ $setting['exp_time'] == '10:30' ? 'selected' : '' }}>10:30 AM</option>
                                    <option value="15:00" {{ $setting['exp_time'] == '15:00' ? 'selected' : '' }}>03:00 PM</option>
                                    <option value="15:30" {{ $setting['exp_time'] == '15:30' ? 'selected' : '' }}>03:30 PM</option>
                                    <option value="20:00" {{ $setting['exp_time'] == '20:00' ? 'selected' : '' }}>08:00 PM</option>
                                    <option value="20:30" {{ $setting['exp_time'] == '20:30' ? 'selected' : '' }}>08:30 PM</option>
                                </select>
{{--                                <input type="time" name="exp_time" id="exp_time" value="{{ $setting['exp_time'] }}" class="form-control">--}}
                                {{--<select name="exp_time" id="exp_time" class="form-control">--}}
                                    {{--@foreach($expire_times as $key=>$time)--}}
                                    {{--<option value="{{ $key }}" {{ ($key==$setting['exp_time'])?'selected':'' }}>{{ $time }}</option>--}}
                                    {{--@endforeach--}}
                                {{--</select>--}}

                                <span class="text-danger">{{ $errors->first('exp_time') }}</span>
                            </div>
                            <div class="col-md-3 {{ $errors->has('prefix') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Client Prefix</label>
                                <input type="text" class="form-control" name="prefix" value="{{ $setting['prefix'] }}"
                                       placeholder="Client Username Prefix">
                                <span class="text-danger">{{ $errors->first('prefix') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-3 {{ $errors->has('print_receipt_after_payment') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Print Receipt on Payment?</label>
                                <select name="print_receipt_after_payment" class="form-control">
                                    <option value="Yes" {{ ($setting['print_receipt_after_payment']=='Yes')?'selected':'' }}>Yes</option>
                                    <option value="No" {{ ($setting['print_receipt_after_payment']=='No')?'selected':'' }}>No</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('print_receipt_after_payment') }}</span>
                            </div>

                            <div class="col-md-3 {{ $errors->has('receipt_print_type') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Receipt Print Type</label>
                                <select name="receipt_print_type" class="form-control">
                                    <option value="regular" {{ ($setting['receipt_print_type']=='regular')?'selected':'' }}>Regular</option>
                                    <option value="pos" {{ ($setting['receipt_print_type']=='pos')?'selected':'' }}>POS</option>
                                </select>
                                <span class="text-danger">{{ $errors->first('print_receipt_after_payment') }}</span>
                            </div>

                            <div class="col-md-3 {{ $errors->has('expire_client_days') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Next * day's expire client</label>
                                <input type="number" class="form-control"
                                       name="expire_client_days" value="{{ $setting['expire_client_days'] }}"
                                       min="0">
                                <span class="text-danger">{{ $errors->first('expire_client_days') }}</span>
                            </div>

                            <div class="col-md-3">
                                <label class="mt-2 mt-md-0">Using With Mikrotik?</label>
                                <input type="text" readonly class="form-control"
                                       value="{{ $setting['using_mikrotik']=='1'?'Yes':'No' }}">
                            </div>

                            {{--<div class="col-md-3 {{ $errors->has('using_mikrotik') ? 'has-error' : '' }}">--}}
                                {{--<label class="mt-2 mt-md-0">Connect Mikrotik?</label>--}}
                                {{--<select name="using_mikrotik" class="form-control">--}}
                                    {{--<option value="1" {{ ($setting['using_mikrotik']=='1')?'selected':'' }}>Yes</option>--}}
                                    {{--<option value="0" {{ ($setting['using_mikrotik']=='0')?'selected':'' }}>No</option>--}}
                                {{--</select>--}}
                                {{--<span class="text-danger">{{ $errors->first('using_mikrotik') }}</span>--}}
                            {{--</div>--}}
                            {{--<label class="col-6 col-md-2 col-form-label">Currency Code</label>--}}
                            {{--<div class="col-4 {{ $errors->has('currencyCode') ? 'has-error' : '' }}">--}}
                                {{--<input type="text" class="form-control" name="currencyCode"--}}
                                       {{--value="{{ $setting['currencyCode'] }}" placeholder="Enter Currency Code">--}}
                                {{--<span class="text-danger">{{ $errors->first('currencyCode') }}</span>--}}
                            {{--</div>--}}

                        </div>

                        <div class="form-group row">
                            <div class="col-md-3 {{ $errors->has('name_operator') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">BTRC Operator Name</label>
                                <input type="text" class="form-control" name="name_operator"
                                       value="{{ $setting['name_operator'] }}" placeholder="Enter BTRC Operator Name">
                                <span class="text-danger">{{ $errors->first('name_operator') }}</span>
                            </div>
                            <div class="col-6 col-md-2 {{ $errors->has('type_of_client') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">BTRC Type Of Client</label>
                                <input type="text" class="form-control" name="type_of_client"
                                       value="{{ $setting['type_of_client'] }}" placeholder="Enter BTRC Type Of Client">
                                <span class="text-danger">{{ $errors->first('type_of_client') }}</span>
                            </div>
                            <div class="col-6 col-md-2 {{ $errors->has('vatRate') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Vat Rate</label>
                                <input type="text" class="form-control" name="vatRate"
                                       value="{{ $setting['vatRate'] }}" placeholder="Enter Vat Rate">
                                <span class="text-danger">{{ $errors->first('vatRate') }}</span>
                            </div>
                            
                            <div class="col-md-3 ">
                                <label class="mt-2 mt-md-0">Client Bandhwidth</label>
                                <select name="client_bandwidth" class="form-control">
                                    <option value="1" {{ ($setting['client_bandwidth']==1)?'selected':'' }}>Yes</option>
                                    <option value="0" {{ ($setting['client_bandwidth']==0)?'selected':'' }}>No</option>
                                </select>
                                <span class="text-danger"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-4 {{ $errors->has('payment_terms_condition') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Payment Terms & Policies</label>
                                <textarea class="form-control" name="payment_terms_condition"
                                          placeholder="Enter Payment Terms & Policies">{{$setting['payment_terms_condition']}}</textarea>
                                <span class="text-danger">{{ $errors->first('payment_terms_condition') }}</span>
                            </div>
                            <div class="col-md-4 {{ $errors->has('notice') ? 'has-error' : '' }}">
                                <label class="mt-2 mt-md-0">Notice</label>
                                <textarea class="form-control" name="notice"
                                          placeholder="Notice will be displayed at client panel">{{ $setting['notice'] }}</textarea>
                                <span class="text-danger">{{ $errors->first('notice') }}</span>
                            </div>
                            <div class="col-md-4">
                                <label class="mt-2 mt-md-0">New Client Terms & Policies </label>
                                <textarea class="form-control" name="new_client_terms_condition"
                                          placeholder="Terms & Policies displayed at client panel while Printing">{{ $setting['new_client_terms_condition'] }}</textarea>
                                <span class="text-danger">{{ $errors->first('new_client_terms_condition') }}</span>
                            </div>
                        </div>

                    @else
                        <div class="form-group row">
                            <label class="col-6 col-md-2 col-form-label">Business Name</label>
                            <div class="col-5 {{ $errors->has('business_name') ? 'has-error' : '' }}">
                                <input type="text" class="form-control" name="business_name"
                                       value="{{ $r->business_name }}" placeholder="Enter Your Business Name">
                                <span class="text-danger">{{ $errors->first('business_name') }}</span>
                            </div>
                            <label class="col-1 col-form-label">Phone No</label>
                            <div class="col-4 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                <input type="text" class="form-control" name="phone" value="{{ $r->phone }}"
                                       placeholder="Enter Phone No">
                                <span class="text-danger">{{ $errors->first('phone') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-6 col-md-2 col-form-label">Logo</label>
                            <div class="col-4 {{ $errors->has('logo') ? 'has-error' : '' }}">
                                @if(file_exists("assets/images/".$r->logo ))
                                    <img src="{{ asset("assets/images/".$r->logo) }}" width="70">
                                @else
                                    <img src="{{ asset('assets/images/default-logo.png') }}" width="70">
                                @endif
                                <input type="file" name="logo"/>
                                <span class="text-danger">{{ $errors->first('logo') }}</span>
                            </div>

                            <label class="col-6 col-md-2 col-form-label">Signature</label>
                            <div class="col-4 {{ $errors->has('signature') ? 'has-error' : '' }}">
                                @if(file_exists("assets/images/".$r->signature))
                                    <img src="{{ asset("assets/images/".$r->signature) }}" width="70">
                                @endif
                                <input type="file" name="signature"/>
                                <span class="text-danger">{{ $errors->first('signature') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-6 col-md-2 col-form-label">Address</label>
                            <div class="col-10 {{ $errors->has('resellerLocation') ? 'has-error' : '' }}">
                                <textarea class="form-control" name="resellerLocation"
                                          placeholder="Enter Address">{{ $r->resellerLocation }}</textarea>
                                <span class="text-danger">{{ $errors->first('resellerLocation') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-6 col-md-2 col-form-label">Notice</label>
                            <div class="col-10 {{ $errors->has('notice') ? 'has-error' : '' }}">
                                <textarea class="form-control" name="notice"
                                          placeholder="Notice will be displayed at client panel">{{ $r->notice }}</textarea>
                                <span class="text-danger">{{ $errors->first('notice') }}</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-6 col-md-2 col-form-label">Client Prefix</label>
                            <div class="col-10 {{ $errors->has('prefix') ? 'has-error' : '' }}">
                                <input type="text" class="form-control" name="prefix" value="{{ $r->prefix }}"
                                       placeholder="Client Username Prefix">
                                <span class="text-danger">{{ $errors->first('prefix') }}</span>
                            </div>
                        </div>
                        {{--@if($setting['invoice_system'] == 'fixed')--}}
                        {{--<div class="form-group row">--}}
                            {{--<label class="col-6 col-md-2 col-form-label">Client Expire Date</label>--}}
                            {{--<div class="col-10 {{ $errors->has('exp_date') ? 'has-error' : '' }}">--}}
                                {{--<input type="text" class="form-control" name="exp_date"--}}
                                       {{--value="{{ $r->c_exp_date }}" placeholder="Enter Exp Date">--}}
                                {{--<span class="text-danger">{{ $errors->first('exp_date') }}</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                        {{--@endif--}}

                        {{--<div class="form-group row">--}}
                            {{--<label class="col-6 col-md-2 col-form-label">Vat Rate</label>--}}
                            {{--<div class="col-10 {{ $errors->has('vat_rate') ? 'has-error' : '' }}">--}}
                                {{--<input type="text" class="form-control" name="vat_rate"--}}
                                       {{--value="{{ $r->vat_rate }}" placeholder="Enter Vat Rate">--}}
                                {{--<span class="text-danger">{{ $errors->first('vat_rate') }}</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    @endif

                    <div class="form-group mt-4 text-center">
                        <button type="submit" class="btn btn-info waves-effect waves-light">Update Settings</button>
                    </div>


                </form>

            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->

@endsection

@section('custom_js')
    <script>
        var invoiceSystem = $("#invoiceSystem").val();
        if(invoiceSystem =='fixed'){
            $("#expDate").show()
        } else {
            $("#expDate").hide()
        }

        $("#invoiceSystem").on('change',function(){
            var invoiceSystem = $("#invoiceSystem").val();
            if(invoiceSystem =='fixed'){
                $("#expDate").show()
            } else {
                $("#expDate").hide()
            }
        })

        // var receiptPrint = $("#receiptPrint").val();
        // if(receiptPrint =='Yes'){
        //     $("#receiptPrintType").show()
        // } else {
        //     $("#receiptPrintType").hide()
        // }

        // $("#receiptPrint").on('change',function(){
        //     var receiptPrint = $("#receiptPrint").val();
        //     if(receiptPrint =='Yes'){
        //         $("#receiptPrintType").show()
        //     } else {
        //         $("#receiptPrintType").hide()
        //     }
        // })
    </script>
@endsection
