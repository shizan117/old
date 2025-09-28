<?php $user = Auth::user(); ?>
@extends ('admin.layouts.master')
@section('title','Solved Complain')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="m-t-0 header-title py-2 py-md-0">Pending Ticket List</h4>
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
                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Solved Date</th>
                        <th>Client Name</th>
                        <th>Ticket Type</th>
                        <th>Description</th>
                        <th>Action Taken</th>
                        <th>Assign To</th>
                        <th>Status</th>
                        <th class="hidden-print" style="width:70px">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @foreach ($complains as $complain)
                        <tr>
                            <td>{{ $complain->id }}</td>
                            <td>{{ date('d-M-y (h:i:a)',strtotime($complain->complain_date))}}</td>
                            <td>{{ date('d-M-y (h:i:a)',strtotime($complain->solved_date))}}</td>
                            <td><a href="{{ route('client.view', $complain->client->id) }}" target="_blank" data-toggle="tooltip" data-placement="right" title="{{ $complain->client->house_no.'-'.$complain->client->address }}">{{ $complain->client->client_name }} ({{$complain->client->username}})</a></td>
                            <td>{{ $complain->title }}</td>
                            <td>{{ $complain->description }}</td>
                            <td>{{ $complain->action_taken??'- - -' }}</td>
                            <td>{{ $complain->assignTo->name??'-' }}</td>
                            <td><span class="badge badge-{{ $complain->is_solved==0? 'danger':'success' }}">{{ $complain->is_solved==0? 'Not Solved':'Solved' }}</span></td>
                            <td class="hidden-print">
                                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('ticket_mark as unsolved'))
                                <a href="{{ route('complain.unsolve', $complain->id) }}"
                                   onclick="return confirm('Mark this complain as unsolved?')"
                                   class="btn-show" title="Mark as unsolved">
                                    <i class="fa fa-times"></i>
                                </a>
                                @endif
                                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('ticket_edit'))
                                <a href="{{ route('complain.edit', $complain->id) }}" class="btn-edit" title="Edit"><i class="fa fa-edit"></i></a>
                                @endif

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
    @include('admin.layouts.print-js')
    <script>
        function myFunction(name) {
          if(confirm("Are you sure to delete "+ name +"\'s invoice?")){
            event.preventDefault();
            document.getElementById('delete').submit();
          } else {
              event.preventDefault();
          }
        }
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
