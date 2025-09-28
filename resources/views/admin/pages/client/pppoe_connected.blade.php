@extends ('admin.layouts.master')


@section('title')
    Connected PPPoE Client
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <div id="ajax-message"></div>
                @if(session('message'))
                    <div class="alert {{ session('m-class') }}">
                        {{ session('message') }}
                    </div>
                @endif

{{--                <div class="row">--}}
{{--                    <div class="col-md-5">--}}
{{--                        <!-- Dynamic heading updated via JS -->--}}
{{--                        <h4 class="m-t-0 header-title m-b-10" id="total-clients"></h4>--}}
{{--                    </div>--}}
{{--                </div>--}}

                <div id="loading-card" style="position: absolute; top: 45%; left:40%; z-index: 900; text-align:center;">
                    <div class="loader-container">
                        <div class="loader-spinner-wrapper">
                            <div class="loader-spinner"></div>
                            <p class="loader-timer-label"><span id="loading-timer">0</span>s</p>
                        </div>
                        <p class="loader-text">Checking server connection...</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 col-md-4 mb-2">
                        <h5 class="mb-0">
                            Total: <span id="total-count">0</span> |
                            Active: <span id="active-count" class="text-success">0</span> |
                            Disconnected: <span id="disconnected-count" class="text-danger">0</span>
                        </h5>

                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <h5 class="mb-0">
                            Data auto-refreshes every 5 minutes. </h5>

                    </div>

                    <div class="col-12 col-md-2 mb-2">
                        @unlessrole('Reseller')
                        <select name="resellerId" id="resellerId" class="form-control form-control-sm">
                            <option value="">Select Reseller</option>
                            <option value="all" {{ (request('resellerId')=='all')?'selected':'' }}>All Reseller</option>
                            @foreach($resellers as $reseller)
                                <option value="{{ $reseller->resellerId }}" {{ (request('resellerId')==$reseller->resellerId)?'selected':'' }}>
                                    {{ $reseller->resellerName }}
                                </option>
                            @endforeach
                        </select>
                        @endunlessrole
                    </div>

                    <div class="col-12 col-md-3 text-md-end">
                        <div class="btn-group w-100 w-md-auto">
                            <button id="filter-all" class="btn btn-sm btn-secondary">All</button>
                            <button id="filter-active" class="btn btn-sm btn-success">Connected</button>
                            <button id="filter-disconnected" class="btn btn-sm btn-danger">Disconnected</button>
                        </div>
                    </div>
                </div>




                <table id="datatables" class="table table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th style="display: none">ID</th>
                        <th>Client Name</th>
                        <th>Username</th>
                        <th>Reseller</th>
                        <th>Mac Address</th>
                        <th>Connected IP</th>
                        <th>Connected Time</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/datatablesSelect.min.js') }}" type="text/javascript"></script>

    <script>
        $(document).ready(function () {


            // Filter buttons
            $('#filter-all').on('click', function () {
                table.column(7).search('').draw(); // uptime column
            });
            $('#filter-active').on('click', function () {
                table.column(7).search('^(?!router_off|using_no_microtik).*', true, false).draw();
            });
            $('#filter-disconnected').on('click', function () {
                table.column(7).search('router_off|using_no_microtik', true, false).draw();
            });



            let table;
            let currentClients = {};
            let isFirstLoad = true;
            let countdownValue;
            const initialTimeout = 40;
            const timeoutExtension = 5;

            $("#resellerId").on('change', function() {
                var resellerId = $("#resellerId").val();
                window.location.href = "{{ route($route_url) }}" + "?resellerId=" + resellerId;
            });

            var timerInterval;

            function startTimer() {
                countdownValue = initialTimeout;
                $('#loading-timer').text(countdownValue);

                timerInterval = setInterval(function () {
                    countdownValue--;
                    $('#loading-timer').text(countdownValue);

                    if (countdownValue <= 1) {
                        countdownValue += timeoutExtension;
                        $('#loading-timer').text(countdownValue);
                    }
                }, 1000);
            }

            function stopTimer() {
                clearInterval(timerInterval);
                $('#loading-card').hide();
            }

            function updateTableWithData(clientData) {
                let newClients = {};
                let activeCount = 0;
                let disconnectedCount = 0;

                clientData.forEach(client => {
                    newClients[client.name] = client;
                    if (client.uptime !== 'router_off' && client.uptime !== 'using_no_microtik') {
                        activeCount++;
                    } else {
                        disconnectedCount++;
                    }
                });

                // Update counts
                $('#total-count').text(clientData.length);
                $('#active-count').text(activeCount);
                $('#disconnected-count').text(disconnectedCount);

                if (isFirstLoad) {
                    table = $('#datatables').DataTable({
                        data: clientData,
                        responsive: true,
                        columns: [
                            {
                                data: null,
                                render: (data, type, row, meta) => meta.row + 1,
                                orderable: false,
                                searchable: false
                            },
                            { data: 'id', visible: false },
                            { data: 'client_name' },
                            { data: 'name' },
                            { data: 'reseller_name' },
                            { data: 'caller-id' },
                            { data: 'address' },
                            { data: 'uptime' },
                            {
                                data: 'uptime',
                                orderable: false,
                                searchable: false,
                                render: function (data, type, row) {
                                    let statusIcon = '';
                                    if (data === 'using_no_microtik') {
                                        statusIcon = `<i title="MikroTik Disconnected" class="fa fa-circle" style="color:#ff5b5b;"></i>`;
                                    } else if (data !== 'router_off') {
                                        statusIcon = `<i title="MikroTik Connected" class="fa fa-circle" style="color:#0eac5c;"></i>`;
                                    } else {
                                        statusIcon = `<i title="Router Off/Disconnected" class="fa fa-circle" style="color:#ff5b5b;"></i>`;
                                    }

                                    return `
                            ${statusIcon}
                            <a href="/adminisp/clients/client-view/${row.id}" class="btn-show" title="View Client">
                                <i class="fa fa-eye" style="color: deepskyblue;"></i>
                            </a>
                        `;
                                }
                            }
                        ],
                        paging: true,
                        "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
                        searching: true,
                        ordering: true,
                        info: true,
                        autoWidth: false,
                        dom: 'Bfrtip',
                        buttons: ['pageLength'],
                        language: { emptyTable: "No connected clients found" }
                    });
                    isFirstLoad = false;
                } else {
                    table.clear().rows.add(clientData).draw();
                }

                currentClients = { ...currentClients, ...newClients };
            }


            function updateHeading(total, type) {
                let headingText = '';
                if (type === 'all') headingText = `All Reseller Clients: ${total}`;
                else if (type === 'local') headingText = `Local Connected Clients: ${total}`;
                else headingText = `Reseller Connected Clients: ${total}`;
                $("#total-clients").text(headingText);
            }

            // Step 1: Fast Load - first server only
            function fetchInitialData() {
                $.ajax({
                    url: "{{ route('pppoe.client.connected.data') }}",
                    type: "GET",
                    data: { resellerId: "{{ request('resellerId') }}", stage: 'fast' },
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'error') {
                            stopTimer();
                            $('#ajax-message').html(`<div class="alert alert-danger">${res.message}</div>`);
                            return;
                        }
                        updateTableWithData(res.clientData);
                        updateHeading(res.total, 'local');
                    },
                    error: function(xhr, status, error) { console.error('Initial load failed:', error); }
                });
            }

            // Step 2: Full Load - all servers
            function fetchFullData() {
                $.ajax({
                    url: "{{ route('pppoe.client.connected.data') }}",
                    type: "GET",
                    data: { resellerId: "{{ request('resellerId') }}", stage: 'full' },
                    dataType: "json",
                    success: function (res) {
                        stopTimer();
                        if (res.status === 'error') {
                            $('#ajax-message').html(`<div class="alert alert-danger">${res.message}</div>`);
                            return;
                        }
                        updateTableWithData(res.clientData);
                        let resellerId = "{{ request('resellerId') }}";
                        let type = resellerId === 'all' ? 'all' : (resellerId === '' || resellerId === 'null' ? 'local' : 'reseller');
                        updateHeading(res.total, type);
                    },
                    error: function(xhr, status, error) {
                        stopTimer();
                        $('#ajax-message').html(`<div class="alert alert-danger">Error loading data: ${error}</div>`);
                    }
                });
            }

            // Start initial fast load
            $('#loading-card').show();
            startTimer();
            fetchInitialData();

            // Fetch full data after short delay
            setTimeout(fetchFullData, 10000);

            // Refresh full data every 5 min
            setInterval(fetchFullData, 300000);

        });
    </script>

@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
    <link href='{{ asset("assets/css/datatablesSelect.min.css") }}' rel="stylesheet" type="text/css"/>
@endsection

@section('custom_css')

    <style>
        /* Only for devices around 979px width */

        @media (min-width: 820px) and (min-height: 1180px) {
            .row.mb-3 {
                margin-top: 72px !important;
                margin-right: 62px !important;
            }
        }

        #loading-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px 40px;
            z-index: 1000;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
            text-align: center;
        }
        .loader-container { display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .loader-spinner-wrapper { position: relative; width: 80px; height: 80px; margin-bottom: 15px; }
        .loader-spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 100%;
            height: 100%;
            animation: spin 2s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
            position: absolute;
            top: 0; left: 0;
        }
        .loader-timer-label {
            font-size: 24px; font-weight: bold; color: #555;
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
        }
        .loader-text { color: #777; font-size: 16px; margin: 0; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .dataTable > thead > tr > th[class*=sort]:after,
        .dataTable > thead > tr > th[class*=sort]:before { display: none; }
        table.dataTable tbody > tr.selected,
        table.dataTable tbody > tr > .selected { background-color: #354650 !important; }
    </style>
@endsection
