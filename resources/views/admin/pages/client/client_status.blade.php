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
                <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                <table id="datatable" class="table table-sm table-bordered table-responsive-sm" cellspacing="0" width="100%">
                    <thead style="background-color: #ffc107; color: #000;">
                        <tr>
                            <th>#</th>
                            <th>Client Name</th>
                            <th>Username</th>
                            {{-- <th>Active Time</th> --}}
                            <th>Expiration</th>
                            {{-- <th>Status</th> --}}
                            <th>Due</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($clientData as $client)
                            <tr>
                                @php($client->status == 'On' ? ($status = 'Active') : ($status = 'Inactive'))
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $client->client_name }}</td>
                                <td>{{ $client->username }}</td>
                                {{-- <td>{{ $client->uptime }}</td> --}}
                                <td data-toggle="tooltip" data-placement="left"
                                    title="{{ date('h:i A', strtotime($client->expiration)) }}">
                                    {{ date('d-M-y', strtotime($client->expiration)) }} </td>
                                    {{-- <td>{{ $status }}</td> --}}
                                    <td>{{ $client->due }}</td>
                                <td class="text-center">
                                    @if ($client->uptime == 'using_no_microtik')
                                        <i title="MikroTik Disconnected" data-toggle="tooltip" class="fa fa-circle"
                                            style="color: #ff5b5b;"></i>
                                    @elseif ($client->uptime != 'router_off')
                                        <i title="{{ $client->uptime }}" data-toggle="tooltip" class="fa fa-circle"
                                            style="color: #0eac5c;"></i>
                                    @else
                                        <i title="Router Off/Disconnected" data-toggle="tooltip" class="fa fa-circle"
                                            style="color: #ff5b5b;"></i>
                                    @endif
                                    <a href="{{ route('client.view', $client->client_id) }}" class="btn-show">
                                        <i class="fa fa-eye" style="color: deepskyblue;"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection

@section('custom_js')
    {{--    @include('admin.layouts.print-js')--}}
    <script type="text/javascript">
        $(document).ready(function() {
            //Buttons examples
            $('#datatable').DataTable({
                dom: 'Bfrtip',
                "pageLength": 50,
                "lengthMenu": [
                    [50, 100, 200, -1],
                    [50, 100, 200, "All"]
                ],
                buttons: ['pageLength',
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