<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
{{ $page_title }}
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <div class="row">
                    <div class="col-md-5">
                        <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                    </div>
                    <div class="col-md-3">
                        @unlessrole('Reseller')
                        <select name="resellerId" id="resellerId" class="form-control bg-secondary">
                            <option value="">All Reseller</option>
                            @foreach($resellers as $reseller)
                                <option value="{{$reseller->resellerId}}" {{ (request('resellerId')==$reseller->resellerId)?'selected':'' }}>{{$reseller->resellerName}}</option>
                            @endforeach
                        </select>
                        @endunlessrole
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                        <div class="m-b-10">
                            <div class="m-b-10 text-md-right text-center">
                                @can('reseller_plan add')
                                    <a href="{{ route('reseller.plan.add') }}" class="btn btn-primary">New Reseller Plan</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $isSuperAdmin = empty(Auth::user()->resellerId);
                @endphp
                <table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        @unlessrole('Reseller')
                            <th>Reseller Name</th>
                        @endunlessrole
                        <th>Plan Name</th>
                        <th>Type</th>

                        @role('Super-Admin')
                            <td>Main Plan Price</td>
                        @endrole

                        @role('Reseller')
                            <th>Buy Price</th>
                        @endrole

                        <th>Sell Price</th>
                        <th>Duration</th>

                        @if ($isSuperAdmin || $reseller_has_plan_price == 1)

                        <th class="hidden-print">Manage</th>
                        @endif
                    </tr>
                    </thead>


                    <tbody>
                    @foreach ($planData as $data)
                        @php(($data->plan->duration > 1) ?
                        (($data->plan->duration_unit == 1) ? $unit = 'Months' : $unit = 'Days') :
                        (($data->plan->duration_unit == 1) ? $unit = 'Month' : $unit = 'Day'))

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            @unlessrole('Reseller')
                                @php((empty($data->reseller)) ? $reseller_name = '-' : $reseller_name = $data->reseller->resellerName)
                                <td>{{ $reseller_name }}</td>
                            @endunlessrole

                            <td>{{ $data->plan->plan_name }}</td>
                            <td>{{ $data->plan->type }}</td>

                            @role('Super-Admin')
                                <td>{{ $data->plan->plan_price }}</td>
                            @endrole

                            <td>{{ $data->sell_price }}</td>

                            @role('Reseller')
                                <td>{{ $data->reseller_sell_price }}</td>
                            @endrole

                            <td>{{ $data->plan->duration }} {{ $unit }}</td>

                            @if (
                                ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('reseller_plan edit'))
                                && ($isSuperAdmin || $reseller_has_plan_price == 1)
                            )
                            <td class="hidden-print">


                                    <a href="{{ route('reseller.plan.edit', $data->id) }}" class="btn-edit">
                                        <i class="fa fa-edit"></i>
                                    </a>


                                @role('Super-Admin')
                                <a href="{{ route('reseller.plan.delete', $data->id) }}" class="btn-show"
                                   onclick="return confirm('Are you sure to delete?')">
                                    <i class="fa fa-trash text-danger"></i>
                                </a>
                                @endrole
                            </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- end row -->


@endsection


@section('custom_js')
    @include('admin.layouts.print-js')
    <script>
        $(document).ready(function () {
            $("#resellerId").on('change',function(){
                var resellerId = $("#resellerId").val()
                window.location.href = "{{ route($route_url) }}" + "?resellerId=" + resellerId ;
            })
        });
    </script>
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
