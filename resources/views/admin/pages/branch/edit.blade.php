@extends ('admin.layouts.master')
@section('title')
    Edit Branch
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">


                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('branch.edit.post', $data->branchId) }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Branch Name</label>
                                        <div class="col-10 {{ $errors->has('branchName') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="branchName" value="{{ $data->branchName }}" placeholder="Enter Branch Name">
                                            <span class="text-danger">{{ $errors->first('branchName') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-2 col-form-label">Branch Location</label>
                                        <div class="col-10 {{ $errors->has('branchLocation') ? 'has-error' : '' }}">
                                            <textarea class="form-control" name="branchLocation">{{ $data->branchLocation }}</textarea>
                                            <span class="text-danger">{{ $errors->first('branchLocation') }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-10">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Update Branch</button>
                                            <a href="{{ route('branch.index') }}" class="btn btn-secondary">Cancel</a>
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