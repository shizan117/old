<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                {{--<h4 class="m-t-0 header-title">{{ $page_title }}</h4>--}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="btn-group m-b-10">
                            <div class="btn-group m-b-10">
                                {{--@if($role_id != 4)--}}
                                    {{--<a href="{{ route('branch.income.list') }}" class="btn btn-secondary">Branches Income List</a>--}}
                                    {{--<a href="{{ route('income.list') }}" class="btn btn-success">All Income List</a>--}}
                                {{--@endif--}}
                                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('income_add'))
                                <a href="{{ route('income.add') }}" class="btn btn-primary">Add Income</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <form class="form-horizontal" action="" role="form" method="get">
                            <div class="form-group row">
                                <div class="col-5">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="from_date" value="{{ \request('from_date')??'' }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="to_date" value="{{ \request('to_date')??'' }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            <button type="submit"
                                                    class="btn btn-info waves-effect waves-light">Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <table id="datatable"
                       class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg"
                       cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        {{--<th>#</th>--}}
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Note</th>
                        <th class="text-right">Amount</th>
                        <th>Entry By</th>

                        {{--@if($role_id != 4)--}}
                            {{--<th>Branch Name</th>--}}
                        {{--@endif--}}
                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @php($total_amount = 0)
                    @foreach ($incomes as $income)
                        @php($total_amount += $income->amount )
                        <tr>

                            {{--<td></td>--}}
                            <td>{{ date("d-M-Y", strtotime($income->date)) }}</td>
                            <td>{{ $income->name }}</td>
                            <td>{{ $income->category->name??'' }}</td>
                            <td>{{ $income->note }}</td>
                            <td class="text-right">{{ number_format($income->amount, 2) }}</td>
                            <td>{{ $income->user->name }}</td>
                            {{--@if($role_id != 4)--}}
                                {{--@php((empty($income->branchId)) ? $branch_name = '-' : $branch_name = $income->branch->branchName)--}}
                                {{--<td>{{ $branch_name }}</td>--}}
                            {{--@endif--}}


                            <td class="hidden-print">

                                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('income_edit'))
                                <a href="{{ route('income.edit', $income->id) }}" class="btn-edit">
                                    <i class="fa fa-edit"></i></a>
                                @endif

                            </td>
                        </tr>

                    @endforeach
                    </tbody>


                    <tfoot>     <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">Total:</td>
                    <td class="text-right">{{ number_format($total_amount, 2) }}</td>
                    <td></td>
                    {{--@if($role_id != 4)--}}
                    {{--<td></td>--}}
                    {{--@endif--}}
                    <td class="hidden-print"></td></tfoot>
                </table>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
{{--    @include('admin.layouts.print-js')--}}
<script type="text/javascript">
    $(document).ready(function() {



        //Buttons examples
        $('#datatable').DataTable({

            "footerCallback": function(row, data, start, end, display) {
                var api = this.api();

                var intVal = function(i) {
                    return typeof i === 'string'
                        ? i.replace(/[\$,]/g, '') * 1
                        : typeof i === 'number'
                            ? i : 0;
                };

                var col = [4];  // Only sum Amount

                for (var j = 0; j < col.length; j++) {
                    var pageTotal = api
                        .column(col[j], { page: 'current' })
                        .data()
                        .reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);

                    var Total = api
                        .column(col[j])
                        .data()
                        .reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);

                    $(api.column(col[j]).footer()).html(
                        '' + pageTotal.toFixed(2)
                    );
                }
            },

            dom: 'Bfrtip',
            "pageLength": 20,
            "lengthMenu": [
                [20, 30, 50, -1],
                [20, 30, 50, "All"]
            ],
            buttons: ['pageLength', 'excel',
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    }

                },

                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },


                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass(
                                'table-striped table-responsive-sm table-responsive-lg dataTable'
                            )
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    }
                },
                {
                    extend: 'print',
                    text: 'Print',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)'],
                        modifier: {
                            page: 'current'
                        }
                    },


                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass(
                                'table-striped table-responsive-sm table-responsive-lg dataTable'
                            )
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    }

                }

            ]
        });
    });


</script>
    <script>
        $(document).ready(function () {
            $(".datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            });
        });
    </script>
@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
    <link href='{{ asset("assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css") }}'
          rel="stylesheet" type="text/css"/>
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
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
@endsection
