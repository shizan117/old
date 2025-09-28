@extends ('admin.layouts.master')
@section('title')
    SMS Content Setup
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs nav-fill d-block d-md-flex">
                <li class="nav-item">
                    <a class="nav-link active" href="#sms" role="tab" data-toggle="tab"><i
                            class="fa fa-envelope-open"></i> Send SMS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#content" role="tab" data-toggle="tab"><i class="fa fa-file-text"></i> SMS
                        Content</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#settings" role="tab" data-toggle="tab"><i class="fa fa-cog"></i> SMS API
                        Settings</a>
                </li>
            </ul>


            <div class="card-box">
                <!-- Tab panes -->
                <div class="tab-content p-0">
                    {{-- CUSTOM SMS --}}
                    <div role="tabpanel" class="tab-pane active" id="sms">
                        <form action="{{ route('custom.sms.send') }}" method="post"
                            onsubmit="submitBtn.disabled = true; return true;">
                            @csrf
                            <div class="row mb-3">

                                <div class="col-12 col-md-2">
                                    <select name="clientType" id="clientType" class="form-control bg-secondary">
                                        <option value="">All Client</option>
                                        @php($clientTypes = ['active', 'inactive', 'old', 'due'])
                                        @foreach ($clientTypes as $type)
                                            <option value="{{ $type }}"
                                                {{ request('clientType') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) . ' Client' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 offset-md-7 my-2 my-md-0">
                                    <select name="smsType" id="smsType" class="form-control bg-secondary">
                                        <option value="">SMS Template</option>
                                        @php($smsTypes = ['sms_new_client' => 'New Client', 'sms_invoice' => 'Invoice Create', 'sms_payment' => 'Payment Confirmation', 'sms_remainder' => 'Payment Remainder', 'sms_disconnect' => 'Line Disconnect', 'sms_custom' => 'Custom SMS'])
                                        @foreach ($smsTypes as $key => $type)
                                            <option value="{{ $key }}"
                                                {{ request('smsType') == $key ? 'selected' : '' }}>{{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-1">
                                    <button type="submit" name="submitBtn"
                                        onclick="return confirm('Send SMS to the selected clients?')"
                                        class="btn btn-outline-warning waves-effect waves-light">Send
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <table id="datatable" class="table table-sm table-bordered" cellspacing="0"
                                        width="100%">
                                        <thead>
                                            <tr>
                                                {{-- @if (Auth::user()->name == 'Super Admin') --}}
                                                <th><input type="checkbox" id="checkedAll" value="all"></th>
                                                {{-- @endif --}}
                                                <th>SL</th>
                                                <th>Client Name</th>
                                                <th>Username</th>
                                                <th>Phone</th>
                                                <th>Box/Area</th>
                                                <th>Plan</th>
                                                <th>Due</th>
                                                <th>Exp Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>


                                        <tbody>
                                            @foreach ($clientData as $dataClient)
                                                <tr>
                                                    @php($dataClient->status == 'On' ? ($status = 'Active') : ($status = 'Inactive'))
                                                    {{-- @if (Auth::user()->name == 'Super Admin') --}}
                                                    <td>
                                                        <input name="clientID[]" class="checkSingle" type="checkbox"
                                                            value="{{ $dataClient->id }}">
                                                    </td>
                                                    {{-- @endif --}}
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <td data-toggle="tooltip" data-placement="right"
                                                        title="{{ $dataClient['house_no'] . '-' . $dataClient['address'] }}">
                                                        {{ $dataClient['client_name'] }}</td>
                                                    <td>{{ $dataClient['username'] }}</td>
                                                    <td>{{ $dataClient['phone'] }}</td>
                                                    <td>{{ $dataClient->distribution->distribution ?? '--' }}</td>
                                                    <td>{{ $dataClient->plan->plan_name ?? '--' }}</td>
                                                    <td>{{ $dataClient['due'] }}</td>
                                                    <td data-toggle="tooltip" data-placement="left"
                                                        title="{{ date('h:i A', strtotime($dataClient['expiration'])) }}">
                                                        {{ date('d-M-y', strtotime($dataClient['expiration'])) }} </td>
                                                    <td>{{ $status }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                    @if (Auth::user()->resellerId == '')
                        <!-- SMS CONTENT -->
                        <div role="tabpanel" class="tab-pane" id="content">
                            <div class="row">
                                <div class="col-12">
                                    <div class="p-20">
                                        <form class="form-horizontal" role="form"
                                            action="{{ route('config.sms.content.update') }}" method="POST">

                                            {{ csrf_field() }}
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">New Client SMS:</label>
                                                <div
                                                    class="col-md-9 {{ $errors->has('smsNewClient') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [password]</span>
                                                    <textarea class="form-control" name="smsNewClient">{{ $setting['sms_new_client'] }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsNewClient') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">Invoice Create SMS:</label>
                                                <div class="col-md-9 {{ $errors->has('smsInvoice') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsInvoice">{{ $setting['sms_invoice'] }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsInvoice') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">Payment Remainder SMS:</label>
                                                <div
                                                    class="col-md-9 {{ $errors->has('smsRemainder') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsRemainder">{{ $setting['sms_remainder'] }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsRemainder') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">Payment Confirmation SMS:</label>
                                                <div class="col-md-9 {{ $errors->has('smsPayment') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due],
                                                        [paid]</span>
                                                    <textarea class="form-control" name="smsPayment">{{ $setting['sms_payment'] }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsPayment') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">Line Disconnect SMS:</label>
                                                <div
                                                    class="col-md-9 {{ $errors->has('smsDisconnect') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsDisconnect">{{ $setting['sms_disconnect'] }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsDisconnect') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">Reseller Confirmation SMS:</label>
                                                <div
                                                    class="col-md-9 {{ $errors->has('smsReseller') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [paid]</span>
                                                    <textarea class="form-control" name="smsReseller">{{ $setting['sms_payment_to_reseller'] }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsReseller') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-3 col-form-label">Custom SMS:</label>
                                                <div class="col-md-9 {{ $errors->has('smsCustom') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsCustom">{{ $setting['sms_custom'] }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsCustom') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 justify-content-end row">
                                                <div class="col-9">
                                                    <button type="submit"
                                                        class="btn btn-info waves-effect waves-light">Update</button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SMS API -->
                        <div role="tabpanel" class="tab-pane" id="settings">
                            <div class="row">
                                <div class="col-12">
                                    @if ($sms_balance != '')
                                        @if ($sms_balance == '1003')
                                            <p class="text-warning text-center">Invalid API Key</p>
                                        @else
                                            <p class="text-warning text-center">{{ $sms_balance }}
                                                <a href="https://deelko.com/manual/sms/sms.pdf" target="_blank"
                                                    class="d-inline ml-2">T&C</a>
                                            </p>
                                        @endif
                                    @endif
                                    <div class="card-box table-responsive">
                                        <form class="form-horizontal" role="form"
                                            action="{{ route('config.sms.api') }}" method="POST">
                                            {{ csrf_field() }}

                                            {{-- <div class="form-group row">
                                                <label class="col-4 col-md-2 col-form-label">API Base URL</label>
                                                <div
                                                    class="col-8 col-md-10 {{ $errors->has('sms_api_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_api_url"
                                                        value="{{ $setting['sms_api_url'] }}" placeholder="API Base URL">
                                                    <span class="text-danger">{{ $errors->first('sms_api_url') }}</span>
                                                </div>
                                            </div> --}}

                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">API Key *</label>
                                                <div
                                                    class="col-md-10 col-8 {{ $errors->has('sms_api_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_api_key"
                                                        value="{{ $setting['sms_api_key'] }}" placeholder="SMS API Key"
                                                        required>
                                                    <span class="text-danger">{{ $errors->first('sms_api_key') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Secret Key *</label>
                                                <div
                                                    class="col-md-10 col-8 {{ $errors->has('sms_secret_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_secret_key"
                                                        value="{{ $setting['sms_secret_key'] }}"
                                                        placeholder="SMS Secret Key" required>
                                                    <span
                                                        class="text-danger">{{ $errors->first('sms_secret_key') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Masking ID *</label>
                                                <div
                                                    class="col-md-10 col-8 {{ $errors->has('sms_masking_id') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_masking_id"
                                                        value="{{ $setting['sms_masking_id'] }}"
                                                        placeholder="Masking Name" required>
                                                    <span
                                                        class="text-danger">{{ $errors->first('sms_masking_id') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-2 col-form-label">Client ID *</label>
                                                <div
                                                    class="col-10 {{ $errors->has('sms_client_id') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_client_id"
                                                        value="{{ $setting['sms_client_id'] }}" placeholder="Client ID"
                                                        required>
                                                    <span class="text-danger">{{ $errors->first('sms_client_id') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Status *</label>
                                                <div
                                                    class="col-md-10 col-8 {{ $errors->has('sms_is_active') ? 'has-error' : '' }}">
                                                    <select name="sms_is_active" class="form-control" required>
                                                        <option value="0"
                                                            {{ $setting['sms_is_active'] == '0' ? 'selected' : '' }}>
                                                            Inactive</option>
                                                        <option value="1"
                                                            {{ $setting['sms_is_active'] == '1' ? 'selected' : '' }}>Active
                                                        </option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('sms_is_active') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 justify-content-end row">
                                                <div class="col-10">
                                                    <button type="submit"
                                                        class="btn btn-info waves-effect waves-light">Update
                                                        Settings</button>
                                                </div>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div> <!-- end row -->
                        </div>
                    @else
                        <!-- Content -->
                        <div role="tabpanel" class="tab-pane" id="content">
                            <div class="row">
                                <div class="col-12">
                                    <div class="p-20">
                                        <form class="form-horizontal" role="form"
                                            action="{{ route('config.sms.content.update') }}" method="POST">

                                            {{ csrf_field() }}
                                            <div class="form-group row">
                                                <label class="col-3 col-form-label">New Client SMS:</label>
                                                <div class="col-9 {{ $errors->has('smsNewClient') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [password]</span>
                                                    <textarea class="form-control" name="smsNewClient">{{ $r->sms_new_client }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsNewClient') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-3 col-form-label">Invoice Create SMS:</label>
                                                <div class="col-9 {{ $errors->has('smsInvoice') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsInvoice">{{ $r->sms_invoice }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsInvoice') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-3 col-form-label">Payment Remainder SMS:</label>
                                                <div class="col-9 {{ $errors->has('smsRemainder') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsRemainder">{{ $r->sms_remainder }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsRemainder') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-3 col-form-label">Payment Confirmation SMS:</label>
                                                <div class="col-9 {{ $errors->has('smsPayment') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due],
                                                        [paid]</span>
                                                    <textarea class="form-control" name="smsPayment">{{ $r->sms_payment }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsPayment') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-3 col-form-label">Line Disconnect SMS:</label>
                                                <div class="col-9 {{ $errors->has('smsDisconnect') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsDisconnect">{{ $r->sms_disconnect }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsDisconnect') }}</span>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-3 col-form-label">Custom SMS:</label>
                                                <div class="col-9 {{ $errors->has('smsCustom') ? 'has-error' : '' }}">
                                                    <span class="text-muted">[name], [username], [expiration], [due]</span>
                                                    <textarea class="form-control" name="smsCustom">{{ $r->sms_custom }}</textarea>
                                                    <span class="text-danger">{{ $errors->first('smsCustom') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 justify-content-end row">
                                                <div class="col-9">
                                                    <button type="submit"
                                                        class="btn btn-info waves-effect waves-light">Update</button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- API -->
                        <div role="tabpanel" class="tab-pane" id="settings">

                            <div class="row">
                                <div class="col-12">
                                    @if ($sms_balance != '')
                                        <p class="text-warning text-center">{{ $sms_balance }}
                                            <a href="https://deelko.com/manual/sms/sms.pdf" target="_blank"
                                                class="d-inline ml-2">T&C</a>
                                        </p>
                                    @endif
                                    <div class="card-box table-responsive">
                                        <form class="form-horizontal" role="form"
                                            action="{{ route('config.sms.api') }}" method="POST">
                                            {{ csrf_field() }}

                                            {{-- <div class="form-group row">
                                                <label class="col-2 col-form-label">API Base URL</label>
                                                <div class="col-10 {{ $errors->has('sms_api_url') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_api_url"
                                                        value="{{ $r->sms_api_url }}" placeholder="API Base URL">
                                                    <span class="text-danger">{{ $errors->first('sms_api_url') }}</span>
                                                </div>
                                            </div> --}}

                                            <div class="form-group row">
                                                <label class="col-2 col-form-label">API Key *</label>
                                                <div class="col-10 {{ $errors->has('sms_api_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_api_key"
                                                        value="{{ $r->sms_api_key }}" placeholder="SMS API Key" required>
                                                    <span class="text-danger">{{ $errors->first('sms_api_key') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-md-2 col-4 col-form-label">Secret Key *</label>
                                                <div
                                                    class="col-md-10 col-8 {{ $errors->has('sms_secret_key') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_secret_key"
                                                        value="{{ $r->sms_secret_key }}" placeholder="SMS Secret Key"
                                                        required>
                                                    <span
                                                        class="text-danger">{{ $errors->first('sms_secret_key') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-2 col-form-label">Masking ID *</label>
                                                <div
                                                    class="col-10 {{ $errors->has('sms_masking_id') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_masking_id"
                                                        value="{{ $r->sms_masking_id }}" placeholder="Masking Name"
                                                        required>
                                                    <span
                                                        class="text-danger">{{ $errors->first('sms_masking_id') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-2 col-form-label">Client ID *</label>
                                                <div
                                                    class="col-10 {{ $errors->has('sms_client_id') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="sms_client_id"
                                                        value="{{ $r->sms_client_id }}" placeholder="Client ID" required>
                                                    <span class="text-danger">{{ $errors->first('sms_client_id') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-2 col-form-label">Status *</label>
                                                <div
                                                    class="col-10 {{ $errors->has('sms_is_active') ? 'has-error' : '' }}">
                                                    <select name="sms_is_active" class="form-control" required>
                                                        <option value="0"
                                                            {{ $r->sms_is_active == '0' ? 'selected' : '' }}>Inactive
                                                        </option>
                                                        <option value="1"
                                                            {{ $r->sms_is_active == '1' ? 'selected' : '' }}>Active
                                                        </option>
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('sms_is_active') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0 justify-content-end row">
                                                <div class="col-10">
                                                    <button type="submit"
                                                        class="btn btn-info waves-effect waves-light">Update
                                                        Settings</button>
                                                </div>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div> <!-- end row -->
                        </div>
                    @endif
                </div>
            </div>

        </div><!-- end col -->
    </div>
    <!-- end row -->
@endsection

@section('custom_js')
    <script>
        //Buttons examples
        $('#datatable').DataTable({
            dom: 'Bfrtip',
            "pageLength": 100,
            "lengthMenu": [
                [20, 50, 100, -1],
                [20, 50, 100, "All"]
            ],
            "aaSorting": [],
            "columnDefs": [{
                "orderable": false,
                "targets": 0
            }],
            buttons: ['pageLength', 'excel',
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },
                    footer: true

                },

                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },
                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: 'Print',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)'],
                        modifier: {
                            page: 'current'
                        }
                    },

                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    },
                    footer: true
                }

            ],
        });
    </script>

    <script>
        $("#checkedAll").change(function() {
            if (this.checked) {
                $(".checkSingle").each(function() {
                    this.checked = true;
                    $("#print-button-wrapper").show();
                });
            } else {
                $(".checkSingle").each(function() {
                    this.checked = false;
                    $("#print-button-wrapper").hide();
                });
            }
        });



        $("#datatable").on('click', '.checkSingle', function() {
            if ($(this).is(":checked")) {
                var isAllChecked = 0;

                $(".checkSingle").each(function() {
                    if (!this.checked)
                        isAllChecked = 1;
                });

                if (isAllChecked == 0) {
                    $("#checkedAll").prop("checked", true);
                }
                $("#print-button-wrapper").show();
            } else {
                var isAllUnchecked = 0;
                $(".checkSingle").each(function() {
                    if (this.checked)
                        isAllUnchecked = 1;
                });

                if (isAllUnchecked == 0) {
                    $("#print-button-wrapper").hide();
                }
                $("#checkedAll").prop("checked", false);
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // $(".datepicker").datepicker({
            //     changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            // });
            $("#clientType").on('change', function() {
                var clientType = $("#clientType").val()
                window.location.href = "{{ route('config.sms.content') }}" + "?clientType=" + clientType;
            })
        });
    </script>
@endsection
@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
    <link href='{{ asset('assets/css/datatablesSelect.min.css') }}' rel="stylesheet" type="text/css" />
    {{--    <link href='{{ asset("assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css") }}' rel="stylesheet" type="text/css"/> --}}
@endsection
@section('custom_css')
    <style>
        .dataTable>thead>tr>th[class*=sort]:after {
            display: none;
        }

        .dataTable>thead>tr>th[class*=sort]:before {
            display: none;
        }

        table.dataTable tbody>tr.selected,
        table.dataTable tbody>tr>.selected {
            background-color: #292d30 !important;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/datatablesSelect.min.js') }}" type="text/javascript"></script>
    {{--    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script> --}}
@endsection
