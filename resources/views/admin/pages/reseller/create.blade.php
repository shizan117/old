@extends ('admin.layouts.master')
@section('title')
    Add New Reseller
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('reseller.add.post') }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Reseller Name</label>
                                        <div class="col-10 {{ $errors->has('resellerName') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="resellerName" value="" placeholder="Enter Reseller Name">
                                            <span class="text-danger">{{ $errors->first('resellerName') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Reseller Location</label>
                                        <div class="col-10 {{ $errors->has('resellerLocation') ? 'has-error' : '' }}">
                                            <textarea class="form-control" name="resellerLocation" ></textarea>
                                            <span class="text-danger">{{ $errors->first('resellerLocation') }}</span>
                                        </div>
                                    </div>

                                    @if($setting['invoice_system'] == 'fixed')
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Client Expire Date</label>
                                        <div class="col-10 {{ $errors->has('c_exp_date') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="c_exp_date" placeholder="Client Expire Date">
                                            <span class="text-danger">{{ $errors->first('c_exp_date') }}</span>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Reseller</button>
                                            <a href="{{ route('reseller.index') }}" class="btn btn-secondary">Cancel</a>
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