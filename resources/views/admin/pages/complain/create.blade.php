<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title','New Complain')

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">

                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('complain.store') }}" method="POST">
                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <div class="col-md-4 {{ $errors->has('client_id') ? 'has-error' : '' }}">
                                            <label class="col-form-label">Client Name</label>
                                            <select class="form-control select2" name="client_id" id="client">
                                                <option value="">Select Client Name</option>
                                                @foreach($clients as $client)
                                                    <option value="{{$client['id']}}">{{$client['client_name'].'-'.$client['username']}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('client_id') }}</span>
                                        </div>
	                                    @php($c_titles=['Line Slow','Router Disconnect','Router Signal Red','Router Signal Yellow','Connection Disconnect',
	                                    'Computer Red Signal','Computer Yellow Signal','Re-Connection Request','House/Flat Shifting','Disconnect Request',
	                                    'Package Change','Others'])
                                        <div class="col-md-4 {{ $errors->has('title') ? 'has-error' : '' }}">
                                            <label class="col-form-label">Ticket Type</label>
                                            <select class="form-control" name="title" id="title">
                                                <option value="">Select Ticket Type</option>
                                                @foreach($c_titles as $title)
                                                    <option value="{{$title}}">{{$title}}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('title') }}</span>
                                        </div>

                                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('ticket_assign ticket'))
                                        <div class="col-md-4 {{ $errors->has('assign_to') ? 'has-error' : '' }}">
                                            <label class="col-form-label">Assign To</label>
                                            <select class="form-control select2" name="assign_to" id="client">
                                                <option value="">Select User</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger">{{ $errors->first('assign_to') }}</span>
                                        </div>
                                        @else
                                            <input type="hidden" name="assign_to" value="{{ Auth::user()->id }}">
                                        @endif
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 {{ $errors->has('description') ? 'has-error' : '' }}">
                                            <label class="col-form-label">Description</label>
                                            <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                                            <span class="text-danger">{{ $errors->first('description') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12 {{ $errors->has('action_taken') ? 'has-error' : '' }}">
                                            <label class="col-form-label">Action Taken</label>
                                            <textarea class="form-control" name="action_taken">{{ old('action_taken') }}</textarea>
                                            <span class="text-danger">{{ $errors->first('action_taken') }}</span>
                                        </div>
                                    </div>

                                   <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Complain</button>
                                            <a href="{{ route('complain.pending') }}" class="btn btn-secondary">Cancel</a>
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
            // Select2
            $(".select2").select2();

        });

    </script>
@endsection
