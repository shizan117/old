<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
    <div class="sidebar-inner slimscrollleft">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <ul>
                {{--<li class="text-muted menu-title">Navigation</li>--}}

                <li>
                    <a href="{{ route('home') }}" class="waves-effect"><i class="mdi mdi-view-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('client.invoice') }}" class="waves-effect"><i class="mdi mdi-file-document"></i>
                        <span>Invoices</span> </a>
                </li>
                
                <li>
                    <a href="{{ route('client.pay') }}" class="waves-effect"><i class="mdi mdi-file"></i> <span>Pay Due</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('client.receipt') }}" class="waves-effect"><i class="mdi mdi-file"></i> <span>Receipt</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('client.complain.index') }}" class="waves-effect"><i class="mdi mdi-svg"></i> <span>Complain</span>
                    </a>
                </li>

            </ul>
            <div class="clearfix"></div>
        </div>
        <!-- Sidebar -->
        <div class="clearfix"></div>

    </div>

</div>
<!-- Left Sidebar End -->