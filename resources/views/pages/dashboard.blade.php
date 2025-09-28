<section id="masterLayoutForLargeScreen"></section>
<script>
    // Function to handle the resize event
    function handleResize() {
        if (window.innerWidth > 575) {
            document.getElementById('masterLayoutForLargeScreen').innerHTML = `@extends('layouts.master')`;
            document.getElementById('masterLayoutForLargeScreen').innerHTML =
                `@section('title')
                                                                                    Dashboard
                                                                                @endsection')`;
        }
    }
    // Initial check when the page loads
    handleResize();

    // Listen for the resize event
    window.addEventListener('resize', handleResize);
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />


<style>
    .btn-bandwidth {
        text-color: #ffc107;
    }

    #mobileViewForClient {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: #fff;
        z-index: 99999999;
        overflow: auto;
        padding-bottom: 80px;
    }

    #mobileViewForClient * {
        color: #000;
    }

    .clientLogo {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 2px solid #ccc;
    }

    .clientInfo * {
        margin: 0;
        padding: 0;
        line-height: 1;
    }

    .clientName {
        font-size: 16px;
        font-weight: 600;
        padding-bottom: 3px;
    }

    .clientStatus,
    .clientUsername {
        font-size: 15px;
        font-weight: 400;
    }

    #mobileHeaderPart {
        padding: 12px 0;
        background: rgb(2, 0, 36);
        background: linear-gradient(90deg, rgba(2, 0, 36, 1) 0%, rgba(9, 9, 121, 1) 35%, rgba(0, 212, 255, 1) 100%);
        border-radius: 0 0 15px 15px;
    }

    .activeClient {
        font-size: 12px;
        font-weight: 700;
        padding: 2px 3.5px;
        border-radius: 5px;
        background: #fff;
        color: green !important;
    }

    #noticeOfClient {
        padding: 10px 0;
    }

    .noticeTextCtrl {
        box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
        padding: 3px 2px;
        font-size: 13px;
        font-weight: 500;
        color: orange !important;
    }

    .homeBtn i {
        font-size: 20px;
    }

    .homeBtn span {
        font-size: 12px;
    }

    #footerBtns {
        position: fixed;
        width: 100%;
        z-index: 999999;
        bottom: 0;
        left: 0;
    }

    #footerBtns .container {
        box-shadow: rgb(204, 219, 232) 3px 3px 6px 0px inset, rgba(255, 255, 255, 0.5) -3px -3px 6px 1px inset;
        background: #fff;
    }

    .footerBtnCtrl {
        padding: 5px 0;
    }

    .logOutBtn {
        background: transparent;
        border: 1px solid #fff !important;
        padding: 5px 10px !important;
    }

    .logOutBtn span {
        font-size: 10px !important;
        font-weight: 500 !important;
        color: #fff !important;
    }

    .quickLinkCtrl .card {
        background: #fff;
        text-align: center;
        padding: 10px 5px;
        border-radius: 5px;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
        border-color: #fff;
        transition: 03s;
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .quickLinkCtrl .card:hover {
        border: 1px solid #ccc;
    }

    .quickLinkCtrl .card i {
        font-size: 25px;
        color: #8bc92d !important;
    }

    .quickLinkCtrl .card p {
        font-size: 13px;
        flex-wrap: 500;
        margin: 0;
        padding: 0;
        padding-top: 5px;
    }

    .quickDetailsCtrl .icon img {
        width: 35px;
    }

    .quickDetailsCtrl .detailsNote {
        padding-left: 10px;
    }

    .quickDetailsCtrl .detailsNote p {
        font-size: 12px;
        font-weight: 400;
        line-height: 1;
    }

    .quickDetailsCtrl .detailsNote h6 {
        font-size: 12px;
        line-height: 1;
        font-weight: 700;
    }

    .quickDetailsCtrl {
        padding: 10px;
        margin-top: 15px;
        border-radius: 5px;
        box-shadow: inset 0px 0px 15px 8px rgba(0, 0, 0, 0.1);
    }

    .liveGraphCtrl {
        box-shadow: 0px 0px 16px 6px rgba(0, 0, 0, 0.1);
        margin-top: 15px;
        padding: 10px;
    }
</style>


<section id="mobileViewForClient" class="d-md-none">
    <section id="mobileHeaderPart">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <div class="leftHeader d-flex align-items-center">

                        @if (file_exists('assets/images/clients/' . $user->user_image))
                            <a href="{{ route('client.profile.edit') }}">
                                <img src="{{ asset('assets/images/clients/' . $user->user_image) }}" alt="profile"
                                    class="img-fluid clientLogo">
                            </a>
                        @else
                            <a href="{{ route('client.profile.edit') }}">
                                <img src="{{ asset('assets/client_mobile_view/profile.png') }}" alt="profile"
                                    class="img-fluid clientLogo">
                            </a>
                        @endif

                        <div class="clientInfo pl-3">
                            <h5 class="clientName text-light">{{ $user->client_name }}</h5>
                            <p class="clientUsername text-light py-1"> UserId: <span
                                    style="font-weight: 500; color: orange;">
                                    {{ $user->username }}</span></p>
                            <h6 class="clientStatus text-light">Status: <span
                                    class="activeClient">{{ $user->status == 'On' ? 'ON' : 'OFF' }}</span></h6>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="clientMenu d-flex align-items-end justify-content-center flex-column text-right h-100">
                        {{-- <button class="btn menuOpenBtn"><i class="fa-solid fa-bars text-light"></i></button> --}}

                        <button class="btn logOutBtn" onclick="$('#logout-form').submit()">
                            <i class="fa-solid fa-right-from-bracket text-light"></i>
                            <span> Log Out</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="noticeOfClient">
        <div class="container">
            <marquee class="noticeTextCtrl">
                @if ($user->resellerId != '')
                    {{ $user->reseller->notice }}
                @else
                    {{ $setting['notice'] }}
                @endif
            </marquee>
        </div>
    </section>

    <section id="footerBtns">
        <div class="container">
            <div class="footerBtnCtrl d-flex justify-content-between">
                <a href="{{ route('home') }}" class="btn homeBtn text-center">
                    <i class="fa-solid fa-house-chimney"></i>
                    <br>
                    <span>Home</span>
                </a>

                <a href="{{ route('client.pay') }}" class="btn homeBtn text-center">
                    <i class="fa-solid fa-money-bill"></i>
                    <br>
                    <span>Pay Due</span>
                </a>

                <a href="{{ route('client.receipt') }}" class="btn homeBtn text-center">
                    <i class="fa-regular fa-credit-card"></i>
                    <br>
                    <span>Payment History</span>
                </a>

                <a href="tel:{{ setting('phone') }}" class="btn homeBtn text-center">
                    <i class="fa-solid fa-phone"></i>
                    <br>
                    <span>Support</span>
                </a>
            </div>
        </div>
    </section>

    <section id="qucikLinksOfClient">
        <div class="container">
            <div class="quickLinkCtrl">
                <div class="row row-cols-2 g-2">
                    <div class="col">
                        <a href="{{ route('client.invoice') }}">
                            <div class="card">
                                <i class="fa-solid fa-file-invoice"></i>
                                <p>Invoices</p>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('client.receipt') }}">
                            <div class="card">
                                <i class="fa-solid fa-rectangle-list"></i>
                                <p>Receipt</p>
                            </div>
                        </a>
                    </div>
                    <div class="col">
                        <a href="{{ route('client.complain.index') }}">
                            <div class="card">
                                <i class="fa-solid fa-headset"></i>
                                <p>Complain</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="quickDetails">
        <div class="container">
            <div class="quickDetailsCtrl">
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex align-items-center py-2">
                            <div class="icon">
                                <img src="{{ asset('assets/client_mobile_view/bill.png') }}" alt="bill"
                                    class="img-fluid">
                            </div>
                            <div class="detailsNote">
                                <p class="m-0 p-0">Due Bill</p>
                                <h6 class="m-0 p-0">{{ $user->due }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center py-2">

                            @php
                                $planName = DB::table('plans')
                                    ->where('id', $user->plan_id)
                                    ->value('plan_name');
                            @endphp

                            <div class="icon">
                                <img src="{{ asset('assets/client_mobile_view/package.png') }}" alt="package"
                                    class="img-fluid">
                            </div>
                            <div class="detailsNote">
                                <p class="m-0 p-0">Package</p>
                                <h6 class="m-0 p-0">{{ $planName ?? 'N/A' }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center py-2">
                            <div class="icon">
                                <img src="{{ asset('assets/client_mobile_view/advance.png') }}" alt="advance"
                                    class="img-fluid">
                            </div>
                            <div class="detailsNote">
                                <p class="m-0 p-0">Paid (Advance)</p>
                                <h6 class="m-0 p-0">{{ $user->balance }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center py-2">
                            <div class="icon">
                                <img src="{{ asset('assets/client_mobile_view/date.png') }}" alt="date"
                                    class="img-fluid">
                            </div>
                            <div class="detailsNote">
                                <p class="m-0 p-0">Expire Date</p>
                                <h6 class="m-0 p-0">
                                    {{ date('d-M-Y h:i A', strtotime($user->expiration)) }}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="liveGraphTrack">
        <div class="container">
            <div class="liveGraphCtrl">
                @if ($user->status == 'On' && setting('using_mikrotik') && setting('client_bandwidth'))
                    <a style="visibility: hidden; width: 0; height: 0; position:absolute; left: -500px;"
                        id="chartShow1" data-toggle="modal" data-target="#trafficView1"
                        data-id="{{ $user->id }}" class="">
                        Bandwidth &nbsp;&nbsp;<i class="fa fa-bar-chart"></i></a>
                @endif

                <!-- Add traffic modal -->
                <div class="fade text-left pb-3" id="trafficView1" tabindex="-1" role="dialog"
                    aria-labelledby="myModalLabel33" aria-hidden="false">
                    <div class="modal-dialog traffic-graph modal-lg" role="document" style="border: none;">
                        <h5 class="text-left" style="font-weight: 700">Live Speed:</h5>
                        {{-- <div id="traffic" class="pl-0 pt-0 pb-0 text-center" style="border: none;"></div> --}}
                        <div class="modal-content pt-3" style="border: none;">
                            <div id="container1" style="min-width: 67%; margin: 0 auto; border: none;"></div>
                            <input type=hidden name="interface" id="interface1" />

                        </div>
                    </div>
                </div>
                <!-- Modal End -->

            </div>
        </div>
    </section>

</section>

@section('content')
    <div class="d-none d-md-block">

        <div class="row">

            <div class="col-sm-12 col-md-2">

                @if ($user->status == 'On' && setting('using_mikrotik') && setting('client_bandwidth'))
                    <a href="" id="chartShow" data-toggle="modal" data-target="#trafficView"
                        data-id="{{ $user->id }}" class="btn btn-outline-warning btn-block">
                        Bandwidth &nbsp;&nbsp;<i class="fa fa-bar-chart"></i></a>
                @endif
            </div>
            <div class="col-sm-12 col-md-8">
                <marquee class="badge badge-purple" style="font-size: 22px; padding: 5px;">
                    @if ($user->resellerId != '')
                        {{ $user->reseller->notice }}
                    @else
                        {{ $setting['notice'] }}
                    @endif
                </marquee>
            </div>
            <div class="col-sm-12 col-md-2">
                <a href="{{ route('client.pay') }}" class="btn btn-outline-warning btn-block">Pay Due Bill</a>
            </div>
            <div class="col-sm-12">
                <div class="row">

                    <div class="col-xl-3 col-md-6">
                        <div class="card-box badge-purple">

                            <h4 class="header-title mt-0 m-b-30">Active Plan</h4>

                            <div class="widget-box-2">
                                <div class="widget-detail-2">
                                    <h2 class="p-t-10 mb-0"> {{ $user->plan->plan_name }} </h2>

                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->

                    {{-- <div class="col-xl-3 col-md-6">
                        
                        
                        @if ($user->status == 'On' && setting('using_mikrotik'))
                        
                        <a class="btn-show" id="chartShow" data-toggle="modal" style="cursor: pointer;"  data-target="#trafficView" data-id="{{ $user->id }}">
                          <i class="fa fa-bar-chart text-warning"></i>
                        </a>
                        @endif
                   
                    </div> --}}

                    <div class="col-xl-3 col-md-6">
                        <div class="card-box badge-success">
                            <h4 class="header-title mt-0 m-b-30">Balance</h4>

                            <div class="widget-box-2">
                                <div class="widget-detail-2">
                                    <h2 class="p-t-10 mb-0"> {{ $setting['currencyCode'] . ' ' . $user->balance }} </h2>

                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <div class="card-box badge-danger">

                            <h4 class="header-title mt-0 m-b-30">Due</h4>

                            <div class="widget-box-2">
                                <div class="widget-detail-2">
                                    <h2 class="p-t-10 mb-0"> {{ $setting['currencyCode'] . ' ' . $user->due }} </h2>

                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card-box badge-primary">
                            <h4 class="header-title mt-0 m-b-30">Status</h4>

                            <div class="widget-box-2">
                                <div class="widget-detail-2">
                                    <h2 class="p-t-10 mb-0">{{ $user->status }}</h2>

                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <!--<div class="col-xl-3 col-md-6">
                                                <!--    <div class="card-box badge-success">-->
                    <!--        <h4 class="header-title mt-0 m-b-30">Bandwidth</h4>-->
                    <!--        <div class="widget-box-2">-->
                    <!--            <div class="widget-detail-2">-->
                    <!--            @if ($user->status == 'On' && setting('using_mikrotik') && setting('client_bandwidth'))
    -->
                    <!--            <a class="btn-show" id="chartShow" data-toggle="modal" style="cursor: pointer;"  data-target="#trafficView" data-id="{{ $user->id }}">-->
                    <!--             <i style="font-size: 40px" class="fa fa-bar-chart text-warning"></i>-->
                    <!--             </a>-->
                    <!--
    @endif-->
                    <!--                <p class="m-b-10">Bandwidth</p>-->
                    <!--            </div>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div><!-- end col -->



                </div>
                <!-- end row -->

                <div class="row">
                    <div class="col-xl-4">
                        <div class="card-box">
                            <h4 class="header-title mt-0 m-b-30">Client Details</h4>

                            <div class="inbox-widget nicescroll" style="height: 315px;">
                                <div class="inbox-item">
                                    <div class="pull-left">
                                        <h5>Name :</h5>
                                    </div>
                                    <div class="pull-right">
                                        <h4>{{ $user->client_name }}</h4>
                                    </div>
                                </div>

                                <div class="inbox-item">
                                    <div class="pull-left">
                                        <h5>Email :</h5>
                                    </div>
                                    <div class="pull-right">
                                        <h4>{{ $user->email }}</h4>
                                    </div>
                                </div>

                                <div class="inbox-item">
                                    <div class="pull-left">
                                        <h5>Username :</h5>
                                    </div>
                                    <div class="pull-right">
                                        <h4>{{ $user->username }}</h4>
                                    </div>
                                </div>

                                <div class="inbox-item">
                                    <div class="pull-left">
                                        <h5>Server Password :</h5>
                                    </div>
                                    <div class="pull-right">
                                        <h4>{{ $user->server_password }}</h4>
                                    </div>
                                </div>

                                <div class="inbox-item">
                                    <div class="pull-left">
                                        <h5>Phone :</h5>
                                    </div>
                                    <div class="pull-right">
                                        <h4>{{ $user->phone }}</h4>
                                    </div>
                                </div>

                                <div class="inbox-item">
                                    <div class="pull-left">
                                        <h5>Expire Date :</h5>
                                    </div>
                                    <div class="pull-right">
                                        <h4 class="text-warning">
                                            {{ date('d-M-Y h:i A', strtotime($user->expiration)) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end col -->

                    <div class="col-xl-8">
                        <div class="card-box">
                            <h4 class="header-title mt-0 m-b-30">Latest Invoices</h4>

                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Month</th>
                                            {{-- <th>Bandwidth</th> --}}
                                            <th>Price</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php($i = 0)
                                        @foreach ($invoices as $invoice)
                                            @php($i = $i + 1)
                                            <tr class="{{ $invoice->due > 0 ? 'text-danger' : 'text-success' }}">
                                                <td>{{ $i }}</td>
                                                <td>{{ date('M', mktime(0, 0, 0, $invoice->bill_month, 1)) }} -
                                                    {{ $invoice->bill_year }}</td>
                                                {{-- <td>{{ $invoice->bandwidth }}</td> --}}
                                                <td>{{ $invoice->total }}</td>
                                                <td>{{ $invoice->paid_amount }}</td>
                                                <td>{{ $invoice->due }}</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div><!-- end col -->

                </div>




            </div><!-- end col -->

            <!-- Add traffic modal -->
            <div class="modal fade text-left" id="trafficView" tabindex="-1" role="dialog"
                aria-labelledby="myModalLabel33" aria-hidden="true">
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



        </div>
    </div>
@endsection

@section('custom_js')
    <script>
        var interVal;
        var chart;

        function requestDatta(id) {
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "{{ route('pppoe.traffic_for_client') }}",
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


        $(document).ready(function() {
            $(document).on("click", "#chartShow1", function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var c_name = $(this).data('name');
                $('#interface1').val(id);
                Highcharts.setOptions({
                    global: {
                        useUTC: false
                    }
                });


                chart = new Highcharts.Chart({
                    chart: {
                        renderTo: 'container1',
                        animation: Highcharts.svg,
                        type: 'area', //line,//area
                        events: {
                            load: function() {
                                interVal = setInterval(function() {
                                    requestDatta(document.getElementById("interface1")
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
                            text: 'Traffic1',
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
            $("#trafficView1").click(function() {
                clearInterval(interVal);
            })

        })
    </script>
@endsection
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
@section('required_css')
    <!--Morris Chart CSS -->
    <link href='{{ asset('assets/plugins/morris/morris.css') }}' rel="stylesheet" type="text/css" />
@endsection

@section('required_js')
    <script src="{{ asset('assets/highchart/js/highcharts.js') }}"></script>
    <script src="{{ asset('assets/highchart/js/themes/grid.js') }}"></script>
@endsection
<script>
    // jQuery code to click the button three times with a 1-second interval after the document loads, only for screen size up to 700px
    $(document).ready(function() {
        if ($(window).width() <= 700) {
            // First click
            $('#chartShow1').click();

            // Second click after 1 second
            setTimeout(function() {
                $('#chartShow1').click();

                // Third click after 1 second from the second click
                setTimeout(function() {
                    $('#chartShow1').click();
                }, 1000);
            }, 1000);
        }
    });
</script>
