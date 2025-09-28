<?php
$user = Auth::user();
if ($user->branchId != null) {
    $_branchName = $user->branch->branchName;
} else {
    $_branchName = '';
}
?>
@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <style>
        table.dataTable tbody>tr.selected,
        table.dataTable tbody>tr>.selected {
            color: #fff !important;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                {{-- <h4 class="m-t-0 header-title">{{ $page_title }}</h4> --}}
                <div class="row">
                    <div class="col-6">
                        <div class="btn-group m-b-10">
                            <div class="btn-group m-b-10">
                                {{-- <a href="{{ route($branch_url) }}" class="btn btn-secondary">Branches Clients</a> --}}
                                {{-- <a href="{{ route($main_url) }}" class="btn btn-success">Clients</a> --}}

                                @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('client_add'))
                                    <a href="{{ route('client.add') }}" class="btn btn-primary"
                                        style="border-radius: 5px; text-transform: uppercase;">Add New Client</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <form class="form-horizontal" action="" role="form" method="get">
                            <div class="form-group row">
                                <div class="col-5">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="from_date"
                                            value="{{ \request('from_date') ?? '' }}" class="form-control datepicker"
                                            placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="input-group">
                                        <input type="text" autocomplete="off" name="to_date"
                                            value="{{ \request('to_date') ?? '' }}" class="form-control datepicker"
                                            placeholder="yyyy-mm-dd">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="ti-calendar"></i></span>
                                            <button type="submit" class="btn btn-info waves-effect waves-light">Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- <!--@if ($role_id == 1)--> --}}
                {{-- <!--<form class="" role="form" action="{{ route('client.add.excel.post') }}" method="POST" enctype="multipart/form-data">--> --}}
                {{-- <!--    {{ csrf_field() }}--> --}}
                {{-- <!--    <div class="form-group row">--> --}}
                {{-- <!--        <div class="form-group col-12 row">--> --}}
                {{-- <!--            <label class="col-form-label col-12">Add Clients From Excel File:</label>--> --}}

                {{-- <!--            <div class="input-group col-5">--> --}}
                {{-- <!--                <input class="form-control" type="file" name="import_file" />--> --}}
                {{-- <!--            </div>--> --}}
                {{-- <!--            <button type="submit" class="btn btn-info col-form-label waves-effect waves-light">Submit</button>--> --}}

                {{-- <!--        </div>--> --}}
                {{-- <!--    </div>--> --}}
                {{-- <!--</form>--> --}}
                {{-- <!--@endif--> --}}

                <form id="bulk-active-inactive-form">
                    @csrf
                    <table id="datatable" class="table table-sm table-bordered table-responsive-sm" cellspacing="0"
                        width="100%">
                        <thead style="background-color: #ffc107; color: #000;">
                            <tr>
                                <th><input type="checkbox" id="checkedAll" value="all"></th>
                                <th style="display: none" class="hidden-print"></th>
                                <th style="display: none" class="hidden-print"></th>
                                <th class="hidden-print">#</th>
                                <th style="min-width: 91px;">Client Name</th>
                                <th>Username</th>
                                {{-- <th>Email</th> --}}
                                <th>Phone</th>
                                <th style="min-width: 64px;">Reg Date</th>
                                <th style="min-width: 70px">Box/Area</th>
                                <th>Plan</th>
                                <th>Server</th>
                                <th>Address</th>
                                <th style="min-width: 56px;">Cable T</th>
                                <th style="min-width: 56px;">OLT T</th>
                                <th style="min-width: 74px;">Pkg. Price</th>
                                <th>Dis</th>
                                <th style="min-width: 97px;">Advance TK.</th>
                                <th>Due</th>
                                <th style="min-width: 64px;">Exp Date</th>
                                <th>Status</th>
                                {{-- <th>Up-Time</th> --}}
                                {{-- <th>Branch</th> --}}
                                <th class="hidden-print" style="width:70px">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientData as $dataClient)
                                {{-- {{dd($dataClient)}} --}}
                                {{-- Conditionally Client marking by diffrent colors --}}

                                @php
                                    $bgColor = '';
                                    if (number_format($dataClient['due'], 2) > 0 && $dataClient->status == 'On') {
                                        // DUE + ACTIVE
                                        $bgColor = 'background-color:  #2F3E47; color: #08A9AC;';
                                    } elseif (
                                        number_format($dataClient['due'], 2) > 0 &&
                                        $dataClient->status == 'Off'
                                    ) {
                                        // DUE + INACTIVE
                                        $bgColor = 'background-color: #2F3E47; color: #F1948A;';
                                    } elseif (
                                        number_format($dataClient['due'], 2) == 0 &&
                                        $dataClient->status == 'On'
                                    ) {
                                        // PAID + ACTIVE
                                        // $bgColor = 'background-color: #086a34; color: #fff;';
                                        $bgColor = 'background-color: #2F3E47; color: #2BD45C;';
                                    } elseif (
                                        number_format($dataClient['due'], 2) == 0 &&
                                        $dataClient->status == 'Off'
                                    ) {
                                        // PAID + INACTIVE
                                        $bgColor = 'background-color: #2F3E47; color: #ABB2B9;';
                                    }

                                    $clientPlanPrice = 0;
                                    if ($dataClient->resellerId != null) {
                                        // If the client has a reseller, fetch the reseller sell price
                                        $clientPlanPrice = DB::table('reseller_plans')
                                            ->where('resellerId', $dataClient->resellerId)
                                            ->where('plan_id', $dataClient->plan_id)
                                            ->value('reseller_sell_price');
                                    } else {
                                        $clientPlanPrice = $dataClient->plan->plan_price;
                                    }

                                @endphp

                                <tr style="{{ $bgColor }}">
                                    {{-- <tr> --}}
                                    @php($dataClient->status == 'On' ? ($status = 'Active') : ($status = 'Inactive'))
                                    <td style="display: none" class="hidden-print">{{ $dataClient->id }}</td>
                                    <td style="display: none" class="hidden-print">{{ $dataClient->server_status }}</td>
                                    <td style="background: #2f3e47">
                                        <input name="clientID[]" class="checkSingle" type="checkbox"
                                            value="{{ $dataClient->id }}">
                                    </td>
                                    <td></td>
                                    <td data-toggle="tooltip" data-placement="right"
                                        title="{{ $dataClient['house_no'] . '-' . $dataClient['address'] }}">
                                        {{ $dataClient['client_name'] }}</td>
                                    <td>{{ $dataClient['username'] }}</td>
                                    {{--                            <td>{{ $dataClient['email'] }}</td> --}}
                                    <td>{{ $dataClient['phone'] }}</td>
                                    <td>{{ $dataClient['created_at']->format('d-M-y') }}</td>
                                    <td>{{ $dataClient->distribution->distribution ?? '--' }}</td>
                                    <td>{{ $dataClient->plan->plan_name ?? '--' }}</td>
                                    <td>{{ $dataClient->plan->server->server_name ?? '--' }}</td>
                                    <td>{{ $dataClient['address'] }}</td>
                                    <td>{{ $dataClient['cable_type'] ?? '-----' }}</td>
                                    <td>{{ $dataClient['olt_type'] ?? '-----' }}</td>
                                    <td>{{ $clientPlanPrice }}</td>
                                    <td>{{ $dataClient['discount'] }}</td>
                                    <td>{{ number_format($dataClient['balance'], 2) }}</td>
                                    <td>{{ number_format($dataClient['due'], 2) }}</td>
                                    <td data-toggle="tooltip" data-placement="left"
                                        title="{{ date('h:i A', strtotime($dataClient['expiration'])) }}">
                                        {{ date('d-M-y', strtotime($dataClient['expiration'])) }} </td>
                                    <td>{{ $status }}</td>
                                    {{-- <td>{{$dataClient->uptime}}</td> --}}
                                    {{-- @php((empty($dataClient->branch)) ? $branch_name = '-' : $branch_name = $dataClient->branch->branchName) --}}
                                    {{-- <td>{{ $branch_name }}</td> --}}
                                    <td class="hidden-print" style="background: #2f3e47">
                                        <span style="display: flex; align-items: center;">

                                            @if (Route::is('client.index') && !empty($dataClient->uptime))
                                                @if ($dataClient->uptime == 'using_no_microtik')
                                                    <i title="MikroTik Disconnected" data-toggle="tooltip"
                                                        data-placement="left" class="fa fa-circle"
                                                        style="color: #ff5b5b; font-size: 17px; margin-right: 4px; margin-top: 2px;">
                                                    </i>
                                                @elseif ($dataClient->uptime != 'router_off')
                                                    <i title="{{ $dataClient->uptime }}" data-toggle="tooltip"
                                                        data-placement="left" class="fa fa-circle"
                                                        style="color: #0eac5c; font-size: 17px; margin-right: 4px; margin-top: 2px;">
                                                    </i>
                                                @else
                                                    <i title="Router Off/Disconnected" data-toggle="tooltip"
                                                        data-placement="left" class="fa fa-circle"
                                                        style="color: #ff5b5b; font-size: 17px; margin-right: 4px; margin-top: 2px;">
                                                    </i>
                                                @endif
                                            @endif



                                            @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('client_profile'))
                                                <a href="{{ route('client.view', $dataClient->id) }}" target="_blank"
                                                    class="btn-show"><i class="fa fa-eye"
                                                        style="color: deepskyblue;"></i></a>
                                            @endif
                                            @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('client_edit'))
                                                <a href="{{ route('client.edit', $dataClient->id) }}" class="btn-edit"><i
                                                        class="fa fa-edit"></i></a>
                                            @endif
                                            {{-- <a href="#" class="btn-del"><i class="fa fa-trash-o"></i></a> --}}
                                            @if (
                                                $dataClient->status == 'On' &&
                                                    setting('using_mikrotik') &&
                                                    ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('client_bandwidth graph')))
                                                <a class="btn-show" id="chartShow" data-toggle="modal"
                                                    style="cursor: pointer;" data-name="{{ $dataClient->client_name }}"
                                                    data-target="#trafficView" data-id="{{ $dataClient->id }}">
                                                    <i class="fa fa-bar-chart text-warning"></i>
                                                </a>
                                            @endif
                                            @if ($dataClient->server_status == 1 && (!$dataClient->reseller || $dataClient->reseller->is_payment == 0))
                                                @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can(['client_payment collection']))
                                                    <a href="{{ route('client.pay.due', $dataClient->id) }}"
                                                        class="btn-show"><i
                                                            class="fa fa-product-hunt text-primary"></i></a>
                                                @endif
                                            @endif
                                            @if (request()->is('adminisp/clients/old-clients') &&
                                                    ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can(['client_delete'])))
                                                <a href="{{ route('client.delete', $dataClient->id) }}" class="btn-show"
                                                    onclick="return confirm('Are you sure to delete?')">
                                                    <i class="fa fa-trash text-danger"></i>
                                                </a>
                                            @endif
                                            @if ($dataClient->phone != '')
                                                <?php
                                                $mobile_no = $dataClient->phone;
                                                $lenght = strlen((string) $mobile_no);
                                                if ($lenght > 11) {
                                                    $to = substr_replace($mobile_no, '880', 0, 3) . '';
                                                } elseif ($lenght == 11) {
                                                    $to = substr_replace($mobile_no, '880', 0, 1) . '';
                                                } else {
                                                    $to = substr_replace($mobile_no, '880', 0, 0) . '';
                                                }
                                                ?>
                                                <a href="https://api.whatsapp.com/send/?phone={{ $to }}"
                                                    title="Contact With Client"
                                                    class="text-success waves-effect waves-light" style="font-size: 18px;"
                                                    target="_blank">
                                                    <i class="fa fa-whatsapp"></i>
                                                </a>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div> <!-- end row -->
    <!-- end row -->

    <!-- Add traffic modal -->
    <div class="modal fade text-left" id="trafficView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33"
        aria-hidden="true">
        <div class="modal-dialog traffic-graph modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <label class="modal-title text-text-bold-600 text-warning" id="myModalLabel33"
                        style="font-size: 20px;">
                        <span class="fa fa-bar-chart-o"></span> Traffic View</label>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="container">
                    <div id="container" style="min-width: 67%; margin: 0 auto"></div>
                    <input type=hidden name="interface" id="interface" />
                    <div id="traffic" class="pl-3 mt-3 pb-3"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal End -->
