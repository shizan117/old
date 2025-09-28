@extends ('admin.layouts.master')
@section('title')
    Edit Investor
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">

                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('investor.edit.post', $investor->id) }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Investor Name</label>
                                        <div class="col-9 {{ $errors->has('name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="name" value="{{ $investor->name }}" placeholder="Enter Investor Name">
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        </div>
                                    </div>

                                   <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-9">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Update</button>
                                            <a href="{{ route('investor.index') }}" class="btn btn-secondary">Cancel</a>
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