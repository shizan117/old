@extends ('admin.layouts.master')
@section('title')
    Add Distribution Area
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">

                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('distribution.add.post') }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Distribution Area/Box</label>
                                        <div class="col-9 {{ $errors->has('distribution') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="distribution" value="" placeholder="Enter Distribution Area/Box">
                                            <span class="text-danger">{{ $errors->first('distribution') }}</span>
                                        </div>
                                    </div>

                                   <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-9">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Add Distribution</button>
                                            <a href="{{ route('distribution.index') }}" class="btn btn-secondary">Cancel</a>
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