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
        table.dataTable tbody>tr.selected a,
        table.dataTable tbody>tr>.selected a {
            color: rgb(35, 35, 255) !important;
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <h4 class=" pb-2 header-title">{{ $page_title }}</h4>

                <div class="bulkAction d-flex align-items-center pb-3">
                    <form id="restore-all-clients-form" action="{{ route('restore.all.client') }}" method="POST">
                        @csrf
                        @method('POST')
                        <button class="btn-success btn p-1" type="submit" onclick="warningOfAllRestore()">Restore All
                            Clients</button>
                    </form>

                    <form id="delete-all-clients-form" action="{{ route('delete.all.client') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn-danger btn p-1 ml-3" type="submit" onclick="warningOfAllDelete()">Delete All
                            Clients</button>
                    </form>


                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif


                <table id="datatable" class="table table-sm table-bordered table-responsive-sm table-responsive-lg"
                    cellspacing="0" width="100%">
                    <thead style="background-color: #ffc107;
                    color: #000;">
                        <tr>
                            <th style="display: none" class="hidden-print"></th>
                            <th style="display: none" class="hidden-print"></th>
                            <th>#</th>
                            <th>Client Name</th>
                            <th>Username</th>
                            <th>Box/Area</th>
                            <th>Phone</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Due</th>
                            <th class="hidden-print text-center">Manage</th>
                        </tr>
                    </thead>


                    <tbody>
                        @foreach ($clientData as $dataClient)
                            <tr style="color: #E59866;">
                                @php($dataClient->status == 'On' ? ($status = 'Active') : ($status = 'Inactive'))
                                <td style="display: none" class="hidden-print">{{ $dataClient->id }}</td>
                                <td style="display: none" class="hidden-print">{{ $dataClient->server_status }}</td>
                                <td></td>
                                <td data-toggle="tooltip" data-placement="right"
                                    title="{{ $dataClient['house_no'] . '-' . $dataClient['address'] }}">
                                    <a href="{{ route('client.view', $dataClient->id) }}" target="_blank"
                                        style="text-decoration: none; display: inline-block; border-bottom: 1px solid blue; padding-bottom: 1px;">
                                        {{ $dataClient->client_name }}
                                    </a>
                                </td>
                                <td>{{ $dataClient['username'] }}</td>
                                <td>{{ $dataClient->distribution->distribution }}</td>
                                <td>{{ $dataClient['phone'] }}</td>
                                <td>{{ $dataClient->plan->plan_name ?? '--' }}</td>
                                <td>{{ $status }} {{ $dataClient->id }}</td>
                                <td class="text-right">{{ number_format($dataClient['due'], 2) }}</td>
                                <td class="hidden-print text-center d-flex align-items-center justify-content-center"
                                    style="background: #2f3e47;">

                                    <form id="delete-client-form" action="{{ route('deleted.client') }}" method="POST">
                                        @csrf
                                        @method('POST') <!-- Use POST method for deletion -->
                                        <input type="hidden" name="id" value="{{ $dataClient->id }}">
                                        <!-- Hidden field to pass the ID -->
                                        <button style="background: transparent;" class="btn p-1" type="submit" onclick="confirmDeletion()">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>


                                    <form id="restore-client-form" action="{{ route('restore.client') }}" method="POST">
                                        @csrf
                                        @method('POST') <!-- Use POST method for deletion -->
                                        <input type="hidden" name="id" value="{{ $dataClient->id }}">
                                        <!-- Hidden field to pass the ID -->
                                        <button class="btn p-1 ml-1" style="background: transparent;" onclick="confirmRestoreClient()">
                                            <i class="fas fa-undo-alt text-primary"></i>
                                        </button>
                                    </form>
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
    <script>
        function confirmDeletion() {
            // Show confirmation dialog
            if (confirm('Are you sure you want to permanently delete this client?')) {
                // If confirmed, submit the form
                document.getElementById('delete-client-form').submit();
            }
        }

        function confirmRestoreClient() {
            // Show confirmation dialog
            if (confirm('Are you sure you want to restore this client?')) {
                // If confirmed, submit the form
                document.getElementById('restore-client-form').submit();
            }
        }

        function warningOfAllDelete() {
            // Show confirmation dialog
            if (confirm('Are you sure you want to delete all clients?')) {
                // If confirmed, submit the form
                document.getElementById('delete-all-clients-form').submit();
            }
        }


        function warningOfAllRestore() {
            // Show confirmation dialog
            if (confirm('Are you sure you want to restore all clients?')) {
                // If confirmed, submit the form
                document.getElementById('restore-all-clients-form').submit();
            }
        }
    </script>
    <script>
        var table = $('#datatable').DataTable({
            select: {
                style: 'single'
            },
            order: [],

            dom: 'Bfrtip',
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                //debugger;
                var index = iDisplayIndexFull + 1;
                $("td:eq(2)", nRow).html(index);
                return nRow;
            },

            "pageLength": 20,
            "lengthMenu": [
                [20, 30, 50, -1],
                [20, 30, 50, "All"]
            ],
            
            buttons: ['pageLength', 'excel', 'pdf',
                {
                    extend: 'print',
                    text: 'Print All',
                    autoPrint: true,
                    exportOptions: {
                        columns: [':not(.hidden-print)'],
                        modifier: {
                            page: 'all'
                        },
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

                    },
                    footer: true,

                }

            ]
        });
    </script>
@endsection

@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
    <link href='{{ asset('assets/css/datatablesSelect.min.css') }}' rel="stylesheet" type="text/css" />
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
