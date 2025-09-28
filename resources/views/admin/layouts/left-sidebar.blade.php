<?php $user = Auth::user(); ?>
<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
    <div class="sidebar-inner slimscrollleft">
        <input 
    type="search" 
    name="search_menu" 
    id="search_menu" 
    class="form-control sticky-top" 
    placeholder="Search Menu Here"
    autocomplete="off"
    aria-label="Search Menu"
/>

<style>
    #sidebar-menu ul li {
        transition: all 0.3s ease-in-out;
    }
</style>
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <ul>
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="waves-effect"><i class="mdi mdi-view-dashboard"></i><span>Dashboard</span></a>
                </li>

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny([
                        'client_list','client_due client list','client_active client list'
                        ,'client_inactive client list','client_old client list'
                    ])
                )
                <li class="has_sub">
                    <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/clients*') ? 'active subdrop' : '' }}"><i class="mdi mdi-account-circle"></i><span>Clients</span>
                        <span class="menu-arrow"></span></a>
                    <ul class="list-unstyled">
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_list'))
                        <li><a href="{{ route('client.index') }}" class="">All Clients</a></li>
                        @endif
{{--                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_active client list'))--}}
{{--                        <li><a href="{{ route('client.uptime') }}">Client Status</a></li>--}}
{{--                        @endif--}}
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_due client list'))
                        <li><a href="{{ route('client.due') }}" class="">Due Clients</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_active client list'))
                        <li><a href="{{ route('active.client') }}">Active Clients</a></li>
                        @endif
                       
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_inactive client list'))
                        <li><a href="{{ route('inactive.client') }}">Inactive Clients</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('client_old client list'))
                        <li><a href="{{ route('old.client') }}">Old Clients</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin'))
                        <li><a href="{{ route('expire.bulk.clients') }}">Bulk Expiry Update</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin') || $user->can('client_old client list'))
                        <li><a href="{{ route('deleted.client.index') }}">Deleted Clients</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny(['invoice_add','invoice_list','invoice_due invoices','receipt_list'])
                )
                <li class="has_sub">
                    <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/invoice/*') ? 'active subdrop' : '' }}"><i class="mdi mdi-file-document"></i><span>Invoices</span>
                        <span class="menu-arrow"></span></a>
                    <ul class="list-unstyled">
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('invoice_add'))
                        <li><a href="{{ route('invoice.add') }}">Create Invoice</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('invoice_list'))
                        <li><a href="{{ route('invoice.index') }}">Invoices List</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('invoice_due invoices'))
                        <li><a href="{{ route('invoice.due') }}">Due Invoices List</a></li>
                        @endif
                        {{--@if($roleId == 1 or $roleId == 2 or $roleId == 4)--}}
                            {{--<li><a href="{{ route('invoice.seller') }}">Reseller Invoices List</a></li>--}}
                        {{--@endif--}}
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('receipt_list'))
                        <li><a href="{{ route('receipt.index') }}">Receipt List</a></li>
                        @endif

                        @if($user->hasRole('Super-Admin'))
                        <li><a href="{{ route('invoice.trash') }}">Trashed Invoice</a></li>
                        @endif

                    </ul>
                </li>
                @endif

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny([
                        'reseller_list','reseller_active client list','reseller_inactive client list',
                        'reseller_old client list','reseller_plan list','reseller_invoice list','reseller_receipt list'
                    ])
                )
                <li class="has_sub">
                    <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/reseller/*') ? 'active subdrop' : '' }}"><i class="mdi mdi-account-network"></i><span>Reseller</span>
                        <span class="menu-arrow"></span></a>
                    <ul class="list-unstyled">
                        @can('reseller_list')
                            <li><a href="{{ route('reseller.index') }}">All Reseller</a></li>
                        @endcan
                        @can('reseller_active client list')
                            <li><a href="{{ route('client.reseller') }}">Active Clients</a></li>
                        @endcan
                        @can('reseller_inactive client list')
                            <li><a href="{{ route('client.reseller.inactive') }}">Inactive Clients</a></li>
                        @endcan
                        @can('reseller_old client list')
                            <li><a href="{{ route('client.reseller.old') }}">Old Clients</a></li>
                        @endcan
                        @can('reseller_plan list')
                            <li><a href="{{ route('reseller.plan.index') }}">Reseller Plan</a></li>
                        @endcan

                        @if($user->hasAnyRole('Super-Admin'))
                        <li><a href="{{ route('reseller.invoice.list') }}">Reseller Invoices</a></li>
                        @endif

                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('reseller_invoice list'))
                        <li><a href="{{ route('reseller.client.invoice') }}">Balance Deduction Report</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('reseller_receipt list'))
                        <li><a href="{{ route('receipt.seller') }}">Recharge Report</a></li>
                        @endif
                        @role('Reseller')
                            <li><a href="{{ route('reseller.pay') }}">Recharge Balance</a></li>
                        @endrole
                    </ul>
                </li>
                @endif

                @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('account_list'))
                <li>
                    <a href="{{ route('account.index') }}" class="waves-effect {{ request()->is('adminisp/account/*') ? 'active' : '' }}"><i class="mdi mdi-bank"></i><span>Final Accounts</span></a>
                </li>
                @endif

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny(['report_transaction','report_income','report_expense','report_pppoe connected client', 'report_client status '])
                )
                <li class="has_sub">
                    <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/reports/*') ? 'active subdrop' : '' }}"><i
                                class="mdi mdi-clipboard-text"></i><span>Reports</span> <span class="menu-arrow"></span></a>
                    <ul class="list-unstyled">
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('report_transaction'))
                        <li><a href="{{ route('transaction.index') }}">Transactions</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('report_income'))
                        <li><a href="{{ route('report.income') }}">Income Reports</a></li>
                        @endif

                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('report_income'))
                        <li><a href="{{ route('report.onlinePay') }}">Online Payment</a></li>
                        @endif

                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('report_expense'))
                        <li><a href="{{ route('report.expanse') }}">Expense Reports</a></li>
                        @endif
                        @role('Super-Admin')
                        <li><a href="{{ route('report.account.summary') }}">Accounts Summary</a></li>
                        @endrole

                        @if(setting('using_mikrotik') && ($user->hasAnyRole('Super-Admin','Reseller') || $user->can('report_pppoe connected client')))
                        <li><a href="{{ route('pppoe.client.connected') }}">Connected PPPOE Clients</a></li>
                        @endif
                            {{--<li><a href="{{ route('hotspot.client.connected') }}">Connected Hotspot Clients</a></li>--}}
                            {{--<li><a href="{{ route('ip.client.connected') }}">Connected IP Clients</a></li>--}}

                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('report_btrc client list'))
                        <li><a href="{{ route('client.btrc') }}">Client List Of BTRC</a></li>
                        @endif

                        @if( $user->hasAnyRole('Super-Admin') || $user->can('report_btrc client list'))
                        <li><a href="{{ route('client.status') }}">Client Status Report</a></li>
                        @endif

                        @if( $user->hasAnyRole('Reseller'))
                        <li><a href="{{ route('report.reseller.profit') }}">Profit Reports</a></li>
                        @endif

                        @role('Super-Admin')
                            <li>
                                <a href="{{ route('activity.log')}}" target="_blank"><span>Activity Log</span></a>
                            </li>
                        @endrole

                    </ul>
                </li>
                @endif

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny(['plan_list','bandwidth_list','pool_list','server_list'])
                )
                <li class="has_sub">
                    <a href="javascript:void(0);" class="waves-effect {{ (request()->is('adminisp/services/*') || request()->is('adminisp/networks/*')) ? 'active subdrop' : '' }}"><i
                                class="mdi mdi-access-point-network"></i><span>Mikrotik</span> <span
                                class="menu-arrow"></span></a>
                    <ul class="list-unstyled">

                        @role('Reseller')
                            <li><a href="{{ route('reseller.plan.index') }}">Plans</a></li>
                        @endrole
                        @can('plan_list')
                            <li><a href="{{ route('plan.index') }}">Plans</a></li>
                        @endcan
                        @can('bandwidth_list')
                        <li><a href="{{ route('bandwidth.index') }}">Bandwidths</a></li>
                        @endcan
                        @can('pool_list')
                        <li><a href="{{ route('pool.index') }}">Pools</a></li>
                        @endcan
                        @can('server_list')
                        <li><a href="{{ route('server.index') }}">Servers</a></li>
                        @endcan
                    </ul>
                </li>
                @endif

                {{--@if($roleId == 1)--}}
                    {{--<li class="has_sub">--}}
                        {{--<a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/networks/*') ? 'active subdrop' : '' }}"><i class="mdi mdi-server-network"></i><span>Networks</span>--}}
                            {{--<span class="menu-arrow"></span></a>--}}
                        {{--<ul class="list-unstyled">--}}
                            {{--<li><a href="{{ route('server.index') }}">Servers</a></li>--}}
                            {{--<li><a href="{{ route('pool.index') }}">Pools</a></li>--}}
                        {{--</ul>--}}
                    {{--</li>--}}
                {{--@endif--}}
                {{--@if($roleId == 1 or $roleId == 2 or $roleId == 4)--}}
                    {{--<li>--}}
                        {{--<a href="{{ route('investor.index') }}" class="waves-effect {{ request()->is('adminisp/investor/*') ? 'active' : '' }}"><i--}}
                                    {{--class="mdi mdi-account"></i><span>Investors</span></a>--}}
                    {{--</li>--}}

                    {{--<li>--}}
                        {{--<a href="{{ route('loan.payer.index') }}" class="waves-effect {{ request()->is('adminisp/loan-payer/*') ? 'active' : '' }}"><i--}}
                                    {{--class="mdi mdi-account-convert"></i><span>Loan Payers</span></a>--}}
                    {{--</li>--}}
                {{--@endif--}}



                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny(['ticket_add','ticket_pending list','ticket_solved list'])
                )
                <li class="has_sub">
                    <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/complain/*') ? 'active subdrop' : '' }}"><i class="mdi mdi-svg"></i><span>Support Ticket</span>
                        <span class="menu-arrow"></span></a>
                    <ul class="list-unstyled">
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('ticket_add'))
                        <li><a href="{{ route('complain.create') }}">New Ticket</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('ticket_pending list'))
                        <li><a href="{{ route('complain.pending') }}">Pending Tickets</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('ticket_solved list'))
                        <li><a href="{{ route('complain.solved') }}">Solved Tickets</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny(['income_list','income_category list'])
                )
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/income-*') ? 'active subdrop' : '' }}">
                            <i class="mdi mdi-briefcase-check"></i><span>Income</span>
                            <span class="menu-arrow"></span></a>
                        <ul class="list-unstyled">
                            @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('income_list'))
                                <li><a href="{{ route('income.list') }}">Incomes</a></li>
                            @endif
                            @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('income_category list'))
                                <li><a href="{{ route('income.cat.list') }}">Category List</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->canAny(['expense_list','expense_category list'])
                )
                <li class="has_sub">
                    <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/expense-*') ? 'active subdrop' : '' }}"><i class="mdi mdi-view-list"></i><span>Expense</span>
                        <span class="menu-arrow"></span></a>
                    <ul class="list-unstyled">
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('expense_list'))
                        <li><a href="{{ route('expanse.list') }}">Expanses</a></li>
                        @endif
                        @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('expense_category list'))
                        <li><a href="{{ route('expanse.cat.list') }}">Category List</a></li>
                        @endif
                        {{--@if($roleId != 3)--}}
                        {{--<li><a href="{{ route('invest.list') }}">Invests</a></li>--}}
                        {{--<li><a href="{{ route('loan.list') }}">Loans</a></li>--}}
                        {{--<li><a href="{{ route('loan.pay.list') }}">Loan Paid</a></li>--}}
                        {{--@endif--}}
                    </ul>
                </li>
                @endif

                {{--<li class="has_sub">--}}
                    {{--<a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/inventory/*') ? 'active subdrop' : '' }}"><i--}}
                                {{--class="mdi mdi-webpack"></i><span>Inventory</span> <span class="menu-arrow"></span></a>--}}
                    {{--<ul class="list-unstyled">--}}
                        {{--<li><a href="{{ route('inventory.maintain') }}">Maintain</a></li>--}}
                        {{--<li><a href="{{ route('purchases') }}">Purchases</a></li>--}}
                        {{--<li><a href="{{ route('product.index') }}">Products</a></li>--}}
                        {{--<li><a href="{{ route('inventory.items.detail') }}">Items Qty Details</a></li>--}}

                    {{--</ul>--}}
                {{--</li>--}}

                @if(
                    $user->hasAnyRole('Super-Admin','Reseller') ||
                    $user->can(['distribution_list'])
                )
                    <li class="has_sub">
                        <a href="javascript:void(0);" class="waves-effect {{ request()->is('adminisp/setting/*') ? 'active subdrop' : '' }}"><i class="mdi mdi-settings"></i><span>Settings</span>
                            <span class="menu-arrow"></span></a>
                        <ul class="list-unstyled">

                            <li><a href="{{ route('config.index') }}">General</a></li>

                            <li><a href="{{ route('config.sms.content') }}">SMS Setup</a></li>
                            @if($user->hasAnyRole('Super-Admin','Reseller') || $user->can('distribution_list'))
                            <li><a href="{{ route('distribution.index') }}">Area/Box</a></li>
                            @endif
                            {{--<li><a href="{{ route('branch.index') }}">Branches</a></li>--}}
                            @role('Super-Admin')
                                <li><a href="{{ route('config.users') }}">User List</a></li>
                                <li><a href="{{ route('roles') }}">Role/Permission</a></li>
                                <li><a href="{{ route('config.payment.api') }}">Payment Gateway</a></li>
                            @endrole

                        </ul>
                    </li>
                @endif


                {{--<li><i class="mdi mdi-information"></i> <a href="#">Guideline</a></li>--}}

                {{--<li>--}}
                    {{--<a href="http://deelko.com/manual/isp/admin.pdf" target="_blank" class="waves-effect {{ request()->is('adminisp/guideline/*') ? 'active' : '' }}"><i class="mdi mdi-information"></i><span>Guideline</span></a>--}}
                {{--</li>--}}

            </ul>
            <div class="clearfix"></div>
        </div>
        <!-- Sidebar -->
        <div class="clearfix"></div>

    </div>

</div>
<!-- Left Sidebar End -->

 <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get the search input and the sidebar menu items
        const searchInput = document.getElementById('search_menu');
        const menuItems = document.querySelectorAll('#sidebar-menu ul li');

        // Add an event listener for the search input
        searchInput.addEventListener('input', function () {
            const searchTerm = searchInput.value.toLowerCase();

            // Loop through all menu items and hide those that don't match the search term
            menuItems.forEach(function (item) {
                const menuText = item.textContent.toLowerCase();
                if (menuText.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script> 
