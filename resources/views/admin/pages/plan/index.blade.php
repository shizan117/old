@extends ('admin.layouts.master')
@section('title')
{{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <div class="row">
                    <div class="col-md-4">
                        <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                    </div>
                    <div class="col-md-8 text-right">
                        <div class="btn-group m-b-10">
                            <div class="btn-group m-b-10">
                                <a href="{{ route('plan.pppoe') }}" class="btn btn-success">PPPOE Plan List</a>
                                <a href="{{ route('plan.hotspot') }}" class="btn btn-secondary">Hotspot Plan List</a>
                                <a href="{{ route('plan.ip') }}" class="btn btn-custom">IP Plan List</a>
                                <a href="{{ route('plan.index') }}" class="btn btn-dark">All Plan List</a>
                                @can('plan_add')
                                    <a href="{{ route('plan.add') }}" class="btn btn-primary" style="text-transform: uppercase;">Add New Plan</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>


                <table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Plan Name</th>
                        <th>Type</th>
                        @role('Reseller')
                            <th>Buy Price</th>
                            <th>Sell Price</th>
                        @else
                            <th>Plan Price</th>
                        @endrole

                        <th>Duration</th>
                        @unlessrole('Reseller')
                            <th>Server</th>
                            {{--<th>Branch Name</th>--}}
                            {{--<th>Reseller Name</th>--}}
                        @endunlessrole
                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @foreach ($planData as $data)
                        @php(($data->duration > 1) ?
                        (($data->duration_unit == 1) ? $unit = 'Months' : $unit = 'Days') :
                        (($data->duration_unit == 1) ? $unit = 'Month' : $unit = 'Day'))

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $data->plan_name }}</td>
                            <td>{{ $data->type }}</td>
                            <td>{{ number_format($data->plan_price,2) }}</td>
                            {{--@if($role_id == 4)--}}
                                {{--<td>{{ $data->reseller_sell_price }}</td>--}}
                            {{--@endif--}}
                            <td>{{ $data->duration }} {{ $unit }}</td>
                            @unlessrole('Reseller')
                                {{--@php((empty($data->branch)) ? $branch_name = '-' : $branch_name = $data->branch->branchName)--}}
                                {{--@php((empty($data->reseller)) ? $reseller_name = '-' : $reseller_name = $data->reseller->resellerName)--}}
                                <td>{{ $data->server->server_name }}</td>
                                {{--<td>{{ $branch_name }}</td>--}}
                                {{--<td>{{ $reseller_name }}</td>--}}
                            @endunlessrole

                            <td class="hidden-print">
                                @can('plan_edit')
                                <a href="{{ route('plan.edit', $data->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
                                @endcan

                                @can('plan_delete')
                                <a href="{{ route('plan.delete', $data->id) }}" onclick="return confirm('Are you sure to delete this plan?')"  class="btn-del"> <i class="fa fa-trash-o"></i></a>
                                @endcan
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
