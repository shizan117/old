<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
    Edit Client
@endsection

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

    @php
        $curentCableType = $clientData->cable_type;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card-box">

                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" role="form"
                                  action="{{ route('client.edit.post', $clientData->id) }}" method="POST" enctype="multipart/form-data">

                                {{ csrf_field() }}
                                {{--<div class="form-group mb-0 justify-content-end row">--}}
                                {{--<div class="col-10">--}}
                                {{--<h4>Client Information</h4>--}}
                                {{--</div>--}}
                                {{--</div>--}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">
                                                Client Name <span class="text-warning">*</span>

                                            </label>
                                            <div class="col-8 {{ $errors->has('client_name') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="client_name"
                                                       value="{{ $clientData->client_name }}" placeholder="Enter Client Name">
                                                <span class="text-danger">{{ $errors->first('client_name') }}</span>
                                            </div>
                                        </div>




                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Username <span
                                                        class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('username') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="username"
                                                       value="{{ $clientData->username }}"
                                                       placeholder="Enter Username">
                                                <span class="text-danger">{{ $errors->first('username') }}</span>
                                            </div>
                                        </div>

                                        {{--<div class="form-group row">--}}
                                        {{--<label class="col-4 col-form-label">Email</label>--}}
                                        {{--<div class="col-8 {{ $errors->has('email') ? 'has-error' : '' }}">--}}
                                        {{--<input type="email" class="form-control" name="email"--}}
                                        {{--value="{{ $clientData->email }}"--}}
                                        {{--placeholder="Enter Client Email Address">--}}
                                        {{--<span class="text-danger">{{ $errors->first('email') }}</span>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Client Login Password</label>
                                            <div class="col-8 {{ $errors->has('password') ? 'has-error' : '' }}">
                                                <input type="password" class="form-control" name="password" value=""
                                                       placeholder="Enter New Password">
                                                <span class="text-danger">{{ $errors->first('password') }}</span>
                                            </div>
                                        </div>

                                        {{--<div class="form-group row">--}}
                                        {{--<label class="col-4 col-form-label">Confirm Password</label>--}}
                                        {{--<div class="col-8 {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">--}}
                                        {{--<input type="password" class="form-control" name="password_confirmation" value="" placeholder="Confirm New Password">--}}
                                        {{--<span class="text-danger">{{ $errors->first('password_confirmation') }}</span>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Phone Number</label>
                                            <div class="col-8 {{ $errors->has('phone') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="phone"
                                                       value="{{ $clientData->phone }}"
                                                       placeholder="Enter Phone Number">
                                                <span class="text-danger">{{ $errors->first('phone') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Client NID</label>
                                            <div class="col-8 {{ $errors->has('clientNid') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="clientNid"
                                                       value="{{ $clientData->clientNid }}"
                                                       placeholder="Enter NID Number">
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
                                                    <input type="text" class="form-control" name="discount"
                                                           value="{{ $clientData->discount }}"
                                                           placeholder="Enter Discount"  >
                                                    <span class="text-danger">{{ $errors->first('discount') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Extra Charge</label>
                                                <div class="col-8 {{ $errors->has('charge') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="charge"
                                                           value="{{ $clientData->charge }}"
                                                           placeholder="Enter Extra Charge"  >
                                                    <span class="text-danger">{{ $errors->first('charge') }}</span>
                                                </div>
                                            </div>

                                        @else
                                            <input type="hidden" name="charge" value="{{ $clientData->charge }}">
                                            <input type="hidden" name="discount" value="{{ $clientData->discount }}">

                                        @endif

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">OTC Fee</label>
                                            <div class="col-8 {{ $errors->has('otc_charge') ? 'has-error' : '' }}">
                                                <input type="text" class="form-control" name="otc_charge"
                                                       value="{{ $clientData->otc_charge }}"
                                                       placeholder="Enter OTC Fee"  >
                                                <span class="text-danger">{{ $errors->first('otc_charge') }}</span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Distribution <span class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('distribution') ? 'has-error' : '' }}">
                                                <select class="form-control" name="distribution" required>
                                                    <option value="">Select Distribution</option>
                                                    @foreach($distributions as $distribution)
                                                        <option value="{{ $distribution->id}}" {{ ($clientData->distribution_id == $distribution->id) ? 'selected':'' }}>{{$distribution->distribution}}</option>
                                                    @endforeach
                                                </select>
                                                <span class="text-danger">{{ $errors->first('distribution') }}</span>
                                            </div>
                                        </div>

                                    </div><!-- end col -->

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">
                                                Address Area <span class="text-warning">*</span>
                                                <button type="button" class="btn btn-link text-info">
                                                        <span data-toggle="collapse" data-target="#collapseOne"
                                                              aria-expanded="false" aria-controls="collapseOne">
                                                            <i class="fa fa-plus"></i> Edit Details
                                                        </span>
                                                </button>
                                            </label>
                                            <div class="col-8 {{ $errors->has('address') ? 'has-error' : '' }}">
                                                <textarea type="text" class="form-control" name="address"
                                                          style="min-height: 50px;" rows="1" cols="1"
                                                          placeholder="Enter Address Area"
                                                          required>{{ $clientData->address }}</textarea>
                                                <span class="text-danger">{{ $errors->first('address') }}</span>
                                            </div>
                                        </div>

                                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                             data-parent="#accordion">

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">House Number</label>
                                                <div class="col-8 {{ $errors->has('house_no') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="house_no"
                                                           value="{{ $clientData->house_no }}"
                                                           placeholder="Enter House Number">
                                                    <span class="text-danger">{{ $errors->first('house_no') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Road Number</label>
                                                <div class="col-8 {{ $errors->has('road_no') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="road_no"
                                                           value="{{ $clientData->road_no }}"
                                                           placeholder="Enter Road Number">
                                                    <span class="text-danger">{{ $errors->first('road_no') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Thana</label>
                                                <div class="col-8 {{ $errors->has('thana') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="thana"
                                                           value="{{ $clientData->thana }}" placeholder="Enter Thana">
                                                    <span class="text-danger">{{ $errors->first('thana') }}</span>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">District</label>
                                                <div class="col-8 {{ $errors->has('district') ? 'has-error' : '' }}">
                                                    <input type="text" class="form-control" name="district"
                                                           value="{{ $clientData->district }}"
                                                           placeholder="Enter District">
                                                    <span class="text-danger">{{ $errors->first('district') }}</span>
                                                </div>
                                            </div>


                                            <style>

                                                #other-documents-container {
                                                    display: flex;
                                                    flex-wrap: wrap;
                                                    gap: 10px;
                                                    align-items: flex-start;
                                                }

                                                #other-documents-container label.add-new-btn {

                                                    flex-shrink: 0;     /* prevents shrinking */
                                                    order: 999;         /* ensures it appears last in flex order */
                                                }

                                                /* Container flex for previews */
                                                #client-photo-container,
                                                #other-documents-container {
                                                    display: flex;
                                                    align-items: flex-start;
                                                    gap: 10px;
                                                    flex-wrap: wrap;
                                                }

                                                /* Thumbnail preview box */
                                                .thumb-preview {
                                                    position: relative;
                                                    width: 100px;  /* slightly wider for label */
                                                    height: 110px; /* enough height for label */
                                                    flex-shrink: 0; /* prevent shrinking */
                                                    display: flex;
                                                    flex-direction: column;
                                                    align-items: center;
                                                    justify-content: flex-start;
                                                    border: 1px solid #ddd;
                                                    border-radius: 6px;
                                                    overflow: hidden;
                                                    background: #f9f9f9;
                                                    padding: 4px;
                                                }

                                                .thumb-preview img {
                                                    width: 90px;
                                                    height: 70px;
                                                    object-fit: cover;
                                                    border-radius: 4px;
                                                    border: 1px solid #ddd;
                                                }

                                                .thumb-label {
                                                    margin-top: 6px;
                                                    width: 90px;
                                                    font-size: 12px;
                                                    white-space: nowrap;
                                                    overflow: hidden;
                                                    text-overflow: ellipsis;
                                                    text-align: center;
                                                }

                                                .remove-btn {
                                                    position: absolute;
                                                    top: 4px;
                                                    right: 4px;
                                                    background: rgba(0,0,0,0.6);
                                                    color: white;
                                                    border: none;
                                                    border-radius: 50%;
                                                    width: 18px;
                                                    height: 18px;
                                                    cursor: pointer;
                                                    font-weight: bold;
                                                    line-height: 18px;
                                                    text-align: center;
                                                    z-index: 10;
                                                }

                                                /* Add new file button style */
                                                .add-new-btn {
                                                    background: #28a745;
                                                    color: #fff;
                                                    width: 36px;
                                                    height: 36px;
                                                    border-radius: 50%;
                                                    font-size: 28px;
                                                    cursor: pointer;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                    user-select: none;
                                                }

                                                /* Hide native file inputs */
                                                .hidden-input {
                                                    display: none;
                                                }


                                            </style>

                                            <!-- Client Photo -->
                                            <div class="form-group">
                                                <label>Client Photo</label>
                                                <div id="client-photo-container">
                                                    @if ($clientData->client_photo)
                                                        <div class="thumb-preview" data-type="client_photo" id="client-photo-preview">
                                                            <img src="{{ asset('assets/uploads/client_photos/' . $clientData->client_photo) }}" alt="Client Photo">

                                                            <a href="{{ asset('assets/uploads/client_photos/' . $clientData->client_photo) }}" target="_blank" class="thumb-label">{{ $clientData->client_photo }}</a>

                                                            <button type="button" class="remove-btn" title="Remove Client Photo" data-checkbox-id="remove_client_photo_checkbox">×</button>
                                                            <input type="checkbox" name="remove_client_photo" id="remove_client_photo_checkbox" class="remove-checkbox" value="1" style="display:none;">
                                                        </div>
                                                    @endif

                                                    <!-- New client photo preview -->
                                                    <div id="new-client-photo-preview"></div>

                                                    <input type="file" name="client_photo" accept="image/*" id="client_photo_input" class="hidden-input" />

                                                    <label for="client_photo_input" class="add-new-btn" title="Add New Client Photo">+</label>
                                                </div>
                                                <span class="text-danger">{{ $errors->first('client_photo') }}</span>
                                            </div>

                                            <!-- Other Documents -->
                                            <div class="form-group">
                                                <label>Other Documents</label>
                                                <div id="other-documents-container">
                                                    @php
                                                        $existingDocs = is_array($clientData->other_documents)
                                                            ? $clientData->other_documents
                                                            : json_decode($clientData->other_documents, true);
                                                    @endphp

                                                    @if (!empty($existingDocs))
                                                        @foreach ($existingDocs as $index => $document)
                                                            <div class="thumb-preview" data-index="{{ $index }}" data-type="other_documents" id="doc-preview-{{ $index }}">
                                                                @php $ext = pathinfo($document, PATHINFO_EXTENSION); @endphp

                                                                @if (in_array(strtolower($ext), ['jpg','jpeg','png','gif','bmp','webp']))
                                                                    <img src="{{ asset('assets/uploads/client_documents/' . $document) }}" alt="Document Preview">
                                                                @else
                                                                    <img src="{{ asset('assets/images/file-icon.png') }}" alt="File Icon" style="width: 70px; height: 90px; object-fit: contain;">
                                                                @endif

                                                                <a href="{{ asset('assets/uploads/client_documents/' . $document) }}" target="_blank" class="thumb-label">{{ basename($document) }}</a>

                                                                <button type="button" class="remove-btn" title="Remove Document" data-checkbox-id="remove_doc_checkbox_{{ $index }}">×</button>
                                                                <input type="checkbox" name="remove_documents[]" id="remove_doc_checkbox_{{ $index }}" class="remove-checkbox" value="{{ $index }}" style="display:none;">
                                                            </div>
                                                        @endforeach
                                                    @endif

                                                    <!-- Hidden file input and "+" button -->
                                                    <input type="file" name="other_documents[]" accept="image/*,application/pdf" multiple id="other_documents_input" class="hidden-input" />
                                                    <label for="other_documents_input" class="add-new-btn" title="Add New Documents">+</label>
                                                </div>
                                                <span class="text-danger">{{ $errors->first('other_documents') }}</span>
                                            </div>

                                            <script>
                                                // ---- Existing photo remove toggle ----
                                                document.querySelectorAll('.remove-btn').forEach(button => {
                                                    button.addEventListener('click', function () {
                                                        const checkboxId = this.getAttribute('data-checkbox-id');
                                                        const checkbox = document.getElementById(checkboxId);
                                                        if (checkbox) {
                                                            checkbox.checked = !checkbox.checked;
                                                            const preview = this.closest('.thumb-preview');
                                                            preview.style.opacity = checkbox.checked ? '0.4' : '1';
                                                        }
                                                    });
                                                });

                                                // ---- Client photo (single) new upload ----
                                                const clientPhotoInput = document.getElementById('client_photo_input');
                                                const clientPhotoPreview = document.getElementById('new-client-photo-preview');
                                                const existingClientPhoto = document.getElementById('client-photo-preview');

                                                let newClientPhotoFile = null;

                                                clientPhotoInput.addEventListener('change', () => {
                                                    const file = clientPhotoInput.files[0];
                                                    if (!file || !file.type.startsWith('image/')) return;

                                                    newClientPhotoFile = file;

                                                    // Remove existing preview
                                                    if (existingClientPhoto) {
                                                        existingClientPhoto.style.display = 'none';
                                                    }

                                                    clientPhotoPreview.innerHTML = ''; // Clear any new previews

                                                    const div = document.createElement('div');
                                                    div.classList.add('thumb-preview');

                                                    const img = document.createElement('img');
                                                    img.src = URL.createObjectURL(file);
                                                    img.style.width = '90px';
                                                    img.style.height = '90px';
                                                    img.style.objectFit = 'cover';
                                                    img.style.borderRadius = '6px';
                                                    img.style.border = '1px solid #ddd';

                                                    const btn = document.createElement('button');
                                                    btn.type = 'button';
                                                    btn.className = 'remove-btn';
                                                    btn.title = 'Remove new photo';
                                                    btn.textContent = '×';
                                                    btn.onclick = () => {
                                                        newClientPhotoFile = null;
                                                        clientPhotoInput.value = '';
                                                        clientPhotoPreview.innerHTML = '';

                                                        // Restore old preview if available
                                                        if (existingClientPhoto) {
                                                            existingClientPhoto.style.display = 'flex';
                                                        }
                                                    };

                                                    div.appendChild(img);
                                                    div.appendChild(btn);
                                                    clientPhotoPreview.appendChild(div);
                                                });

                                                // ---- Other documents (multiple) ----
                                                const otherDocsInput = document.getElementById('other_documents_input');
                                                const otherDocsContainer = document.getElementById('other-documents-container');
                                                let otherDocsFiles = [];

                                                otherDocsInput.addEventListener('change', () => {
                                                    for (const file of otherDocsInput.files) {
                                                        otherDocsFiles.push(file);
                                                    }
                                                    updateOtherDocsPreview();
                                                    updateOtherDocsInputFiles();
                                                });

                                                function updateOtherDocsPreview() {
                                                    otherDocsContainer.querySelectorAll('.thumb-preview[data-new="true"]').forEach(el => el.remove());

                                                    otherDocsFiles.forEach((file, idx) => {
                                                        const div = document.createElement('div');
                                                        div.classList.add('thumb-preview');
                                                        div.setAttribute('data-new', 'true');

                                                        const ext = file.name.split('.').pop().toLowerCase();

                                                        if (['jpg','jpeg','png','gif','bmp','webp'].includes(ext)) {
                                                            const img = document.createElement('img');
                                                            img.src = URL.createObjectURL(file);
                                                            img.style.width = '90px';
                                                            img.style.height = '70px';
                                                            img.style.objectFit = 'cover';
                                                            img.style.borderRadius = '6px';
                                                            img.style.border = '1px solid #ddd';
                                                            div.appendChild(img);
                                                        } else {
                                                            const icon = document.createElement('img');
                                                            icon.src = '{{ asset("assets/images/file-icon.png") }}';
                                                            icon.style.width = '70px';
                                                            icon.style.height = '90px';
                                                            icon.style.objectFit = 'contain';
                                                            div.appendChild(icon);
                                                        }

                                                        const label = document.createElement('div');
                                                        label.classList.add('thumb-label');
                                                        label.textContent = file.name;
                                                        div.appendChild(label);

                                                        const btn = document.createElement('button');
                                                        btn.type = 'button';
                                                        btn.className = 'remove-btn';
                                                        btn.title = 'Remove this file';
                                                        btn.textContent = '×';
                                                        btn.style.position = 'absolute';
                                                        btn.style.top = '3px';
                                                        btn.style.right = '3px';

                                                        btn.onclick = () => {
                                                            otherDocsFiles.splice(idx, 1);
                                                            updateOtherDocsPreview();
                                                            updateOtherDocsInputFiles();
                                                        };

                                                        div.appendChild(btn);
                                                        otherDocsContainer.appendChild(div);
                                                    });
                                                }

                                                function updateOtherDocsInputFiles() {
                                                    const dataTransfer = new DataTransfer();
                                                    otherDocsFiles.forEach(file => dataTransfer.items.add(file));
                                                    otherDocsInput.files = dataTransfer.files;
                                                }

                                                // Ensure file list is synced on submit
                                                document.querySelector('form').addEventListener('submit', function () {
                                                    updateOtherDocsInputFiles();
                                                });
                                            </script>



                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Note</label>
                                            <div class="col-8 {{ $errors->has('note') ? 'has-error' : '' }}">
                                                <textarea type="text" class="form-control" name="note"
                                                          style="min-height: 50px;" rows="1" cols="1"
                                                          placeholder="Additional Information">{{ $clientData->note }}</textarea>
                                                <span class="text-danger">{{ $errors->first('note') }}</span>
                                            </div>
                                        </div>


                                        {{--@role('Reseller')--}}
                                        {{--<input type="hidden" name="plan_id" value="{{ $clientData->plan_id }}">--}}
                                        {{--<input type="hidden" name="type" value="{{ $clientPlanData->type }}">--}}
                                        {{--<div class="form-group row">--}}
                                        {{--<label class="col-2 col-form-label">Plan Name</label>--}}
                                        {{--<div class="col-10 {{ $errors->has('plan_id') ? 'has-error' : '' }}">--}}
                                        {{--<select class="form-control" id="plan_resell" name="plan_id">--}}
                                        {{--<option value="">Select Plan Name</option>--}}
                                        {{--@foreach($planData as $plan)--}}
                                        {{--<option value="{{$plan['id']}}" {{ ($clientData->plan_id == $plan->id) ? 'selected':'' }}>{{$plan['plan_name']}}</option>--}}
                                        {{--@endforeach--}}
                                        {{--</select>--}}
                                        {{--<span class="text-danger">{{ $errors->first('plan_id') }}</span>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}
                                        {{--@else--}}

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">Connection Type <span
                                                        class="text-warning">*</span></label>
                                            <div class="col-8">
                                                <select class="form-control" id="cable_type" name="cable_type">
                                                    <option value="{{$clientData->cable_type}}">Select Cable Type
                                                    </option>
                                                    <option value="UTP" {{ ( $clientData->cable_type == 'UTP') ? 'selected':'' }}>
                                                        UTP
                                                    </option>
                                                    <option value="Fiber" {{ ($clientData->cable_type == 'Fiber') ? 'selected':'' }}>
                                                        Fiber
                                                    </option>
                                                    <option value="Wireless" {{ ($clientData->cable_type == 'Wireless ') ? 'selected':'' }}>
                                                        Wireless
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-4 col-form-label">OLT Type <span class="text-warning">*</span></label>
                                            <div class="col-8 {{ $errors->has('olt_type') ? 'has-error' : '' }}">
                                                <select class="form-control" name="olt_type" required>
                                                    <option value="">Select OLT</option>
                                                    <option value="Solitine"{{ ( $clientData->olt_type == 'Solitine') ? 'selected':'' }}>Solitine</option>
                                                    <option value="VSOL"{{ ( $clientData->olt_type == 'VSOL') ? 'selected':'' }}>VSOL</option>
                                                    <option value="BDCOM"{{ ( $clientData->olt_type == 'BDCOM') ? 'selected':'' }}>BDCOM</option>
                                                    <option value="DBC"{{ ( $clientData->olt_type == 'DBC') ? 'selected':'' }}>DBC</option>
                                                    <option value="HSGQ"{{ ( $clientData->olt_type == 'HSGQ') ? 'selected':'' }}>HSGQ</option>
                                                    <option value="CORELINK"{{ ( $clientData->olt_type == 'CORELINK') ? 'selected':'' }}>CORE LINK</option>
                                                    <option value="WISEE"{{ ( $clientData->olt_type == 'WISEE') ? 'selected':'' }}>WISEE</option>
                                                    <option value="TBS"{{ ( $clientData->olt_type == 'TBS') ? 'selected':'' }}>TBS</option>
                                                </select>
                                                <span class="text-danger">{{ $errors->first('olt_type') }}</span>
                                            </div>
                                        </div>


                                    @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_change plan'))
                                            @if($user->hasRole('Super-Admin') || $user->can('client_change plan'))
                                                <div class="form-group row">

                                                    <label class="col-4 col-form-label">Service Type <span
                                                                class="text-warning">*</span></label>
                                                    <div class="col-8 {{ $errors->has('type') ? 'has-error' : '' }}">
                                                        <select class="form-control" id="type" name="type">
                                                            <option value="">Select Plane Type</option>
                                                            <option value="PPPOE" {{ ($clientPlanData->type == 'PPPOE') ? 'selected':'' }}>
                                                                PPPOE
                                                            </option>
                                                            <option value="Hotspot" {{ ($clientPlanData->type == 'Hotspot') ? 'selected':'' }}>
                                                                Hotspot
                                                            </option>
                                                            <option value="IP" {{ ($clientPlanData->type == 'IP') ? 'selected':'' }}>
                                                                IP
                                                            </option>
                                                        </select>
                                                        <span class="text-danger">{{ $errors->first('type') }}</span>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-4 col-form-label">Server Name <span
                                                                class="text-warning">*</span></label>
                                                    <div class="col-8 {{ $errors->has('server_id') ? 'has-error' : '' }}">
                                                        <select class="form-control" id="server" name="server_id">
                                                            <option value="">Select Server Name</option>
                                                            @foreach($serverData as $server)
                                                                <option value="{{$server['id']}}" {{ ($clientPlanData->server->id == $server->id) ? 'selected':'' }}>{{$server['server_name']}}</option>
                                                            @endforeach
                                                        </select>
                                                        <span class="text-danger">{{ $errors->first('server_id') }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group row">
                                                <label class="col-4 col-form-label">Plan Name <span
                                                            class="text-warning">*</span></label>
                                                <div class="col-8 {{ $errors->has('plan_id') ? 'has-error' : '' }}">
                                                    <select class="form-control" id="plan" name="plan_id">
                                                        <option value="">Select Plan Name</option>
                                                        @foreach($planData as $plan)
                                                            <option value="{{$plan['id']}}" {{ ($clientData->plan_id == $plan->id) ? 'selected':'' }}>{{$plan['plan_name']}}</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-danger">{{ $errors->first('plan_id') }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        @if($clientPlanData->type == 'IP')
                                            <div style="display:none;" id="server_password">
                                                @else
                                                    <div id="server_password">
                                                        @endif
                                                        <div class="form-group row">
                                                            <label class="col-4 col-form-label">Router Password <span
                                                                        class="text-warning">*</span></label>
                                                            <div class="col-8 {{ $errors->has('server_password') ? 'has-error' : '' }}">
                                                                <input type="text" class="form-control"
                                                                       name="server_password"
                                                                       value="{{ $clientData->server_password }}"
                                                                       placeholder="Enter Router Password">
                                                                <span class="text-danger">{{ $errors->first('server_password') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($clientPlanData->type == 'IP')
                                                        <div class="form-group row" id="client_ip">
                                                            @else
                                                                <div class="form-group row" style="display:none;"
                                                                     id="client_ip">
                                                                    @endif
                                                                    <label class="col-4 col-form-label">Client IP <span
                                                                                class="text-warning">*</span></label>
                                                                    <div class="col-8 {{ $errors->has('client_ip') ? 'has-error' : '' }}">
                                                                        <input type="text" class="form-control"
                                                                               id="c_ip"
                                                                               name="client_ip"
                                                                               value="{{ $clientData->client_ip }}"
                                                                               placeholder="Enter Client IP">
                                                                        <span class="text-danger">{{ $errors->first('client_ip') }}</span>
                                                                    </div>
                                                                </div>




                                                                {{--<div class="form-group row">--}}
                                                                {{--<label class="col-4 col-form-label">Branch</label>--}}
                                                                {{--<div class="col-8 {{ $errors->has('branchId') ? 'has-error' : '' }}">--}}
                                                                {{--<select class="form-control" name="branchId">--}}
                                                                {{--<option value="">Select Branch</option>--}}
                                                                {{--@foreach($branches as $branch)--}}
                                                                {{--<option value="{{ $branch->branchId}}" {{ ($clientData->branchId == $branch->branchId) ? 'selected':'' }}>{{$branch->branchName}}</option>--}}
                                                                {{--@endforeach--}}
                                                                {{--</select>--}}
                                                                {{--<span class="text-danger">{{ $errors->first('branchId') }}</span>--}}
                                                                {{--</div>--}}
                                                                {{--</div>--}}
                                                        </div><!-- end col -->
                                            </div><!-- end row -->


                                            {{--<div class="form-group mb-0 justify-content-end row">--}}
                                            {{--<div class="col-10">--}}
                                            {{--<h4>Service Information</h4>--}}
                                            {{--</div>--}}
                                            {{--</div>--}}
                                            <input type="hidden" name="type_of_connection" value="Wired">
                                            <input type="hidden" name="type_of_connectivity" value="Shared">

                                            {{--<div class="form-group row">--}}
                                            {{--<label class="col-2 col-form-label">Connection Type</label>--}}
                                            {{--<div class="col-10 {{ $errors->has('type_of_connection') ? 'has-error' : '' }}">--}}
                                            {{--<select class="form-control" name="type_of_connection">--}}
                                            {{--<option value="">Select Connection Type</option>--}}
                                            {{--<option value="Wired" {{ ($clientData->type_of_connection == 'Wired') ? 'selected':'' }}>--}}
                                            {{--Wired--}}
                                            {{--</option>--}}
                                            {{--<option value="Wireless" {{ ($clientData->type_of_connection == 'Wireless') ? 'selected':'' }}>--}}
                                            {{--Wireless--}}
                                            {{--</option>--}}
                                            {{--</select>--}}
                                            {{--<span class="text-danger">{{ $errors->first('type_of_connection') }}</span>--}}
                                            {{--</div>--}}
                                            {{--</div>--}}

                                            {{--<div class="form-group row">--}}
                                            {{--<label class="col-2 col-form-label">Connectivity Type</label>--}}
                                            {{--<div class="col-10 {{ $errors->has('type_of_connectivity') ? 'has-error' : '' }}">--}}
                                            {{--<select class="form-control" name="type_of_connectivity">--}}
                                            {{--<option value="">Select Connectivity Type</option>--}}
                                            {{--<option value="Shared" {{ ($clientData->type_of_connectivity == 'Shared') ? 'selected':'' }}>--}}
                                            {{--Shared--}}
                                            {{--</option>--}}
                                            {{--<option value="Dedicate" {{ ($clientData->type_of_connectivity == 'Dedicate') ? 'selected':'' }}>--}}
                                            {{--Dedicate--}}
                                            {{--</option>--}}
                                            {{--</select>--}}
                                            {{--<span class="text-danger">{{ $errors->first('type_of_connectivity') }}</span>--}}
                                            {{--</div>--}}
                                            {{--</div>--}}

                                            <div class="form-group mt-3 justify-content-center row">
                                                <div class="col-4 offset-3">
                                                    <button type="submit"
                                                            class="btn btn-info waves-effect waves-light">
                                                        Edit Client
                                                    </button>
                                                    <a href="{{ route('client.index') }}"
                                                       class="btn btn-secondary">Cancel</a>
                                                </div>
                                            </div>

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