@endsection

@section('custom_js')
    <script>
        var interVal;
        var chart;

        function requestDatta(id) {
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "{{ route('get.pppoe.traffic') }}",
                data: {
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    var midata = data;
                    if (midata.length > 0) {
                        var TX = parseInt(midata[0].data);
                        var RX = parseInt(midata[1].data);
                        var x = (new Date()).getTime();
                        shift = chart.series[0].data.length > 19;
                        chart.series[0].addPoint([x, TX], true, shift);
                        chart.series[1].addPoint([x, RX], true, shift);
                        if (isNaN(TX)) {
                            TX = '-';
                        } else {
                            if (TX >= 1024) {
                                TX = parseFloat(TX / 1024).toFixed(2) + 'Mbps';
                            } else {
                                TX = parseFloat(TX).toFixed(2) + 'Kbps';
                            }
                        }
                        if (isNaN(RX)) {
                            RX = '-';
                        } else {
                            if (RX >= 1024) {
                                RX = parseFloat(RX / 1024).toFixed(2) + 'Mbps';
                            } else {
                                RX = parseFloat(RX).toFixed(2) + 'Kbps';
                            }
                        }
                        document.getElementById("traffic").innerHTML = TX + " / " + RX;
                    } else {
                        document.getElementById("traffic").innerHTML = "- / -";
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.error("Status: " + textStatus + " request: " + XMLHttpRequest);
                    console.error("Error: " + errorThrown);
                }
            });
        }

        $(document).ready(function() {
            $(document).on("click", "#chartShow", function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var c_name = $(this).data('name');
                $('#interface').val(id);
                Highcharts.setOptions({
                    global: {
                        useUTC: false
                    }
                });


                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'container',
                        animation: Highcharts.svg,
                        type: 'area', //line,//area
                        events: {
                            load: function() {
                                interVal = setInterval(function() {
                                    requestDatta(document.getElementById("interface")
                                        .value);
                                }, 1000);
                            }
                        }
                    },
                    title: {
                        text: 'Monitoring (' + c_name + ')'
                    },
                    xAxis: {
                        type: 'datetime',
                        tickPixelInterval: 150,
                        maxZoom: 20 * 1000
                    },
                    yAxis: {
                        minPadding: 0.2,
                        maxPadding: 0.2,
                        title: {
                            text: 'Traffic',
                            margin: 50
                        },
                        labels: {
                            formatter: function() {
                                if (this.value >= 1024) {
                                    return parseFloat(this.value / 1024).toFixed(0) + 'Mbps';
                                }
                                return parseFloat((this.value / 128) * 100).toFixed(0) + 'Kbps';
                            }
                        }
                    },
                    series: [{
                        name: 'TX',
                        data: []
                    }, {
                        name: 'RX',
                        data: []
                    }]
                });
            });
            $("#trafficView").click(function() {
                clearInterval(interVal);
            })

        })


        $("#checkedAll").change(function() {
            if (this.checked) {
                $(".checkSingle").each(function() {
                    this.checked = true;
                    $("#bulk-wrapper").show();
                    $("#single-wrapper").hide();
                });
            } else {
                $(".checkSingle").each(function() {
                    this.checked = false;
                    $("#bulk-wrapper").hide();
                    $("#single-wrapper").show();
                });
            }
        });

        $(".checkSingle").click(function() {
            if ($(this).is(":checked")) {
                var isAllChecked = 0;

                $(".checkSingle").each(function() {
                    if (!this.checked)
                        isAllChecked = 1;
                });

                if (isAllChecked == 0) {
                    $("#checkedAll").prop("checked", true);
                }
                $("#bulk-wrapper").show();
                $("#single-wrapper").hide();
            } else {
                var isAllUnchecked = 0;
                $(".checkSingle").each(function() {
                    if (this.checked)
                        isAllUnchecked = 1;
                });

                if (isAllUnchecked == 0) {
                    $("#bulk-wrapper").hide();
                    $("#single-wrapper").show();
                }
                $("#checkedAll").prop("checked", false);
            }
        });

        var table = $('#datatable').DataTable({
            select: {
                style: 'single'
            },
            order: [],
            dom: 'Bfrtip',
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                //debugger;
                var index = iDisplayIndexFull + 1;
                $("td:eq(3)", nRow).html(index);
                return nRow;
            },

            "pageLength": 20,
            "lengthMenu": [
                [20, 30, 50, -1],
                [20, 30, 50, "All"]
            ],
            stateSave: true,
            stateSaveParams: function(settings, data) {
                data.search.search = "";
            },
            // "columnDefs": [
            //     { "orderable": false, "targets": 3 }
            // ],
            // "aoColumnDefs": [
            //     { "sType": "html", "aTargets": [ 3 ] }
            // ],
            "aaSorting": [],
            "columnDefs": [{
                "orderable": false,
                "targets": 0
            }],
            buttons: ['pageLength',
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    titleAttr: 'Show/Hide Columns'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: [':visible', ':not(.hidden-print)'],
                    },
                },
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: [':visible', ':not(.hidden-print)'],
                    }
                },
                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    className: 'btn btn-warning',
                    exportOptions: {
                        columns: [':visible', ':not(.hidden-print)'],
                    },

                    messageTop: function() {
                        return '<h2 class="text-center">{{ $_branchName }}</h2>'
                    },
                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

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
                    className: 'btn btn-info',
                    exportOptions: {
                        columns: [':visible', ':not(.hidden-print)'],
                        modifier: {
                            page: 'current'
                        }
                    },

                    messageTop: function() {
                        return '<h2 class="text-center">{{ $_branchName }}</h2>'
                    },
                    messageBottom: 'Print: {{ date('d-M-Y') }}',
                    customize: function(win) {

                        $(win.document.body).find('h1').css('text-align', 'center');
                        $(win.document.body).find('table')
                            .removeClass('table-striped table-responsive-sm table-responsive-lg dataTable')
                            .addClass('compact')
                            .css('font-size', 'inherit', 'color', '#000');

                    }

                }

            ]
        });
    </script>
    @if (
        $user->hasAnyRole('Super-Admin', 'Reseller') ||
            $user->canAny(['client_activate', 'client_deactivate', 'client_add to server', 'client_delete from server']))
        <script>
            $('table').before('<div id="sticky-button-wrapper">' +
                '<span id="single-wrapper"><button type="submit" id="add" class="btn btn-sm btn-primary" disabled title="Add to Server""><i class="mdi mdi-plus"></i></button>' +
                '<button type="submit" id="delete" class="btn btn-sm btn-danger" disabled title="Delete from Server"><i class="mdi mdi-minus"></i></button>' +
                '<button type="submit" id="active" class="btn btn-sm btn-info" disabled title="Active"><i class="mdi mdi-check"></i></button>' +
                '<button type="submit" id="inactive" class="btn btn-sm btn-warning" disabled title="Inactive"><i class="mdi mdi-close"></i></button></span>' +
                '<span id="bulk-wrapper" style="display:none"><button type="submit" id="bulk-add-server" class="btn btn-sm btn-primary" title="Bulk Add to Server"><i class="mdi mdi-plus"></i></button>' +
                '<button type="submit" id="bulk-delete-server" class="btn btn-sm btn-danger" title="Bulk Delete from Server"><i class="mdi mdi-minus"></i></button>' +
                '<button type="submit" id="bulk-active" class="btn btn-sm btn-info" title="Bulk Active"><i class="mdi mdi-check"></i></button>' +
                '<button type="submit" id="bulk-inactive" class="btn btn-sm btn-warning" title="Bulk Inactive"><i class="mdi mdi-close"></i></button></span></div>'
            );

            table.on('select', function(e, dt, type, indexes) {
                    var rowData = table.rows(indexes).data().toArray();
                    if (rowData[0][1] == 1) {
                        if (rowData[0][15] === "Active") {
                            $('#active').attr("disabled", true);
                            $('#inactive').attr("disabled", false);
                        } else {
                            $('#active').attr("disabled", false);
                            $('#inactive').attr("disabled", true);
                        }
                        $('#delete').attr("disabled", false);
                    } else {
                        $('#active').attr("disabled", true);
                        $('#inactive').attr("disabled", true);
                        $('#add').attr("disabled", false);
                        $('#delete').attr("disabled", true);
                    }
                })
                .on('deselect', function(e, dt, type, indexes) {
                    var rowData = table.rows(indexes).data().toArray();
                    $('#add').prop("disabled", true);
                    $('#delete').prop("disabled", true);
                    $('#active').prop("disabled", true);
                    $('#inactive').prop("disabled", true);
                });


            $('#active').click(function() {
                if (confirm('Do you want to activate this user?')) {
                    var rows = $('tr.selected');
                    var rowData = table.rows(rows).data();
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ route('client.active') }}",
                        data: {
                            id: rowData[0][0],
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            window.location.reload();
                        }
                    });
                }
            });

            $('#inactive').click(function() {
                if (confirm('Do you want to inactive this user?')) {
                    var rows = $('tr.selected');
                    var rowData = table.rows(rows).data();
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ route('client.inactive') }}",
                        data: {
                            id: rowData[0][0],
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            window.location.reload();
                        }
                    });
                }
            });

            $('#add').click(function() {
                //var dataArr = [];
                if (confirm('Do you want to add this user to server?')) {

                    var rows = $('tr.selected');
                    var rowData = table.rows(rows).data();
                    // $.each($(rowData),function(key,value){
                    //     dataArr.push(value["5"]); //"name" being the value of your first column.
                    // });
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ route('client.add.server') }}",
                        data: {
                            id: rowData[0][0],
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            window.location.reload();
                        }
                    });
                }
            });

            $('#delete').click(function() {
                if (confirm('Do you want to delete this user from server?')) {
                    var rows = $('tr.selected');
                    var rowData = table.rows(rows).data();
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ route('client.del.server') }}",
                        data: {
                            id: rowData[0][0],
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(data) {
                            window.location.reload();
                        }
                    });
                }
            });

            $('#bulk-active').click(function() {
                if (confirm('Do you want to active selected users?')) {
                    var formAction = "{{ route('client.active.bulk') }}";
                    $('#bulk-active-inactive-form').attr('method', 'post');
                    $('#bulk-active-inactive-form').attr('action', formAction);
                    $('#bulk-active-inactive-form').submit()
                } else {
                    return false;
                }
            });
            $('#bulk-inactive').click(function() {
                if (confirm('Do you want to inactivate selected users?')) {
                    var formAction = "{{ route('client.inactive.bulk') }}";
                    $('#bulk-active-inactive-form').attr('method', 'post');
                    $('#bulk-active-inactive-form').attr('action', formAction);
                    $('#bulk-active-inactive-form').submit()
                } else {
                    return false;
                }
            });
            $('#bulk-add-server').click(function() {
                if (confirm('Do you want to add selected users to server?')) {
                    var formAction = "{{ route('client.add.server.bulk') }}";
                    $('#bulk-active-inactive-form').attr('method', 'post');
                    $('#bulk-active-inactive-form').attr('action', formAction);
                    $('#bulk-active-inactive-form').submit()
                } else {
                    return false;
                }
            });
            $('#bulk-delete-server').click(function() {
                if (confirm('Do you want to delete selected users from server?')) {
                    var formAction = "{{ route('client.delete.server.bulk') }}";
                    $('#bulk-active-inactive-form').attr('method', 'post');
                    $('#bulk-active-inactive-form').attr('action', formAction);
                    $('#bulk-active-inactive-form').submit()
                } else {
                    return false;
                }
            });
        </script>
    @endif

    @role('Reseller')
        <script>
            $("#single-wrapper").remove();
        </script>
    @endrole

    <script>
        $(document).ready(function() {
            $(".datepicker").datepicker({
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });
        });
    </script>
@endsection

@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
    <link href='{{ asset('assets/css/datatablesSelect.min.css') }}' rel="stylesheet" type="text/css" />
    <link href='{{ asset('assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}' rel="stylesheet"
        type="text/css" />
@endsection
@section('custom_css')
    <style>
        .dataTable>thead>tr>th[class*=sort]:after {
            display: none;
        }

        .dataTable>thead>tr>th[class*=sort]:before {
            display: none;
        }

        table.dataTable tbody>tr.selected,
        table.dataTable tbody>tr>.selected {
            background-color: #292d30 !important;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/datatablesSelect.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/buttons.colVis.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/highchart/js/highcharts.js') }}"></script>
    <script src="{{ asset('assets/highchart/js/themes/grid.js') }}"></script>
@endsection
