<?php
    $user = Auth::user();
    if (Auth::user()->branchId != null) $_branchName = Auth::user()->branch->branchName;
    else $_branchName = '';
?>
@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <div class="row">
                    <div class="col-md-7">
                        <div class="btn-group m-b-10">
                            <div class="btn-group m-b-10">
                                @can('report_expense')
                                    <a href="{{ route('report.expanse') }}" class="btn btn-success">Expanse Report</a>
                                    {{--<a href="{{ route('report.expanse.branch') }}" class="btn btn-secondary">Branches Expanse Report</a>--}}
                                @endcan
                                <a href="{{ route($this_month_url) }}" class="btn btn-primary waves-effect waves-light">This
                                    Month Report</a>
                                <a href="{{ route($last_month_url) }}"
                                   class="btn btn-secondary waves-effect waves-light">Last Month Report</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <form class="" role="form" action="{{ route($post_url) }}" method="POST">
                            {{ csrf_field() }}
                            <div class="form-group row">
                                <div class="col-5">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="fdate" class="form-control"
                                               placeholder="yyyy-mm-dd"
                                               id="datepicker_from">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-7">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="edate" class="form-control"
                                               placeholder="yyyy-mm-dd"
                                               id="datepicker_to">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Search</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if($fdate != '')
                    <h3 class="text-center">Report Date: {{ date("d-M-Y", strtotime($fdate)) }}
                        to {{ date("d-M-Y", strtotime($edate)) }}</h3>
                @else
                    <h3 class="text-center">{{ $report_date }}</h3>
                @endif

                <table id="datatable"
                       class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg text-center"
                       cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Expanse Name</th>
                        <th>Amount</th>
                        <th>Vat</th>
                        <th>Total</th>
                        {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                            {{--<th>Branch Name</th>--}}
                        {{--@endif--}}
                    </tr>
                    </thead>

                    <tbody>@php($i = 0)
                    @foreach ($reports as $report)
                        @php($i++)
                        <tr>
                            <td>{{ $i }}</td>
                            <td class="text-left">{{ date('d-M-y h:i:s A',strtotime($report->created_at??$report->updated_at)) }}</td>
                            @if($report->tr_type == 'Product Purchase')
                                @php($type = 'Product Purchase')
                            @elseif($report->tr_type == 'Loan')
                                @php($loan_payer_id = \App\PayLoan::where('tr_id', $report->id)->first()->loan_payer_id)
                                @php($type = 'Loan Pay ('.\App\LoanPayer::find($loan_payer_id)->name.')')
                            @else
                                @php($type = \App\Expanse::where('tr_id', $report->id)->first()->name)
                            @endif


                            <td class="text-left">{{ $type }}</td>
                            <td class="text-right">{{ $report->tr_amount }}</td>
                            <td class="text-right">{{ $report->tr_vat }}</td>
                            <td class="text-right">{{ $report->dr }}</td>
                            {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                                {{--@php(($report->branchId == null) ? $branch_name = '-' : $branch_name = $report->branchId->branchName)--}}
                                {{--<td>{{ $branch_name }}</td>--}}
                            {{--@endif--}}

                        </tr>

                    @endforeach
                    <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="text-right">Total:</td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
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
            $("#datepicker_from").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'

            });
            $("#datepicker_to").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            });

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

                    var col = [3, 4, 5];
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
                buttons: ['pageLength','excel','pdf',
                    {
                        extend: 'print',
                        text: 'Print All',
                        autoPrint: true,
                        exportOptions: {
                            columns: [':not(.hidden-print)']
                        },

                        messageTop: function () {
                            var fdate = '{{ $fdate }}';
                            if (fdate != '') {
                                return '<h2 class="text-center">{{ $_branchName }}</h2>' +
                                    '<h5 class="text-center">Report Date: {{ date("d-M-Y", strtotime($fdate)) }} to {{ date("d-M-Y", strtotime($edate)) }}</h5>'
                            } else {
                                return '<h2 class="text-center">{{ $_branchName }}</h2>' +
                                    '<h5 class="text-center">{{ $report_date }}</h5>'
                            }

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
                        text: 'Print',
                        autoPrint: true,
                        exportOptions: {
                            columns: [':not(.hidden-print)'],
                            modifier: {
                                page: 'current'
                            }
                        },

                        messageTop: function () {
                            var fdate = '{{ $fdate }}';
                            if (fdate != '') {
                                return '<h2 class="text-center">{{ $_branchName }}</h2>' +
                                    '<h5 class="text-center">Report Date: {{ date("d-M-Y", strtotime($fdate)) }} to {{ date("d-M-Y", strtotime($edate)) }}</h5>'
                            } else {
                                return '<h2 class="text-center">{{ $_branchName }}</h2>' +
                                    '<h5 class="text-center">{{ $report_date }}</h5>'
                            }
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
