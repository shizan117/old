<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
    Distribution Area
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12 w-100 col-12">
            <div class="card-box table-responsive">

                @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('distribution_add'))
                <a href="{{ route('distribution.add') }}" class="btn btn-primary mb-2 mb-md-0"
                        style="display: inline-block; margin-bottom: 5px !important;">Add Distribution Area/Box</a>
                @endif
                <table id="datatable" class="table table-striped table-bordered mt-2" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Distribution Area/Box</th>
                            <th class="text-center">Total Clients</th>
                            <th class="text-center">Total Due Bill</th>
                            <th class="hidden-print text-center">Manage</th>
                        </tr>
                    </thead>


                    <tbody>
                        @php($i = 0)
                        @foreach ($distributions as $distribution)
                            @php($i = $i + 1)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $distribution->distribution }}</td>
                                <td class="text-center">{{$distribution->clients_count}}</td>
                                <th class="text-center">{{ $distribution->total_due ?? "0.00" }}</th>
                                <td class="hidden-print text-center">
                                    @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('distribution_edit'))
                                        <a href="{{ route('distribution.edit', $distribution->id) }}" class="btn-edit"><i
                                                class="fa fa-edit"></i></a>
                                    @endif
                                    {{-- <a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a> --}}
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
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
@endsection
@section('custom_css')
    <style>
        .dataTable>thead>tr>th[class*=sort]:after {
            display: none;
        }

        .dataTable>thead>tr>th[class*=sort]:before {
            display: none;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
@endsection
