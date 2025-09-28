@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
{{--                <h4 class="m-t-0 header-title">{{ $page_title }}</h4>--}}
                <form class="form-row form-horizontal justify-content-center" action="" role="form" method="get">
                    <div class="col-md-3">
                        <h4 class="text-default">{{ (request('from_date') == '')?'This Month Report':'Custom Search Report' }}</h4>
                    </div>
                    <div class="col-md-3">
                        
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="from_date" value="{{ \request('from_date')??'' }}" class="form-control datepicker" placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
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
                    </div>
                </form>
                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#Inv No</th>
                        <th>Inv Date</th>
                        <th>Client Name</th>
                        <th>Bill Month</th>
                        <th>Bandwidth</th>
                        <th class="text-right">Buy Price</th>
                        <th class="text-right">Sale Price</th>
                        <th class="text-right">Paid Amount</th>
                        <th class="text-right">Due</th>
                        <th class="text-right">Profit</th>
                        <th class="text-right">Pay Date</th>
                        @unlessrole('Reseller')
                        <th>Reseller</th>
                        @endunlessrole
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($invoiceData as $invoice)
                        <tr class="text-success">
                            <td>{{ $invoice->id }}</td>
                            <td>{{ $invoice->created_at->format('d-M-y') }}</td>
                            <td data-toggle="tooltip" data-placement="right" title="{{ 'Area/Box: '.$invoice->client->distribution->distribution }}">
                                <a href="{{ route('client.view', $invoice->client->id) }}" target="_blank">
                                    {{ $invoice->client->client_name }} ({{$invoice->client->username}})
                                </a>
                            </td>
                            <td>{{ date('M', mktime(0, 0, 0, $invoice->bill_month, 1)) }} - {{ $invoice->bill_year }}</td>
                            <td>{{ $invoice->bandwidth }}</td>
                            <td class="text-right">{{ $invoice->buy_price }}</td>
                           <td class="text-right">{{ $invoice->sub_total }}</td>
                           <td class="text-right">{{ $invoice->paid_amount }}</td>
                           <td class="text-right">{{ $invoice->due }}</td>
                           <td class="text-right">{{ $invoice->paid_amount - $invoice->buy_price }}</td>
                           <td class="text-right">{{ $invoice->updated_at }}</td>
                            @unlessrole('Reseller')
                            <td>{{ $invoice->client->reseller->resellerName }}</td>
                            @endunlessrole
                        </tr>
                    @endforeach
                    </tbody>

                    <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right">Total:</td>
                        <td class="text-right"></td>
                        {{--<td class="text-right"></td>--}}

                        @unlessrole('Reseller')
                        <td></td>
                        @endunlessrole

                    </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection
{{--ENCODED LOGO--}}
<?php
$path = asset("assets/images/".$setting['logo']);
$type = pathinfo($path, PATHINFO_EXTENSION);
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$data = file_get_contents($path, false, stream_context_create($arrContextOptions));
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

?>
@section('custom_js')
    {{--@include('admin.layouts.print-js')--}}
    <script>
        window.pdfMake.fonts = {
            kalpurush: {
                normal: "kalpurush.ttf",
                bold: "kalpurush.ttf",
            }
        };
        //Buttons examples
        $('#datatable').DataTable({
            dom: 'Bfrtip',
            "pageLength": 100,
            "lengthMenu": [[100, 200, 500, -1], [100, 200, 500, "All"]],
            "aaSorting": [],
            buttons: ['pageLength','excel',
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },
                    customize: function ( doc ) {
                        doc.defaultStyle = {
                            font: 'kalpurush'
                        };
                        doc.content.splice( 1, 0, {
                            margin: [ 15, -40, 0, 15 ],
                            alignment: 'left',
                            width:100,
                            image: "{{ $base64 }}"
                        } );
                        // console.log(doc.content)
                    },
                    footer: true

                },

                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)']
                    },
                    messageBottom: 'Print: {{ date("d-M-Y") }}',
                    customize: function (win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    },
                    footer: true
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

                    messageBottom: 'Print: {{ date("d-M-Y") }}',
                    customize: function (win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    },
                    footer: true

                }

            ],
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;

                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                var col = [9];
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
                        '' + pageTotal.toFixed(2) + '<br> (Total:'+Total.toFixed(2)+')' +''
                    );
                }
            },
        });

    </script>
    <script>
        $(document).ready(function () {
            $(".datepicker").datepicker({
                changeMonth: true, changeYear: true, autoclose: true, todayHighlight: true, format: 'yyyy-mm-dd'
            });
        });
        {{--$("#resellerId").on('change',function(){--}}
            {{--var resellerId = $("#resellerId").val()--}}
            {{--window.location.href = "{{ route($route_url) }}" + "?resellerId=" + resellerId ;--}}
        {{--})--}}
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
    <script src="{{ asset('assets/js/vfs_fonts.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
@endsection
