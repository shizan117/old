<?php
if (Auth::user()->branchId != null) {
    $_branchName = Auth::user()->branch->branchName;
} else {
    $_branchName = '';
}
?>
@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div class="dropdown pull-left m-b-10">
                    <a href="#" class="dropdown-toggle arrow-none btn btn-primary" data-toggle="dropdown" aria-expanded="false">
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
                        {{--<a href="#" class="dropdown-toggle arrow-none  btn btn-info" data-toggle="dropdown" aria-expanded="false">--}}
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
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Product Cat</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total Price</th>
                        <th>Product Serial</th>
                        <th>Purchase Date</th>
                        {{--@if($brshow)--}}
                            {{--<th>Branch Name</th>--}}
                        {{--@endif--}}
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td></td>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->product->productCategory->name }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-right">{{ $item->price }}</td>
                            <td class="text-right">{{ $item->total_price }}</td>
                            <td class="text-center">{{ $item->serial }}</td>
                            @php($stock_item = \App\StockItem::where('serial', $item->serial)->select('purchase_id')->first())
                            <td class="text-center">
                                <a href="{{ route('purchase.show', $stock_item->purchase_id) }}" target="_blank" class="btn-show"><i
                                            class="fa fa-eye"></i></a>{{ date('d-M-Y', strtotime($stock_item->purchase->purchase_date)) }}
                            </td>
                            {{--@if($brshow)--}}
                                {{--@php(($item->product->branchId == null) ? $branch_name = '-' : $branch_name = $item->product->branch->branchName)--}}
                                {{--<td>{{ $branch_name }}</td>--}}
                            {{--@endif--}}
                        </tr>
                    @endforeach
                        <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-right">Total:</td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td></td>
                            <td></td>
                            {{--@if($brshow)--}}
                                {{--<td></td>--}}
                            {{--@endif--}}
                        </tr>
                        </tfoot>
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
    <script type="text/javascript">
        $(document).ready(function () {
    
            //Buttons examples
            $('#datatable').DataTable({
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(), data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    var col = [4, 5];
                    for (var j = 0; j < col.length; j++) {
                        // Total over this page
                        pageTotal = api
                            .column(col[j], {page: 'current'})
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                            
                        Total = api
                            .column(col[j])
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Update footer
                        $(api.column(col[j]).footer()).html(
                            '' + pageTotal.toFixed(2) + '<br>(Total:'+Total.toFixed(2)+')' +''
                        );
                
                    }
                },
                dom: 'Bfrtip',
                "pageLength": 20,
                "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                //debugger;
                var index = iDisplayIndexFull + 1;
                $("td:first", nRow).html(index);
                return nRow;
                },
                buttons: ['pageLength',
                    {
                        extend: 'print',
                        text: 'Print All',
                        autoPrint: true,
                        exportOptions: {
                            columns: [':not(.hidden-print)']
                        },
    
                        messageTop: function () {
                            return '<h2 class="text-center">{{ $_branchName }}</h2>'
                        },
                        messageBottom: 'Print: {{ date("d-M-Y") }}',
                        customize: function (win) {
    
                            $(win.document.body).find('h1').css('text-align', 'center');
                            $(win.document.body).find('table')
                                .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                                .addClass('compact')
                                .css('font-size', 'inherit', 'color', '#000');
    
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print Current Page',
                        autoPrint: true,
                        exportOptions: {
                            columns: [':not(.hidden-print)'],
                            modifier: {
                                page: 'current'
                            }
                        },
    
                        messageTop: function () {
                            return '<h2 class="text-center">{{ $_branchName }}</h2>'
                        },
                        messageBottom: 'Print: {{ date("d-M-Y") }}',
                        customize: function (win) {
    
                            $(win.document.body).find('h1').css('text-align', 'center');
                            $(win.document.body).find('table')
                                .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                                .addClass('compact')
                                .css('font-size', 'inherit', 'color', '#000');
    
                        }
    
                    }
    
                ]
            });
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