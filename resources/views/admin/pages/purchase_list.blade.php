@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                <div class="btn-group m-b-10">
                    <div class="btn-group m-b-10">
                        @if($role_id == 1 OR $role_id == 2 OR $role_id == 5)
{{--                            <a href="{{ route('purchases.branch') }}" class="btn btn-secondary">Branches Purchases</a>--}}
                            <a href="{{ route('purchases') }}" class="btn btn-success">Purchases</a>
                        @endif
                            <a href="{{ route('purchase.add') }}" class="btn btn-info">Add Purchase</a>
                    </div>
                </div>


                <table id="datatable" class="table table-sm table-bordered table-responsive-sm table-responsive-lg"
                       cellspacing="0" width="100%">
                    <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Date</th>
                        <th>Total Price</th>
                        {{--@if($admin)--}}
                            {{--<th>Branch Name</th>--}}
                        {{--@endif--}}
                        <th>Entry By</th>

                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @php($i = 0)
                    @foreach ($purchases as $purchase)
                        @php($i = $i+1)
                        <tr class="text-center">
                            <td>{{ $i }}</td>
                            <td>{{ date("d-M-Y", strtotime($purchase->purchase_date)) }}</td>
                            <td class="text-right">{{ $purchase->price }}</td>
                            {{--@if($admin)--}}
                                {{--@php((empty($purchase->branchId)) ? $branch_name = '-' : $branch_name = $purchase->branch->branchName)--}}
                                {{--<td>{{ $branch_name }}</td>--}}
                            {{--@endif--}}
                            <td>{{ $purchase->user->name }}</td>
                            <td class="hidden-print">
                                <a href="{{ route('purchase.show', $purchase->id) }}" class="btn-show"><i
                                            class="fa fa-eye"></i></a>
                                <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn-edit"><i class="fa fa-edit"></i></a>
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