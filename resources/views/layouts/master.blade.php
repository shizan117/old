<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Deelko">
        <meta name="csrf-token" id="_token" content="{{ csrf_token() }}">

        <link rel="shortcut icon" href={{ asset("assets/images/favicon.ico") }}>

        <title>@yield('title') - {{ $setting['companyName'] }} {{ config('app.name', 'ISP Billing') }}</title>

        @yield('required_css')

        <!-- App css -->
        <link href={{ asset("assets/css/bootstrap.min.css") }} rel="stylesheet" type="text/css" />
        <link href={{ asset("assets/css/icons.css") }} rel="stylesheet" type="text/css" />
        <link href={{ asset("assets/css/style.css") }} rel="stylesheet" type="text/css" />
        <style>
            .hidden {
                display: none !important;
            }

            #full_page_loading {
                background: url('{{ asset("assets/images/loading.gif") }}') no-repeat scroll center center transparent;
                position: fixed;
                background-size: 120px 100px;
                height: 100%;
                width: 100%;
                z-index: 9999;
                /*opacity: 0.5;*/
                top: 0;
                left: 0
            }
        </style>
    </head>


    <body class="fixed-left">
    <?php
    $user_id    = Auth::user()->id;
    $user_name    = Auth::user()->client_name;
    ?>

        <!-- Begin page -->
        <div id="wrapper">
		
			<!-- Top Header -->	
			@include('layouts.header')
			<!-- /.Top Header -->

            
			<!-- Left side column. contains the logo and sidebar -->  
			@include('layouts.left-sidebar')
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
                                    <div class="alert {{ Session::get('m-class') }} alert-dismissible"  style="margin-top: 10px;">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <!--<h4><i class="icon fa fa-check"></i> Alert!</h4>-->
                                        {{ Session::get('message') }}
                                    </div>
                                @endif

                            </div>
                        </div>
                    </section>
                    <div class="container-fluid">
				
					@yield('content')
                        <div class="hidden" id="full_page_loading"></div>
                    </div>
                    

                </div> <!-- content -->
				
				<!-- Footer -->
				@include('layouts.footer')
				<!-- /.Footer -->                

            </div>


            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->
			
			<!-- Right Sidebar -->
			{{--@include('layouts.right-sidebar')--}}
			<!-- End Right Sidebar -->



        </div>
        <!-- END wrapper -->


        <!-- jQuery  -->
        <script src={{ asset("assets/js/jquery.min.js") }}></script>
        <script src={{ asset("assets/js/popper.min.js") }}></script>
        <script src={{ asset("assets/js/bootstrap.min.js") }}></script>
        <script src={{ asset("assets/js/detect.js") }}></script>
        <script src={{ asset("assets/js/fastclick.js") }}></script>
        <script src={{ asset("assets/js/jquery.blockUI.js") }}></script>
        <script src={{ asset("assets/js/waves.js") }}></script>
        <script src={{ asset("assets/js/jquery.nicescroll.js") }}></script>
        <script src={{ asset("assets/js/jquery.slimscroll.js") }}></script>
        <script src={{ asset("assets/js/jquery.scrollTo.min.js") }}></script>

        @yield('required_js')

        <!-- App js -->
        <script src={{ asset("assets/js/jquery.core.js") }}></script>
        <script src={{ asset("assets/js/jquery.app.js") }}></script>
        @yield('custom_js')

    </body>
</html>