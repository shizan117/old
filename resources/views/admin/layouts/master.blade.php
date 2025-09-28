<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Deelko">
    <meta name="csrf-token" id="_token" content="{{ csrf_token() }}">
    <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">

    <link rel="shortcut icon" href={{ asset("assets/images/favicon.ico") }}>

    <title>@yield('title') - {{ $setting['companyName'] }} {{ config('app.name', 'ISP Billing') }}</title>

    @yield('required_css')

    <link href='{{ asset("assets/plugins/select2/css/select2.min.css") }}' rel="stylesheet" type="text/css"/>

    <!-- App css -->
    <link href='{{ asset("assets/css/bootstrap.min.css") }}' rel="stylesheet" type="text/css"/>
    <link href='{{ asset("assets/css/icons.css") }}' rel="stylesheet" type="text/css"/>
    <link href='{{ asset("assets/css/style.css") }}' rel="stylesheet" type="text/css"/>
    <link href='{{ asset("assets/css/print.css") }}' rel="stylesheet" type="text/css" media="print"/>

    <script src='{{ asset("assets/js/modernizr.min.js") }}'></script>
    @yield('custom_css')
</head>


<body class="fixed-left">
<?php
$user_id = Auth::guard('admin')->user()->id;
$user_name = Auth::guard('admin')->user()->name;
?>
<!-- Begin page -->
<div id="wrapper">

    <!-- Top Header -->
@include('admin.layouts.header')
<!-- /.Top Header -->


    <!-- Left side column. contains the logo and sidebar -->
@include('admin.layouts.left-sidebar')
<!-- /.Left side column. contains the logo and sidebar -->


    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="content-page">
        <!-- Start content -->
        <div class="content">
            <section id="content-message" style="clear: both;">
                <div>
                    <div class="col-md-12">
                        <div class="script-message"></div>
                        @if(Session::has('message'))
                            <div class="alert {{ Session::get('m-class') }} alert-dismissible"
                                 style="margin-top: 10px;">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <!--<h4><i class="icon fa fa-check"></i> Alert!</h4>-->
                                {!! Session::get('message') !!}
                            </div>
                        @endif

                    </div>
                </div>
            </section>
            <div class="container-fluid">
                @yield('content')
                <div class="hidden" id="full_page_loading"></div>
            </div> <!-- container -->

        </div> <!-- content -->

        <!-- Footer -->
    @include('admin.layouts.footer')
    <!-- /.Footer -->

    </div>


    <!-- ============================================================== -->
    <!-- End Right content here -->
    <!-- ============================================================== -->

    <!-- Right Sidebar -->
{{--			@include('admin.layouts.right-sidebar')--}}
<!-- End Right Sidebar -->


</div>
<!-- END wrapper -->


<!-- jQuery  -->
<script src="{{ asset('assets/js/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/detect.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/fastclick.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery.blockUI.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/waves.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery.nicescroll.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery.slimscroll.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery.scrollTo.min.js') }}" type="text/javascript"></script>

@yield('required_js')

<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}" type="text/javascript"></script>

<!-- App js -->
<script src="{{ asset('assets/js/jquery.core.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/jquery.app.js') }}" type="text/javascript"></script>

<script>
    $(window).scroll(function(){
        if ($(this).scrollTop() > 150) {
            $('#sticky-button-wrapper').addClass('stickyy');
        } else {
            $('#sticky-button-wrapper').removeClass('stickyy');
        }
    });
</script>

@yield('custom_js')

</body>
</html>
