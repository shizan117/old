@extends ('admin.layouts.master')
@section('title')
    Add New Pool
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-8">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('bandwidth.add.post') }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Bandwidth Name</label>
                                        <div class="col-9 {{ $errors->has('bandwidth_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="bandwidth_name" value="" placeholder="Enter Bandwidth Name">
                                            <span class="text-danger">{{ $errors->first('bandwidth_name') }}</span>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Rate Download</label>
                                        <div class="col-7 {{ $errors->has('rate_down') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="rate_down" value="" placeholder="Enter Download Rate">
                                            <span class="text-danger">{{ $errors->first('rate_down') }}</span>
                                        </div>
                                        <div class="col-2 {{ $errors->has('rate_down_unit') ? 'has-error' : '' }}">
                                            <select class="form-control" name="rate_down_unit">
                                                <option value="Mbps">Mbps</option>
                                                <option value="Kbps">Kbps</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('rate_down_unit') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Rate Upload</label>
                                        <div class="col-7 {{ $errors->has('rate_up') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="rate_up" value="" placeholder="Enter Upload Rate">
                                            <span class="text-danger">{{ $errors->first('rate_up') }}</span>
                                        </div>
                                        <div class="col-2 {{ $errors->has('rate_up_unit') ? 'has-error' : '' }}">
                                            <select class="form-control" name="rate_up_unit">
                                                <option value="Mbps">Mbps</option>
                                                <option value="Kbps">Kbps</option>
                                            </select>
                                            <span class="text-danger">{{ $errors->first('rate_up_unit') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Bandwidth Allocation MB</label>
                                        <div class="col-9 {{ $errors->has('bandwidth_allocation_mb') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="bandwidth_allocation_mb" value="" placeholder="Enter Bandwidth Allocation MB">
                                            <span class="text-danger">{{ $errors->first('bandwidth_allocation_mb') }}</span>
                                        </div>
                                    </div>



                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-9">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Bandwidth</button>
                                            <a href="{{ route('bandwidth.index') }}" class="btn btn-secondary">Cancel</a>
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