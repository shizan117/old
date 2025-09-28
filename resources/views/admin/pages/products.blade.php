@extends('admin.layouts.master')
@section('title')
    Products
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">

            <div class="row">
                <div class="col-md-8">
                    <div class="card-box">

                        <h4 class="pull-left header-title mt-0 m-b-30">Products</h4>
                        <div class="pull-right btn-group m-b-10">
                            @if($role_id == 1 || $role_id == 2 || $role_id == 5)
                                <a href="{{ route('product.index') }}" class="btn btn-info">Products</a>
{{--                                <a href="{{ route('product.branch.index') }}" class="btn btn-secondary">Branches Products</a>--}}
                            @endif
                            @if($role_id == 1 || $role_id == 2 || $role_id == 4 || $role_id == 5)
                                <a href="{{ route('product.add') }}" class="btn btn-primary">Add New Product</a>
                            @endif
                        </div>


                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    {{--@if($admin && $role_id != 4)--}}
                                        {{--<th>Branch Name</th>--}}
                                    {{--@endif--}}
                                    @if($role_id == 1 or $role_id == 2 or $role_id == 4)
                                        <th>Manage</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @php($i = 0)
                                @foreach ($products as $product)
                                    @php($i = $i+1)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->productCategory->name }}</td>
                                        <td>{{ $product->unit }}</td>
                                        {{--@if($admin && $role_id != 4)--}}
                                            {{--@php(($product->branchId == null ) ? $branch_name = '-' : $branch_name = $product->branch->branchName)--}}
                                            {{--<td>{{ $branch_name }}</td>--}}
                                        {{--@endif--}}
                                        @if($role_id == 1 or $role_id == 2 or $role_id == 4)
                                            <td class="text-center">
                                                <a href="{{ route('product.edit', $product->id) }}"
                                                   class="btn-edit"><i class="fa fa-edit"></i></a>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- end col -->

                <div class="col-md-4">
                    <div class="card-box">

                        <h4 class="header-title mt-0 m-b-30">Products Categories</h4>
                        @if($role_id == 1 or $role_id == 2 or $role_id == 4)
                            <div class="btn-group m-b-10">
                                <a href="{{ route('product.cat.add') }}" class="btn btn-primary">Add Category</a>
                            </div>

                        @endif

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                <tr class="text-center">
                                    <th>#</th>
                                    <th>Category Name</th>
                                    @if($role_id == 1 or $role_id == 2 or $role_id == 4)
                                        <th>Manage</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                @php($i = 0)
                                @foreach ($categories as $category)
                                    @php($i = $i+1)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $category->name }}</td>
                                        @if($role_id == 1 or $role_id == 2 or $role_id == 4)
                                            <td class="text-center">
                                                <a href="{{ route('product.cat.edit', $category->id) }}"
                                                   class="btn-small btn-edit"><i class="fa fa-edit"></i></a>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div><!-- end col -->
            </div>
        </div><!-- end col -->
    </div>
    <!-- end row -->



@endsection
