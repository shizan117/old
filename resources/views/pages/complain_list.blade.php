@extends ('layouts.master')
@section('title','Complain List')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box table-responsive">

                <h4 class="m-t-0 header-title">Complain List</h4>
                <a href="{{ route('client.complain.create') }}" class="btn btn-primary">New Complain</a>
                {{--<div class="btn-group m-b-10">--}}
                    {{--<div class="btn-group m-b-10">--}}
                        {{--@if($role_id == 1 OR $role_id == 2 OR $role_id == 5)--}}
                        {{--<a href="{{ route('invoice.branches') }}" class="btn btn-secondary">Branches Client's Invoice List</a>--}}
                        {{--<a href="{{ route('invoice.index') }}" class="btn btn-success">All Client's Invoice List</a>--}}
                        {{--@elseif($role_id == 4)--}}
                            {{--<a href="{{ route('invoice.seller') }}" class="btn btn-custom">Seller's Invoice List</a>--}}
                            {{--<a href="{{ route('invoice.index') }}" class="btn btn-success">Client's Invoice List</a>--}}
                        {{--@endif--}}
                        {{--<a href="{{ route('invoice.add') }}" class="btn btn-primary">Create New Invoice</a>--}}
                    {{--</div>--}}
                {{--</div>--}}


                <table id="datatable" class="table table-striped table-sm table-bordered table-responsive-sm table-responsive-lg" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Complain Date</th>
                        <th>Complain Title</th>
                        <th>Description</th>
                        <th>Action Taken</th>
                        <th>Status</th>
                        <th class="hidden-print" style="width:70px">Manage</th>
                    </tr>
                    </thead>


                    <tbody>
                    @foreach ($complainData as $complain)
                        <tr>

                            <td>{{ $complain->id }}</td>
                            <td>{{ date('d-M-y (h:i:a)',strtotime($complain->complain_date))}}</td>
                            <td>{{ $complain->title }}</td>
                            <td>{{ $complain->description }}</td>
                            <td>{{ $complain->action_taken }}</td>
                            <td><span class="badge badge-{{ $complain->is_solved==0? 'danger':'success' }}">{{ $complain->is_solved==0? 'Not Solved':'Solved' }}</span></td>
                            @if(!$complain->is_solved)
                            <td class="hidden-print">
                                <a href="{{ route('client.complain.edit', $complain->id) }}" class="btn-edit" title="Edit"><i class="fa fa-edit"></i></a>
                                <a href="" onclick="return myFunction('{{ $complain->client->client_name }}')" class="btn-del" title="Delete">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                                <form id="delete" action="{{ route('client.complain.delete', $complain->id) }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </td>
                            @else
                                <td>-</td>
                            @endif
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
          if(confirm("Are you sure to delete this complain?")){
            event.preventDefault();
            document.getElementById('delete').submit(); 
          } else {
              event.preventDefault();
          }
        }
    </script>  
@endsection

@section('required_css')
    <link href='{{ asset("assets/css/datatables.min.css") }}' rel="stylesheet" type="text/css"/>
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
@endsection