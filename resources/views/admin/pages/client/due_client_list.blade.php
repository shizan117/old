<?php
$user = Auth::user();
if ($user->branchId != null) $_branchName = $user->branch->branchName;
else $_branchName = '';
?>
@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <style>
        table.dataTable tbody > tr.selected a, table.dataTable tbody > tr > .selected a {
            color: rgb(35, 35, 255) !important;
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                {{--<div class="btn-group m-b-10">--}}
                {{--<div class="btn-group m-b-10">--}}
                {{--<a href="{{ route($branch_url) }}" class="btn btn-secondary">Branches Clients</a>--}}
                {{--<a href="{{ route($main_url) }}" class="btn btn-success">Clients</a>--}}
                {{--</div>--}}
                {{--</div>--}}

                <table id="datatable"
                       class="table table-sm table-bordered table-responsive-sm table-responsive-lg"
                       cellspacing="0" width="100%">
                    <thead style="background-color: #ffc107;
                    color: #000;">
                    <tr>
                        <th style="display: none" class="hidden-print"></th>
                        <th style="display: none" class="hidden-print"></th>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Username</th>
                        <th>Box/Area & Address</th>
                        <th>Phone</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th style="max-width: 67px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;">
                            Due Note
                        </th>
                        <th>EXP Date</th>

                        <th class="hidden-print">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @foreach ($clientData as $dataClient)
                        <tr style="color: #E59866;">
                            @php(($dataClient->status == 'On') ? $status = 'Active' : $status = 'Inactive')
                            <td style="display: none" class="hidden-print">{{ $dataClient->id }}</td>
                            <td style="display: none" class="hidden-print">{{ $dataClient->server_status }}</td>
                            <td></td>
                            <td data-toggle="tooltip" data-placement="right"
                                title="{{ $dataClient['house_no'].'-'.$dataClient['address'] }}">
                                <a href="{{ route('client.view', $dataClient->id) }}" target="_blank"
                                   style="text-decoration: none; display: inline-block; border-bottom: 1px solid blue; padding-bottom: 1px;">
                                    {{ $dataClient->client_name }}
                                </a>
                            </td>
                            <td>{{ $dataClient['username'] }}</td>
                            <td>{{ $dataClient->distribution->distribution }} [ {{$dataClient->address}} ]</td>
                            <td>{{ $dataClient['phone'] }}</td>
                            <td>{{ $dataClient->plan->plan_name??'--' }}</td>
                            <td>{{ $status }}</td>
                            <td class="text-right">{{ number_format($dataClient['due'],2) }}</td>
                            <td class="text-left" style="max-width: 67px; white-space: wrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $dataClient->due_client_note??' ' }}
                            </td>
                            {{--@php((empty($dataClient->branch)) ? $branch_name = '-' : $branch_name = $dataClient->branch->branchName)--}}
                            <td data-toggle="tooltip" data-placement="left"
                                title="{{ date('h:i A', strtotime($dataClient['expiration'])) }}">
                                {{ date('d-M-y', strtotime($dataClient['expiration'])) }} </td>
                            <td class="hidden-print text-center" style="background: #2f3e47;">
                                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can(['client_profile']))
                                    <a href="{{ route('client.view', $dataClient->id) }}" class="btn-show">
                                        <i class="fa fa-eye"></i></a>
                                @endif

                                @if(($dataClient->server_status == 1) && (!$dataClient->reseller || $dataClient->reseller->is_payment == 0))
                                    @if($user->hasAnyRole('Super-Admin','Reseller','Accountant') || $user->can(['client_payment collection']))
                                        <a href="{{ route('client.pay.due', $dataClient->id) }}" class="btn-view">
                                            <i class="fa fa-product-hunt"></i></a>
                                    @endif
                                @endif

                                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can(['client_edit']))


                                        <button type="button"
                                                class="btn-edit"
                                                data-toggle="modal"
                                                data-target="#editClientModal"
                                                data-id="{{ $dataClient->id }}"
                                                data-username="{{ $dataClient->username }}"
                                                data-note="{{ $dataClient->due_client_note }}"
                                                title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <style>.btn-edit {
                                                background: none;
                                                border: none;
                                                padding: 0;
                                                color: #007bff;
                                                cursor: pointer;
                                            }

                                            .btn-edit:hover {
                                                color: #0056b3;
                                            }
                                        </style>



                                    @endif



                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                    <tfoot>
                    <tr>
                        <th style="display: none" class="hidden-print"></th>
                        <th style="display: none" class="hidden-print"></th>
                        <th colspan="7" class="text-right">Total:</th>
                        <th class="text-right"></th>
                        {{--<th></th>--}}
                        <th></th>

                    </tr>
                    </tfoot>

                </table>

                <div class="modal fade" id="editClientModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <form id="editClientForm" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Add Note</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <div class="modal-body">
                                    <input type="hidden" name="id" id="edit-client-id">

                                    <label>Note for: <strong id="edit-client-username"></strong></label>
                                    <textarea name="due_client_note" id="edit-client-note" class="form-control"></textarea>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Note</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>


        </div>
    </div> <!-- end row -->
    <!-- end row -->
