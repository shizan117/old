<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
Bandwidth List
@endsection

@section('content')
        <div class="row">
            <div class="col-12">
                <div class="card-box table-responsive">
                    <div class="row mb-2">
                        <div class="col-6">
                            <h4 class="m-t-0 header-title">Bandwidth List</h4>
                        </div>
                        <div class="col-6 text-right">
                            @if($user->can('bandwidth_add'))
                            <a href="{{ route('bandwidth.add') }}" class="btn btn-primary" style="text-transform: uppercase;">Add New Bandwidth</a>
                            @endif
                        </div>
                    </div>

                    <table id="datatable" class="table table-sm table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Bandwidth Name</th>
                            <th>Upload Limit</th>
                            <th>DownLoad Limit</th>
                            <th class="hidden-print">Manage</th>
                        </tr>
                        </thead>


                        <tbody>
                        @foreach ($bandwidthData as $dataBandwidth)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $dataBandwidth['bandwidth_name'] }}</td>
                            <td>{{ $dataBandwidth['rate_up'].' '.$dataBandwidth['rate_up_unit'] }}</td>
                            <td>{{ $dataBandwidth['rate_down'].' '.$dataBandwidth['rate_down_unit'] }}</td>
                            <td class="hidden-print">
                                @if($user->can('bandwidth_edit'))
                                <a href="{{ route('bandwidth.edit', $dataBandwidth->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                @endif
                                {{--<a href="#" class="btn-show"><i class="fa fa-trash-o"></i></a>--}}

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
