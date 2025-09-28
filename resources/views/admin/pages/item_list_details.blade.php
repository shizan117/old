@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div class="dropdown pull-left m-b-10">
                    <a href="#" class="dropdown-toggle btn btn-primary" data-toggle="dropdown" aria-expanded="false">
                        Items Menu
                    </a>
                    <div class="dropdown-menu dropdown-menu-left">
                        <a href="{{ route('inventory.items.detail') }}" class="dropdown-item">Items Detail</a>
                        <a href="{{ route('inventory.stock.item') }}" class="dropdown-item">Stock List</a>
                        <a href="{{ route('inventory.used.item') }}" class="dropdown-item">Used List</a>
                        <a href="{{ route('inventory.lost.item') }}" class="dropdown-item">Lost List</a>
                        <a href="{{ route('inventory.sold.item') }}" class="dropdown-item">Sold List</a>
                        <a href="{{ route('inventory.refund.item') }}" class="dropdown-item">Refund List</a>
                    </div>
                </div>
                {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                    {{--<div class="dropdown pull-left m-b-10">--}}
                        {{--<a href="#" class="dropdown-toggle btn btn-info" data-toggle="dropdown" aria-expanded="false">--}}
                            {{--Branch Items--}}
                        {{--</a>--}}
                        {{--<div class="dropdown-menu dropdown-menu-left">--}}
                            {{--<a href="{{ route('inventory.items.detail.branch') }}" class="dropdown-item">Branch Item Detail</a>--}}
                            {{--<a href="{{ route('inventory.stock.item.branch') }}" class="dropdown-item">Branch Stock List</a>--}}
                            {{--<a href="{{ route('inventory.used.item.branch') }}" class="dropdown-item">Branch Used List</a>--}}
                            {{--<a href="{{ route('inventory.lost.item.branch') }}" class="dropdown-item">Branch Lost List</a>--}}
                            {{--<a href="{{ route('inventory.sold.item.branch') }}" class="dropdown-item">Branch Sold List</a>--}}
                            {{--<a href="{{ route('inventory.refund.item.branch') }}" class="dropdown-item">Branch Refund List</a>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--@endif--}}

                <div class="clearfix"></div>

                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Product Cat</th>
                        <th>Unit</th>
                        <th>Stock</th>
                        <th>Used</th>
                        <th>Lost</th>
                        <th>Sold</th>
                        <th>Refund</th>
                        <th>Total Bought</th>
                        {{--@if($brshow)--}}
                            {{--<th>Branch Name</th>--}}
                        {{--@endif--}}
                    </tr>
                    </thead>


                    <tbody>
                    @php($i = 0)
                    @foreach ($items as $item)
                        @php($i = $i+1)
                        @php($total_bought = $item->stock+$item->used+$item->lost+$item->sold+$item->refund)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->productCategory->name }}</td>
                            <td class="text-center">{{ $item->unit }}</td>
                            <td class="text-center">{{ $item->stock }}</td>
                            <td class="text-center">{{ $item->used }}</td>
                            <td class="text-center">{{ $item->lost }}</td>
                            <td class="text-center">{{ $item->sold }}</td>
                            <td class="text-center">{{ $item->refund }}</td>
                            <td class="text-center">{{ $total_bought }}</td>
                            {{--@if($brshow)--}}
                                {{--@php(($item->branchId == null) ? $branch_name = '-' : $branch_name = $item->branch->branchName)--}}
                                {{--<td>{{ $branch_name }}</td>--}}
                            {{--@endif--}}
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