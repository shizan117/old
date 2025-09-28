@extends ('admin.layouts.master')
@section('title')
    Edit Product
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">

                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" role="form" action="{{ route('product.cat.edit.post', $product_cat->id) }}"
                                  method="POST">
                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Product Category Name</label>
                                    <div class="col-10 {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="name" value="{{ $product_cat->name }}"
                                               placeholder="Enter Product Category Name">
                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="resellerId" value="{{ Auth::user()->resellerId }}">
                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" class="btn btn-info waves-effect waves-light">Update
                                            Product Category
                                        </button>
                                        <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancel</a>
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