@extends ('admin.layouts.master')
@section('title','Add Client')
@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card-box">

                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="" role="form" action="{{ route('client.add.post') }}" method="POST"
                                  enctype="multipart/form-data">
                                {{ csrf_field() }}
                                {{--<div class="form-group mb-0 justify-content-end row">--}}
                                {{--<div class="col-10">--}}
                                {{--<h4>Client Information</h4>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group row">

                                            <label class="col-4 col-form-label">Client Name <span class="text-warning"> <span
                                                            class="text-warning">*</span></span>

                                            </label>
                                            <div class="col-8 {{ $errors->has('client_name') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="client_name"
                                                       value="{{ old('client_name') }}" placeholder="Enter Client Name"
                                                       required autofocus>
                                                <span class="text-danger">{{ $errors->first('client_name') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Username <span
                                                        class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('username') ? 'has-error' : '' }}">
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">{{ $prefix }}</span>
                                                    </div>
                                                    <input type="text" class="form-control" name="username"
                                                           value="{{ old('username') }}" placeholder="Enter Username"
                                                           required>
                                                    <span class="text-danger">{{ $errors->first('username') }}</span>
                                                </div>
                                            </div>
                                        </div>


                                        {{--<div class="form-group row">--}}
                                        {{--<label class="col-4 col-form-label">Email</label>--}}
                                        {{--<div class="col-8 {{ $errors->has('email') ? 'has-error' : '' }}">--}}
                                        {{--<input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Enter Client Email Address">--}}
                                        {{--<span class="text-danger">{{ $errors->first('email') }}</span>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Client Login Pass <span
                                                        class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('password') ? 'has-error' : '' }}">
                                                <input type="password" class="form-control" name="password"
                                                       value="{{ old('password') }}"
                                                       placeholder="Enter Client Login Password" required>
                                                <span class="text-danger">{{ $errors->first('password') }}</span>
                                            </div>
                                        </div>

                                        {{--<div class="form-group row">--}}
                                        {{--<label class="col-4 col-form-label">Confirm Password <span class="text-warning">*</span></label>--}}
                                        {{--<div class="col-8 {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">--}}
                                        {{--<input type="password" class="form-control" name="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="Enter Confirm Login Password" required>--}}
                                        {{--<span class="text-danger">{{ $errors->first('password_confirmation') }}</span>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Phone Number</label>
                                            <div class="col-8 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="phone"
                                                       value="{{ old('phone') }}" placeholder="Enter Phone Number">
                                                <span class="text-danger">{{ $errors->first('phone') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Client NID</label>
                                            <div class="col-8 {{ $errors->has('clientNid') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="clientNid"
                                                       value="{{ old('clientNid') }}" placeholder="Enter NID Number">
                                                <span class="text-danger">{{ $errors->first('clientNid') }}</span>
                                            </div>
                                        </div>
                                        @php
                                            $isSuperAdmin = empty(Auth::user()->resellerId); // true if resellerId null/empty
                                        @endphp
                                        @if ($isSuperAdmin || $reseller_has_extra_charge == 1)

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Client Discount</label>
                                                <div class="col-8 {{ $errors->has('discount') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="discount" value="0.00"
                                                           placeholder="Enter Discount">
                                                    <span class="text-danger">{{ $errors->first('discount') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Extra Charge</label>
                                                <div class="col-8 {{ $errors->has('charge') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="charge" value="0.00"
                                                           placeholder="Enter Extra Charge">
                                                    <span class="text-danger">{{ $errors->first('charge') }}</span>
                                                </div>
                                            </div>

                                        @else
                                            <input type="hidden" name="charge" value="0.00">
                                            <input type="hidden" name="discount" value="0.00">

                                        @endif
                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">OTC Fee</label>
                                            <div class="col-8 {{ $errors->has('otc_charge') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="otc_charge"
                                                       value="0.00"
                                                       placeholder="Enter OTC Fee">
                                                <span class="text-danger">{{ $errors->first('otc_charge') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Distribution <span class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('distribution') ? 'has-error' : '' }}">
                                                <select class="form-control" name="distribution" required>
                                                    <option value="">Select Distribution Area</option>
                                                    @foreach($distributions as $distribution)
                                                        <option value="{{ $distribution->id}}" {{ (collect(old('distribution'))->contains($distribution['id'])) ? 'selected':'' }}>{{$distribution->distribution}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger">{{ $errors->first('distribution') }}</span>
                                            </div>
                                        </div>

                                    </div><!-- end col -->

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">
                                                Address/Area <span class="text-warning">*</span>
                                                <button type="button" class="btn btn-link text-info">
                                                        <span data-toggle="collapse" data-target="#collapseOne"
                                                              aria-expanded="false" aria-controls="collapseOne">
                                                            <i class="fa fa-plus"></i> Add Details
                                                        </span>
                                                </button>
                                            </label>
                                            <div class="col-8 {{ $errors->has('address') ? 'has-error' : '' }}">
                                                <textarea class="form-control" name="address" rows="1" cols="1"
                                                          placeholder="Enter Address Area" style="min-height: 50px;"
                                                          required>{{ old('address') }}</textarea>
                                                <span class="text-danger">{{ $errors->first('address') }}</span>
                                            </div>
                                        </div>

                                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                             data-parent="#accordion">

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">House Number</label>
                                                <div class="col-8 {{ $errors->has('house_no') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="house_no"
                                                           value="{{ old('house_no') }}"
                                                           placeholder="Enter House Number">
                                                    <span class="text-danger">{{ $errors->first('house_no') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Road Number</label>
                                                <div class="col-8 {{ $errors->has('road_no') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="road_no"
                                                           value="{{ old('road_no') }}" placeholder="Enter Road Number">
                                                    <span class="text-danger">{{ $errors->first('road_no') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Thana</label>
                                                <div class="col-8 {{ $errors->has('thana') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="thana"
                                                           value="{{ old('thana') }}" placeholder="Enter Thana">
                                                    <span class="text-danger">{{ $errors->first('thana') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">District</label>
                                                <div class="col-8 {{ $errors->has('district') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="district"
                                                           value="{{ old('district') }}" placeholder="Enter District">
                                                    <span class="text-danger">{{ $errors->first('district') }}</span>
                                                </div>
                                            </div>

                                            {{-- Client Photo --}}
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Client Photo</label>
                                                <div class="col-8 {{ $errors->has('client_photo') ? 'has-error' : '' }}">
                                                    <input type="file" class="form-control" name="client_photo"
                                                           accept="image/*">
                                                    <span class="text-danger">{{ $errors->first('client_photo') }}</span>
                                                </div>
                                            </div>

                                            {{-- Other Documents (PDFs or Images) --}}
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Other Documents</label>
                                                <div class="col-8 {{ $errors->has('other_documents') ? 'has-error' : '' }}">
                                                    <input type="file" class="form-control" name="other_documents[]"
                                                           accept="image/*,application/pdf" multiple>
                                                </div>
                                            </div>


                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Note</label>
                                            <div class="col-8 {{ $errors->has('note') ? 'has-error' : '' }}">
                                                <textarea class="form-control" rows="1" cols="1" name="note"
                                                          value="{{ old('note') }}" placeholder="Additional Information"
                                                          style="min-height: 50px;"></textarea>
                                                <span class="text-danger">{{ $errors->first('note') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Connection Type <span
                                                        class="text-warning">*</span></label>
                                            {{-- <div class="col-8 {{ $errors->has('type') ? 'has-error' : '' }}"> --}}
                                            <div class="col-8">
                                                <select class="form-control" id="cable_type" name="cable_type" required>
                                                    <option value="">Select Cable Type</option>
                                                    <option value="UTP">UTP</option>
                                                    <option value="Fiber">Fiber</option>
                                                    <option value="Wireless">Wireless</option>
                                                </select>
                                                <span class="text-danger">{{ $errors->first('type') }}</span>
                                            </div>
                                        </div>

                                        {{--OLT er kaj--}}
                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">OLT Type <span class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('olt_type') ? 'has-error' : '' }}">
                                                <select class="form-control" name="olt_type" required>
                                                    <option value="">Select OLT</option>
                                                    <option value="Solitine">Solitine</option>
                                                    <option value="VSOL">VSOL</option>
                                                    <option value="BDCOM">BDCOM</option>
                                                    <option value="DBC">DBC</option>
                                                    <option value="HSGQ">HSGQ</option>
                                                    <option value="CORELINK">CORE LINK</option>
                                                    <option value="WISEE">WISEE</option>
                                                    <option value="TBS">TBS</option>
                                                </select>
                                                <span class="text-danger">{{ $errors->first('olt_type') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Service Type <span class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('type') ? 'has-error' : '' }}">
                                                <select class="form-control" id="type" name="type" required>
                                                    <option value="">Select Plan Type</option>
                                                    <option value="PPPOE">PPPOE</option>
                                                    <option value="Hotspot">Hotspot</option>
                                                    <option value="IP">IP</option>
                                                </select>
                                                <span class="text-danger">{{ $errors->first('type') }}</span>
                                            </div>
                                        </div>

                                        @role('Reseller')
                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Plan Name <span
                                                        class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('plan_id') ? 'has-error' : '' }}">
                                                <select class="form-control" id="plan_resell" name="plan_id" required>
                                                    <option value="">Select Plan Name</option>
                                                </select>
                                                <span class="text-danger">{{ $errors->first('plan_id') }}</span>
                                            </div>
                                        </div>
                                        @else
                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Server Name <span
                                                            class="text-warning">*</span></label>
                                                <div class="col-8 {{ $errors->has('server_id') ? 'has-error' : '' }}">
                                                    <select class="form-control" id="server" name="server_id" required>
                                                        <option value="">Select Server Name</option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('server_id') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Plan Name <span
                                                            class="text-warning">*</span></label>
                                                <div class="col-8 {{ $errors->has('plan_id') ? 'has-error' : '' }}">
                                                    <select class="form-control" id="plan" name="plan_id" required>
                                                        <option value="">Select Plan Name</option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('plan_id') }}</span>
                                                </div>
                                            </div>
                                            @endrole

                                            <div style="display:none;" id="server_password">

                                                <div class="form-group row">
                                                    <label class="col-4 col-form-label">Router Password <span
                                                                class="text-warning">*</span></label>
                                                    <div class="col-8 {{ $errors->has('server_password') ? 'has-error' : '' }}">
                                                        <input type="text" class="form-control" name="server_password"
                                                               value="{{ old('server_password') }}"
                                                               placeholder="Enter Router Password">
                                                        <span class="text-danger">{{ $errors->first('server_password') }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row" style="display:none;" id="client_ip">
                                                <label class="col-4 col-form-label">Client IP <span
                                                            class="text-warning">*</span></label>
                                                <div class="col-8 {{ $errors->has('client_ip') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" id="c_ip" name="client_ip"
                                                           value="" placeholder="Enter Client IP">
                                                    <span class="text-danger">{{ $errors->first('client_ip') }}</span>
                                                </div>
                                            </div>


                                            {{--@if(Auth::user()->roleId != 4 && Auth::user()->roleId != 3)--}}
                                            {{--<div class="form-group row">--}}
                                            {{--<label class="col-4 col-form-label">Branch</label>--}}
                                            {{--<div class="col-8 {{ $errors->has('branchId') ? 'has-error' : '' }}">--}}
                                            {{--<select class="form-control" name="branchId">--}}
                                            {{--<option value="">Select Branch</option>--}}
                                            {{--@foreach($branches as $branch)--}}
                                            {{--<option value="{{ $branch->branchId}}" {{ (collect(old('branchId'))->contains($branch['branchId'])) ? 'selected':'' }}>{{$branch->branchName}}</option>--}}
                                            {{--@endforeach--}}
                                            {{--</select>--}}
                                            {{--<span class="text-danger">{{ $errors->first('branchId') }}</span>--}}
                                            {{--</div>--}}
                                            {{--</div>--}}
                                            {{--@endif--}}

                                    </div><!-- end col -->
                                </div><!-- end row -->

                                @if(Auth::user()->branchId != '')
                                    <input type="hidden" name="branchId" value="{{ Auth::user()->branchId }}">
                                @endif
                                <input type="hidden" name="resellerId" value="{{ Auth::user()->resellerId }}">
                                <input type="hidden" name="type_of_connection" value="Wired">
                                <input type="hidden" name="type_of_connectivity" value="Shared">

                                {{--<div class="form-group mb-0 justify-content-end row">--}}
                                {{--<div class="col-10">--}}
                                {{--<h4>Service Information</h4>--}}
                                {{--</div>--}}
                                {{--</div>--}}

                                {{--<div class="form-group row">--}}
                                {{--<label class="col-2 col-form-label">Connection Type <span class="text-warning">*</span></label>--}}
                                {{--<div class="col-10 {{ $errors->has('type_of_connection') ? 'has-error' : '' }}">--}}
                                {{--<select class="form-control" name="type_of_connection" required>--}}
                                {{--<option value="">Select Connection Type</option>--}}
                                {{--<option value="Wired">Wired</option>--}}
                                {{--<option value="Wireless">Wireless</option>--}}
                                {{--</select>--}}
                                {{--<span class="text-danger">{{ $errors->first('type_of_connection') }}</span>--}}
                                {{--</div>--}}
                                {{--</div>--}}

                                {{--<div class="form-group row">--}}
                                {{--<label class="col-2 col-form-label">Connectivity Type <span class="text-warning">*</span></label>--}}
                                {{--<div class="col-10 {{ $errors->has('type_of_connectivity') ? 'has-error' : '' }}">--}}
                                {{--<select class="form-control" name="type_of_connectivity" required>--}}
                                {{--<option value="">Select Connectivity Type</option>--}}
                                {{--<option value="Shared">Shared</option>--}}
                                {{--<option value="Dedicate">Dedicate</option>--}}
                                {{--</select>--}}
                                {{--<span class="text-danger">{{ $errors->first('type_of_connectivity') }}</span>--}}
                                {{--</div>--}}
                                {{--</div>--}}

                                <div class="form-group mt-3 justify-content-center row">
                                    <div class="col-4 offset-3">
                                        <button type="submit" class="btn btn-info waves-effect waves-light">Add Client
                                        </button>
                                        <a href="{{ route('client.index') }}" class="btn btn-secondary">Cancel</a>
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
    @include('admin.layouts.custom-js')
@endsection
