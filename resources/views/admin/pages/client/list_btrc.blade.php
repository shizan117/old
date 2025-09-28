@extends ('admin.layouts.master')
@section('title')
    {{ $page_title }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <h4 class="m-t-0 header-title">{{ $page_title }}</h4>
                <table id="datatable"
                    class="table table-sm table-bordered table-responsive-sm table-responsive-lg table-responsive"
                    cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>client_type</th>
                            <th>connection_type</th>
                            <th>client_name</th>
                            <th>bandwidth_distribution_point</th>
                            <th>connectivity_type</th>
                            <th>activation_date</th>
                            <th>bandwidth_allocation</th>
                            <th>allocated_ip</th>
{{--                            <th>Local_IP_Address</th>--}}
                            {{-- <th>client_username</th> --}}
                            <!--<th>house_no</th>-->
                            <th>division</th>
                            <th>district</th>
                            <th>thana</th>
                            <th>address</th>
                            <th>client_mobile</th>
                            <th>client_email</th>
                            {{-- <th>unit_price_bdt</th> --}}
                            <th> selling_price_bdt_excluding_vat</th>


                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($clientData as $dataClient)
                            @if ($dataClient->plan->plan_price - $dataClient->discount > 0)
                                @php($p_price_without_vat = ceil((($dataClient->plan->plan_price - $dataClient->discount) * 100) / 105))
                                {{-- @php($p_price_without_discount = ceil($dataClient->plan->plan_price - $dataClient->discount)) --}}
                                <tr>
                                    <td>{{ $setting['type_of_client'] }}</td>
                                    <td>{{ $dataClient->type_of_connection }}</td>
                                    <td>{{ $dataClient->client_name }}</td>
                                    <td>{{ $dataClient->distribution->distribution }}</td>
                                    <td>{{ $dataClient->type_of_connectivity }}</td>
                                    <td>{{ date('d-m-Y', strtotime($dataClient->created_at)) }}</td>

                                    <td>{{ intval($dataClient->plan->bandwidth->bandwidth_allocation_mb) }}</td>
{{--                                    allocated_ip--}}
                                    <td>{{ $dataClient->username  }}</td>
{{--                                    Local_IP_Address  --}}
{{--                                    <td>{{ $users[$key]['address'] ?? ''}}</td>--}}
                                 

{{--                                   <td>{{ $dataClient->house_no }}</td>--}}
{{--                                    <td>{{ $dataClient->road_no }}</td>--}}
                                    <td>{{ $dataClient->district }}</td>
                                    <td></td>
                                    <td>{{ $dataClient->thana }}</td>
                                    <td>{{ $dataClient->address }}</td>
                                    {{-- <td>{{ $users[$key]['address'] }}</td> --}}
                                    <td>{{ $dataClient->phone }}</td>
                                    <td>{{ $dataClient->email }}</td>
                                     <td>{{ intval($dataClient->plan->plan_price) }}</td>
{{--                                    <td>{{ $p_price_without_vat }}</td>--}}

                                </tr>
                            @endif
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
        $('#datatable').DataTable({
            dom: 'Bfrtip',
            "pageLength": -1,
            buttons: ['excel']
        });
    </script>
@endsection

@section('required_css')
    <link href='{{ asset('assets/css/datatables.min.css') }}' rel="stylesheet" type="text/css" />
@endsection
@section('custom_css')
    <style>
        .dataTable>thead>tr>th[class*=sort]:after {
            display: none;
        }

        .dataTable>thead>tr>th[class*=sort]:before {
            display: none;
        }
    </style>
@endsection
@section('required_js')
    <script src="{{ asset('assets/js/datatables.min.js') }}" type="text/javascript"></script>
@endsection
