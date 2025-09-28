<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
Expense Category
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="m-t-0 header-title py-2 py-md-0">Expanse Category List</h4>
                        </div>
                        <div class="col-md-6 text-md-right text-center pb-3">
                            @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('expense_category add'))
                            <a href="{{ route('expanse.cat.add') }}" class="btn btn-primary">Add Expanse Category</a>
                            @endif
                        </div>
                    </div>
                    <table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Category Name</th>
                            <th class="hidden-print">Manage</th>
                        </tr>
                        </thead>


                        <tbody>
                        @foreach ($cats as $cat)
                        <tr>
                            <td></td>
                            <td>{{ $cat->name }}</td>
                            <td class="hidden-print">
                                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('expense_category edit'))
                                <a href="{{ route('expanse.cat.edit', $cat->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                @endif
                                {{--<a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a>--}}

                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- end row -->
        <!-- end row -->
@endsection

@section('custom_js')
    @include('admin.layouts.print-js')
@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
@endsection
@section('custom_css')
    <style>
        .dataTable > thead > tr > th[class*=sort]:after {
            display: none;
        }

        .dataTable > thead > tr > th[class*=sort]:before {
            display: none;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
@endsection