@endsection

@section('custom_js')
    {{-- JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#editClientModal').on('show.bs.modal', function (event) {
                let button = $(event.relatedTarget); // Button clicked
                let id = button.data('id');
                let username = button.data('username');
                let note = button.data('note');

                let modal = $(this);
                modal.find('#edit-client-id').val(id);
                modal.find('#edit-client-username').text(username);
                modal.find('#edit-client-note').val(note);

                // update form action dynamically
                let actionUrl = "{{ route('save.client.due', ':id') }}".replace(':id', id);
                modal.find('#editClientForm').attr('action', actionUrl);
            });
        });
    </script>
    <script>


        var table = $('#datatable').DataTable({
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;

                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };
                // Total over this page
                pageTotal = api
                    .column(9, {page: 'current'})
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                Total = api
                    .column(9)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(9).footer()).html(
                    '' + pageTotal.toFixed(2) + '<br>(Total: ' + Total.toFixed(2) + ')' + ''
                );
            },
            select: {
                style: 'single'
            },
            order: [],

            dom: 'Bfrtip',
            "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                //debugger;
                var index = iDisplayIndexFull + 1;
                $("td:eq(2)", nRow).html(index);
                return nRow;
            },

            "pageLength": 20,
            "stateSave": true,
            "lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]],
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

                    },
                    footer: true,

                }

            ]
        });
        $('div.dataTables_filter input').focus()
        // table.on( 'order.dt search.dt', function () {
        // table.column(2, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
        //     cell.innerHTML = i+1;
        // } );
        // } ).draw();

        $('table').before('<div id="sticky-button-wrapper"><button type="submit" id="add" class="btn btn-sm btn-primary" disabled onclick="return confirm(\'Do you want to add this user to server?\');""><i class="mdi mdi-plus"></i></button>' +
            '<button type="submit" id="delete" class="btn btn-sm btn-danger" disabled onclick="return confirm(\'Do you want to delete this user from server?\');"><i class="mdi mdi-minus"></i></button>' +
            '<button type="submit" id="active" class="btn btn-sm btn-info" disabled onclick="return confirm(\'Do you want to active this user?\');"><i class="mdi mdi-check"></i></button>' +
            '<button type="submit" id="inactive" class="btn btn-sm btn-warning" disabled onclick="return confirm(\'Do you want to inactive this user?\');"><i class="mdi mdi-close"></i></button></div>');
        table.on('select', function (e, dt, type, indexes) {
            var rowData = table.rows(indexes).data().toArray();
            if (rowData[0][1] == 1) {
                if (rowData[0][7] === "Active") {
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
            .on('deselect', function (e, dt, type, indexes) {
                var rowData = table.rows(indexes).data().toArray();
                $('#add').prop("disabled", true);
                $('#delete').prop("disabled", true);
                $('#active').prop("disabled", true);
                $('#inactive').prop("disabled", true);
            });

        $('#inactive').click(function () {
            var rows = $('tr.selected');
            var rowData = table.rows(rows).data();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ route('client.inactive') }}",
                data: {id: rowData[0][0], _token: '{{ csrf_token() }}'},
                success: function (data) {
                    window.location.reload();
                }
            });
        });

        $('#active').click(function () {
            var rows = $('tr.selected');
            var rowData = table.rows(rows).data();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ route('client.active') }}",
                data: {id: rowData[0][0], _token: '{{ csrf_token() }}'},
                success: function (data) {
                    window.location.reload();
                }
            });
        });

        $('#delete').click(function () {
            var rows = $('tr.selected');
            var rowData = table.rows(rows).data();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ route('client.del.server') }}",
                data: {id: rowData[0][0], _token: '{{ csrf_token() }}'},
                success: function (data) {
                    window.location.reload();
                }
            });
        });

        $('#add').click(function () {
            //var dataArr = [];
            var rows = $('tr.selected');
            var rowData = table.rows(rows).data();
            // $.each($(rowData),function(key,value){
            //     dataArr.push(value["5"]); //"name" being the value of your first column.
            // });
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "{{ route('client.add.server') }}",
                data: {id: rowData[0][0], _token: '{{ csrf_token() }}'},
                success: function (data) {
                    window.location.reload();
                }
            });
        });

    </script>

@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
    <link href='{{ asset("assets/css/datatablesSelect.min.css") }}' rel="stylesheet" type="text/css"/>

@endsection
@section('custom_css')
    <style>
        .dataTable > thead > tr > th[class*=sort]:after {
            display: none;
        }

        .dataTable > thead > tr > th[class*=sort]:before {
            display: none;
        }

        table.dataTable tbody > tr.selected, table.dataTable tbody > tr > .selected {
            background-color: #292d30 !important;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/datatablesSelect.min.js') }}" type="text/javascript"></script>
@endsection
