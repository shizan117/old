
<!-- Top Bar Start -->
<div class="topbar">

    <!-- LOGO -->
    <div class="topbar-left">
        @if(file_exists("assets/images/".$setting['logo']))
            <a href="{{ route('home') }}" class="logo"><img src="{{ asset("assets/images/".$setting['logo']) }}" style="max-width: 100%;max-height: 75px;"></a>
        @else
            <a href="{{ route('home') }}" class="logo"><img src="{{ asset('assets/images/default-logo.png') }}"  style="max-width: 100%;"></a>
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
                    <h4 class="page-title">@yield('title')</h4>
                </li>
            </ul>
            <li class="list-inline-item dropdown">
                <a href="#" id="dd_user" class="dropdown-toggle" data-toggle="dropdown" style="color: #fff;"><i class="fa fa-user"></i> {{$user_name}}</a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd_user">
                    <a href="{{ route('client.profile.edit') }}" class="dropdown-item"><i class="mdi mdi-settings"></i> Edit Profile</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
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
