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
    <div class="row">
        <div class="col-12">
            <!-- Modal -->
            <div class="modal fade" id="updateExpireDateModal" tabindex="-1" role="dialog"
                aria-labelledby="updateExpireDateModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateExpireDateModalLabel">Set Bulk Expire Date to Selected Clients
                            </h5>
                        </div>
                        <div class="modal-body">

                            <div id="successModalAlert">

                            </div>


                            <div class="progress d-none" id="bulk-date-progress-loading">
                                <div class="progress-bar progress-bar-striped bg-success progress-bar-animated"
                                    role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>

                            <form action="" id="bulkExpirationForm">
                                <input type="text" name="bulkExpiration" id="bulkExpiration"
                                    class="form-control datepicker" placeholder="YYYY-MM-DD" required>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <input type="button" value="Update" class="btn btn-primary" id="expireDateSubmitBtn">
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>


            <div class="card-box table-responsive">
                {{-- <h4 class="m-t-0 header-title">{{ $page_title }}</h4> --}}
                <div class="row">
                    <div class="col-md-6">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="w-100 pb-3 pb-md-0">
                                    <select name="resellerId" id="resellerId" class="form-control bg-secondary">
                                        <option value="">Super Admin</option>
                                        @foreach ($resellers as $reseller)
                                            <option value="{{ $reseller->resellerId }}"
                                                {{ request('resellerId') == $reseller->resellerId ? 'selected' : '' }}>
                                                {{ $reseller->resellerName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <div class="col-md-4">
                                <!-- Button trigger expire date modal -->
                                <button type="button" class="btn btn-outline-warning d-none w-100"
                                    id="updateExpireModlaShowBtn" data-toggle="modal" data-target="#updateExpireDateModal">
                                    Update Expire Date
                                </button>
                            </div>
                            <div class="col-md-4"></div>
                        </div>

                    </div>
                    <div class="col-md-6">
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

                <form id="bulk-active-inactive-form">
                    @csrf
                    <table id="datatable" class="table table-sm table-bordered table-responsive-sm" cellspacing="0"
                        width="100%">
                        <thead>

                            <tr>
                                <th><input type="checkbox" id="checkedAll" value="all"></th>
                                <th style="display: none" class="hidden-print"></th>
                                <th style="display: none" class="hidden-print"></th>
                                <th class="hidden-print">#</th>
                                <th>Client Name</th>
                                <th>Username</th>
                                <th>Phone</th>
                                <th>Reg Date</th>
                                <th>Box/Area</th>
                                <th>Plan</th>
                                <th>Server</th>
                                <th>Exp Date</th>
                                <th>Reseller</th>
                                <th>Status</th>
                                <th class="hidden-print" style="width:70px">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clientData as $dataClient)
                                @php
                                    $bgColor = '';
                                    if (number_format($dataClient['due'], 2) > 0 && $dataClient->status == 'On') {
                                        // DUE + ACTIVE
                                        $bgColor = 'background-color:  #87CEEB; color: #000;';
                                    } elseif (
                                        number_format($dataClient['due'], 2) > 0 &&
                                        $dataClient->status == 'Off'
                                    ) {
                                        // DUE + INACTIVE
                                        $bgColor = 'background-color: #ECF0F1; color: #000;';
                                    } elseif (
                                        number_format($dataClient['due'], 2) == 0 &&
                                        $dataClient->status == 'On'
                                    ) {
                                        // PAID + ACTIVE
                                        $bgColor = 'background-color: #086a34; color: #fff;';
                                    } elseif (
                                        number_format($dataClient['due'], 2) == 0 &&
                                        $dataClient->status == 'Off'
                                    ) {
                                        // PAID + INACTIVE
                                        $bgColor = 'background-color: #82E0AA; color: #000;';
                                    }

                                @endphp

                                <tr style="{{ $bgColor }}">
                                    @php($dataClient->status == 'On' ? ($status = 'Active') : ($status = 'Inactive'))
                                    <td style="display: none" class="hidden-print">{{ $dataClient->id }}</td>
                                    <td style="display: none" class="hidden-print">{{ $dataClient->server_status }}</td>
                                    <td>
                                        <input name="clientID[]" class="checkSingle" type="checkbox"
                                            value="{{ $dataClient->id }}">
                                    </td>
                                    <td></td>
                                    <td data-toggle="tooltip" data-placement="right"
                                        title="{{ $dataClient['house_no'] . '-' . $dataClient['address'] }}">
                                        {{ $dataClient['client_name'] }}</td>
                                    <td>{{ $dataClient['username'] }}</td>
                                    <td>{{ $dataClient['phone'] }}</td>
                                    <td>{{ $dataClient['created_at']->format('d-M-y') }}</td>
                                    <td>{{ $dataClient->distribution->distribution ?? '--' }}</td>
                                    <td>{{ $dataClient->plan->plan_name ?? '--' }}</td>
                                    <td>{{ $dataClient->plan->server->server_name ?? '--' }}</td>
                                    <td data-toggle="tooltip" data-placement="left"
                                        title="{{ date('h:i A', strtotime($dataClient['expiration'])) }}">
                                        {{ date('d-M-y', strtotime($dataClient['expiration'])) }} </td>
                                    <td>{{ $dataClient->reseller->resellerName ?? "Super Admin" }}</td>
                                    <td>{{ $status }}</td>
                                    <td class="hidden-print">
                                        <span style="display: flex; justify-content: center;">
                                            @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('client_profile'))
                                                <a href="{{ route('client.view', $dataClient->id) }}" target="_blank"
                                                    class="btn-show"><i class="fa fa-eye"
                                                        style="color: deepskyblue;"></i></a>
                                            @endif
                                            @if ($user->hasAnyRole('Super-Admin', 'Reseller') || $user->can('client_edit'))
                                            @endif

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
                [20, 30, 50, 100, 200, 500, -1],
                [20, 30, 50, 100, 200, 500, "All"]
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
    <script>
        $(document).ready(function() {
            $(".datepicker").datepicker({
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd'
            });
            $("#resellerId").on('change', function() {
                var resellerId = $("#resellerId").val()
                window.location.href = "{{ route($route_url) }}" + "?resellerId=" + resellerId;
            })
        });
    </script>

    <script>
        $(document).ready(function() {
            // Function to update button visibility based on selected checkboxes
            function updateButtonVisibility() {
                let anyChecked = $('.checkSingle:checked').length > 0;
                $("#updateExpireModlaShowBtn").toggleClass("d-none", !anyChecked);
            }

            // Handle "Select All" checkbox change event
            $('#checkedAll').on('change', function() {
                $('.checkSingle').prop('checked', $(this).prop('checked'));
                updateButtonVisibility(); // Update button visibility
            });

            // Handle individual checkbox change event
            $('.checkSingle').on('change', function() {
                updateButtonVisibility(); // Update button visibility
            });

            // Handle submit button click event
            $('#expireDateSubmitBtn').on('click', function() {
                let checkedClientIDs = [];
                let expire_date = $('#bulkExpiration').val();

                // Collect selected client IDs
                $('.checkSingle:checked').each(function() {
                    checkedClientIDs.push($(this).val());
                });

                // Validate input
                if (checkedClientIDs.length === 0 || !expire_date) {
                    alert("Please select at least one client and set an expiration date.");
                    return;
                }

                // Disable date picker input and show progress bar
                $('#bulkExpiration').prop('disabled', true);
                $('#expireDateSubmitBtn').prop('disabled', true);
                $('#bulk-date-progress-loading').removeClass('d-none'); // Show loading bar

                // Make the AJAX request
                $.ajax({
                    url: "{{ route('expire.bulk.clientsUpdate') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}", // Include CSRF token in POST requests
                        client_ids: JSON.stringify(checkedClientIDs),
                        bulkExpiration: expire_date
                    },
                    success: function(response) {
                        console.log("Success response:", response); // Log success response
                        // Show success message
                        // $('#bulk-date-success-alert').removeClass('d-none').addClass('alert-success');
                        // $('#bulk-date-success-alert').addClass('show');
                        // $('#bulk-date-success-alert strong').text(response.message);
                        $('#successModalAlert').html(`
                 <div class="alert alert-dismissible alert-success show fade mb-3" role="alert"
                                id="bulk-date-success-alert">
                                <strong>${response.message}</strong>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                `);
                        if (response.error) {
                            $('#successModalAlert').html(`
                 <div class="alert alert-dismissible alert-danger show fade mb-3" role="alert"
                                id="bulk-date-success-alert">
                                <strong>${response.message}</strong>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                `);
                        } else if (response.success) {
                            $('#successModalAlert').html(`
                 <div class="alert alert-dismissible alert-success show fade mb-3" role="alert"
                                id="bulk-date-success-alert">
                                <strong>${response.message}</strong>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                `);
                        }
                    },
                    error: function(error) {
                        console.log("Error response:", error); // Log error response
                        // Show error message
                        $('#bulk-date-error-alert').removeClass('d-none').addClass(
                            'alert-danger');
                        $('#bulk-date-success-alert').addClass('show');
                        $('#bulk-date-success-alert strong').text(
                            "An error occurred. Please try again.");
                    },
                    complete: function() {
                        // Re-enable form elements and hide progress bar
                        $('#bulkExpiration').prop('disabled', false);
                        $('#expireDateSubmitBtn').prop('disabled', false);
                        $('#bulk-date-progress-loading').addClass('d-none'); // Hide loading bar
                    }
                });
            });

            // Initial button visibility check
            updateButtonVisibility();
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
