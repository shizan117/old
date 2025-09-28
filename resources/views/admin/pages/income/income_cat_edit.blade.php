@extends ('admin.layouts.master')
@section('title')
    Edit Income Category
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box">

                    <div class="row">
                        <div class="col-12">
                            <div class="p-20">
                                <form class="form-horizontal" role="form" action="{{ route('income.cat.edit.post', $cat->id) }}" method="POST">

                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <label class="col-3 col-form-label">Income Category Name</label>
                                        <div class="col-9 {{ $errors->has('income_category_name') ? 'has-error' : '' }}">
                                            <input type="text" class="form-control" name="income_category_name" value="{{ $cat->name }}" placeholder="Enter Income Category Name">
                                            <span class="text-danger">{{ $errors->first('income_category_name') }}</span>
                                        </div>
                                    </div>

                                   <div class="form-group mb-0 justify-content-end row">
                                        <div class="col-9">
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Update Category</button>
                                            <a href="{{ route('income.cat.list') }}" class="btn btn-secondary">Cancel</a>
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
