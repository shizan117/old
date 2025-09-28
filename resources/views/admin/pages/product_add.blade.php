@extends ('admin.layouts.master')
@section('title')
    Add Product
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">

                <div class="row">
                    <div class="col-12">
                        <div class="p-20">
                            <form class="form-horizontal" role="form" action="{{ route('product.add.post') }}"
                                  method="POST">
                                {{ csrf_field() }}
                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Product Category</label>
                                    <div class="col-10 {{ $errors->has('category') ? 'has-error' : '' }}">
                                        <select class="form-control" name="category">
                                            <option value="">Select Product Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{$category['id']}}" {{ (collect(old('category'))->contains($category['id'])) ? 'selected':'' }}>{{$category['name']}}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('category') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Product Name</label>
                                    <div class="col-10 {{ $errors->has('name') ? 'has-error' : '' }}">
                                        <input type="text" class="form-control" name="name" value=""
                                               placeholder="Enter Product Name">
                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                    </div>
                                </div>



                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Unit</label>
                                    <div class="col-10 {{ $errors->has('unit') ? 'has-error' : '' }}">
                                        <select class="form-control" name="unit">
                                            <option value="">Select Unit</option>
                                            <option value="Kg" {{ (collect(old('unit'))->contains('Kg')) ? 'selected':'' }}>Kg</option>
                                            <option value="Meter" {{ (collect(old('unit'))->contains('Meter')) ? 'selected':'' }}>Meter</option>
                                            <option value="Piece" {{ (collect(old('unit'))->contains('Piece')) ? 'selected':'' }}>Piece</option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('unit') }}</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-2 col-form-label">Single Unit Serial</label>
                                    <div class="col-10 {{ $errors->has('single_unit_serial') ? 'has-error' : '' }}">
                                        <select class="form-control" name="single_unit_serial">
                                            <option value="">Select</option>
                                            <option value="1" {{ (collect(old('single_unit_serial'))->contains(1)) ? 'selected':'' }}>Yes</option>
                                            <option value="2" {{ (collect(old('single_unit_serial'))->contains(2)) ? 'selected':'' }}>No</option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('single_unit_serial') }}</span>
                                    </div>
                                </div>

                                <input type="hidden" name="resellerId" value="{{ Auth::user()->resellerId }}">

                                {{--@if($role_id != 4)--}}
                                {{--<div class="form-group row">--}}
                                    {{--<label class="col-2 col-form-label">Branch</label>--}}
                                    {{--<div class="col-10 {{ $errors->has('branch') ? 'has-error' : '' }}">--}}
                                        {{--<select class="form-control" name="branch">--}}
                                            {{--<option value="">Select Branch</option>--}}
                                            {{--@foreach($branches as $branch)--}}
                                                {{--<option value="{{$branch['branchId']}}" {{ (collect(old('branch'))->contains($branch['branchId'])) ? 'selected':'' }}>{{$branch['branchName']}}</option>--}}
                                            {{--@endforeach--}}
                                        {{--</select>--}}
                                        {{--<span class="text-danger">{{ $errors->first('branch') }}</span>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--@endif--}}



                                <div class="form-group mb-0 justify-content-end row">
                                    <div class="col-10">
                                        <button type="submit" class="btn btn-info waves-effect waves-light">Add
                                            Product
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