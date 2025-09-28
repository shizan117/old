
<!-- Top Bar Start -->
<div class="topbar">
<style>
    .quick-menu .btn-outline-info {
  border-color: #ffc107 !important;
  color: #fff !important;
}
.curentUserName{
    border: 1px solid #ffc107;
    border-radius: 5px;
    font-size: 12px;
}
div.dataTables_wrapper div.dataTables_filter input{
    border: 1px solid #ffc107 !important;
}
.youTubeHelpIcon{
    margin-right: 10px;
    vertical-align: middle;
    display: inline-flex; 
    align-items: center;
    margin-top: -4px;
}
.youTubeHelpIcon:hover{
    background-color: transparent !important;
}
.youTubeHelpIcon:focus{
    background-color: transparent !important;
}
.youTubeHelpIcon:active{
    background-color: transparent !important;
}
</style>
    <!-- LOGO -->
    <div class="topbar-left">
        @if(file_exists("assets/images/".$setting['logo']))
            <a href="{{ route('admin.dashboard') }}" class="logo"><img src="{{ asset("assets/images/".$setting['logo']) }}" style="max-width: 100%;max-height: 75px;"></a>
        @else
            <a href="{{ route('admin.dashboard') }}" class="logo"><img src="{{ asset('assets/images/default-logo.png') }}"  style="max-width: 100%;"></a>
        @endif
    </div>

    <!-- Button mobile view to collapse sidebar menu -->
    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <!-- Page title -->
            <ul class="nav navbar-nav list-inline navbar-left">
                <li class="list-inline-item">
                    <button class="button-menu-mobile open-left">
                        <i class="mdi mdi-menu text-light"></i>
                    </button>
                </li>
                <li class="list-inline-item">
                    <span class="page-title" style="margin-right: 5px;">@yield('title') 
                    </span>
                    @php
                        $endPoint = strtolower(Request::path());
                        $youTubeLink = DB::table('deelko_supports')->where('url_endpoint', $endPoint)->value('youTube_share_link');
                    @endphp
                    <a href="{{$youTubeLink ?? '#'}}" target="_blank" class="youTubeHelpIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#FF0000">
                            <path d="M19.6 3.2H4.4C2.4 3.2 1 4.6 1 6.6v10.8c0 2 1.4 3.4 3.4 3.4h15.2c2 0 3.4-1.4 3.4-3.4V6.6c0-2-1.4-3.4-3.4-3.4zm-8.4 12.5V8.3l6.5 3.7-6.5 3.7z"/>
                        </svg>                                              
                    </a>
                    @if(\Request::route()->getName() != 'admin.dashboard')
                    <span class="btn btn-sm btn-outline-warning d-none d-md-inline-block" onclick="history.back()" style="margin-top: -8px;">
                        <i class="fa fa-arrow-left"></i> Go Back
                    </span>
                    @endif
                </li>
            </ul>
            <ul class="quick-menu">
                <li><a class="btn btn-outline-info btn-sm" href="{{route('client.index')}}"><i class="fa fa-users"></i> All Client</a></li>
                <li><a class="btn btn-outline-info btn-sm" href="{{route('client.add')}}"><i class="fa fa-user-circle"></i> New Client</a></li>
                <li><a class="btn btn-outline-info btn-sm" href="{{route('invoice.add')}}"><i class="fa fa-money"></i> Create Invoice</a></li>
                <li><a class="btn btn-outline-info btn-sm" href="{{route('expanse.add')}}"><i class="fa fa-clipboard"></i> New Expense</a></li>
                {{--<li>--}}
                    {{--<div class="input-group">--}}
                        {{--<input type="text" class="form-control form-control-sm" placeholder="Search client">--}}
                        {{--<div class="input-group-append">--}}
                            {{--<button class="btn btn-sm btn-outline-info" type="button">Button</button>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</li>--}}
            </ul>
            <li class="list-inline-item dropdown py-1 px-2 curentUserName">
                <a href="#" id="dd_user" class="dropdown-toggle" data-toggle="dropdown" style="color: #fff;"><i class="fa fa-user"></i> {{$user_name}}</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd_user">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="mdi mdi-settings"></i> Edit Profile</a>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                    <a href="javascript:" class="dropdown-item" onclick="$('#logout-form').submit()">
                        <i class="mdi mdi-power"></i> Logout
                    </a>
                </div>
            </li>
        </div><!-- end container -->
    </div><!-- end navbar -->
</div>
<!-- Top Bar End -->
