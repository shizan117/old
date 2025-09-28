<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// // Log route

use App\Http\Controllers\DeelkoSupportController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

// CRON JOB SCHEDULER
Route::get('/client/payment/remainder', function () {
    return Artisan::call('mAutoServiceOff:internetService');
});

Route::get('/client/autoServiceOff', function () {
    return Artisan::call('mInvoiceCreate:invoice');
});

// Get SMS Balance For all abir vai er
Route::get('/get-sms-balance', function () {
    $sms_client_id = request('client'); // Get client ID from the request
    $url = "https://smpp.revesms.com/sms/smsConfiguration/smsClientBalance.jsp?client={$sms_client_id}";

    $response = Http::get($url); // Make the request from the server
    return response($response->body())->header('Content-Type', 'application/json');
});



//Route::get('/get-sms-balance', function () {
//    $sms_client_id = request('client');
//    Log::info('Received client ID:', ['client' => $sms_client_id]); // Log client ID
//    $url = "https://smpp.revesms.com/sms/smsConfiguration/smsClientBalance.jsp?client={$sms_client_id}";
//
//    try {
//        $response = Http::get($url);
//        Log::info('SMS API Response:', ['url' => $url, 'response' => $response->body()]);
//
//        return response()->json([
//            'status' => 'success',
//            'data' => json_decode($response->body(), true)
//        ]);
//    } catch (\Exception $e) {
//        Log::error('SMS API Error:', ['error' => $e->getMessage()]);
//        return response()->json([
//            'status' => 'error',
//            'message' => 'Exception occurred',
//            'error' => $e->getMessage()
//        ]);
//    }
//});



Route::group(['middleware' => 'bkash_payment'], function () {
    // Payment Routes for bKash
    Route::post('bkash/get-token', 'BkashController@getToken')->name('bkash-get-token');
    Route::post('bkash/create-payment', 'BkashController@createPayment')->name('bkash-create-payment');
    Route::post('bkash/execute-payment', 'BkashController@executePayment')->name('bkash-execute-payment');
    Route::get('bkash/query-payment', 'BkashController@queryPayment')->name('bkash-query-payment');
    Route::post('bkash/success', 'BkashController@bkashSuccess')->name('bkash-success');
    Route::get('pay-session', 'BkashController@setSession')->name('set.session');
});

Route::post('query-bkash', 'BkashController@queryPayment')->name('bkash.query.payment');
Route::post('search-trx-bkash', 'BkashController@searchTrans')->name('bkash.search.trans');
Auth::routes();


// Bkash WebHook payment intigration start here
Route::post('/bkash/webhook', 'BkashWebhookController@handleWebhook');
Route::post('/bkash/sandbox', 'BkashWebhookController@handleSandbox');
// Bkash WebHook payment intigration End here




//Route::group(['namespace' => 'Auth'], function () {
//    Route::get('logout', 'LoginController@logout')->name('logout');
//});


//=======================CLIENT PART============================//

Route::get('/complain/store/web', 'Clients\ComplainController@storeWeb');
Route::get('invoice/share/{id}', 'Clients\ClientDashboardController@invoiceShow')->name('invoice.share')
    ->middleware('signed');
Route::group(['namespace' => 'Clients', 'middleware' => 'auth'], function () {
    Route::get('/', 'ClientDashboardController@index')->name('home');
    Route::get('invoices', 'ClientDashboardController@invoice')->name('client.invoice');
    Route::get('pay', 'ClientDashboardController@pay')->name('client.pay');
    Route::get('receipt', 'ClientDashboardController@receipt')->name('client.receipt');
    Route::get('receipt/{id}', 'ClientDashboardController@receiptShow')->name('client.receipt.show');


    Route::get('profile', 'ClientDashboardController@profileEdit')->name('client.profile.edit');
    Route::post('profile', 'ClientDashboardController@profileUpdate')->name('client.profile.edit.post');

    Route::get('complain', 'ComplainController@index')->name('client.complain.index');
    Route::get('complain/create', 'ComplainController@create')->name('client.complain.create');
    Route::post('complain/store', 'ComplainController@store')->name('client.complain.store');
    Route::get('complain/edit/{id}', 'ComplainController@edit')->name('client.complain.edit');
    Route::post('complain/update/{id}', 'ComplainController@update')->name('client.complain.update');
    Route::post('complain/delete/{id}', 'ComplainController@destroy')->name('client.complain.delete');
});

Route::get('get-pppoe-traffic-for-client', 'Admin\AjaxController@pppoeRealTimeTraffic')->name('pppoe.traffic_for_client');
//=======================ADMIN PART============================//

Route::group(['prefix' => 'adminisp'], function () {
    Route::get('/', function () {
        return redirect()->route('admin.login');
    });

    // Deelko Support Youtube link controll start here
    Route::get('/help', [DeelkoSupportController::class, 'index'])
        ->name('deelko.support')
        ->middleware('auth.admin');

    Route::post('/help/store', [DeelkoSupportController::class, 'store'])
        ->name('deelko.support.store')
        ->middleware('auth.admin');

    Route::put('/help/update', [DeelkoSupportController::class, 'update'])
        ->name('deelko.support.update')
        ->middleware('auth.admin');

    Route::delete('/help/distroy', [DeelkoSupportController::class, 'distroy'])
        ->name('deelko.support.distroy')
        ->middleware('auth.admin');
  // Deelko Support Youtube link controll end here


    Route::group(['namespace' => 'AdminAuth'], function () {
        // Admin Auth Route
        Route::get('login', 'AdminLoginController@showLoginForm')->name('admin.login');
        Route::post('login', 'AdminLoginController@login')->name('admin.login.post');
        Route::post('logout', 'AdminLoginController@logout')->name('admin.logout');

        Route::post('password/email', 'ForgotAdminPasswordController@sendResetLinkEmail')->name('admin.password.email');
        Route::get('password/reset', 'ForgotAdminPasswordController@showLinkRequestForm')->name('admin.password.reset');
        Route::post('password/reset', 'ResetAdminPasswordController@reset')->name('admin.password.reset.post');
        Route::get('password/reset/{token}', 'ResetAdminPasswordController@showResetForm')->name('admin.password.reset.token');
    });

    Route::group(['namespace' => 'Admin', 'middleware' => 'auth.admin'], function () {
        Route::get('dashboard', 'DashboardController@index')->name('admin.dashboard');

        Route::group(['middleware' => ['role:Super-Admin']], function () {
            // Software Config Route
            Route::get('setting/users', 'UserController@index')->name('config.users');
            Route::get('setting/add-user', 'UserController@create')->name('config.user.add');
            Route::post('setting/add-user', 'UserController@store')->name('config.user.add.post');
            Route::get('setting/edit-user/{id}', 'UserController@edit')->name('config.user.edit');
            Route::post('setting/edit-user/{id}', 'UserController@update')->name('config.user.edit.post');
            Route::get('reseller/{id}/sec-lg', 'UserController@secretLogin')->name('user.secret.login');

            //Role Permission
            Route::get('roles', 'RoleController@index')->name('roles');
            Route::get('role/create', 'RoleController@create')->name('role.create');
            Route::post('role/store', 'RoleController@store')->name('role.store');
            Route::get('role/edit/{id}', 'RoleController@edit')->name('role.edit');
            Route::post('role/update/{id}', 'RoleController@update')->name('role.update');

            //BRANCH
            Route::get('branch', 'BranchController@index')->name('branch.index');
            Route::get('branch/add', 'BranchController@create')->name('branch.add');
            Route::post('branch/add', 'BranchController@store')->name('branch.add.post');
            Route::get('branch/edit/{id}', 'BranchController@edit')->name('branch.edit');
            Route::post('branch/edit/{id}', 'BranchController@update')->name('branch.edit.post');


            //ACTIVITY LOG
            Route::get('/activity-log', 'ActivityController@index')->name('activity.log');
        });

        // Server Route
        Route::get('networks/server', 'ServerController@index')->name('server.index')->can('server_list');
        Route::get('networks/server/add', 'ServerController@create')->name('server.add')->can('server_add');
        Route::post('networks/server/add', 'ServerController@store')->name('server.add.post');
        Route::get('networks/server/edit/{id}', 'ServerController@edit')->name('server.edit')->can('server_edit');
        Route::post('networks/server/edit/{id}', 'ServerController@update')->name('server.edit.post');
        Route::get('networks/server-delete/{id}', 'ServerController@destroy_server')->name('server.delete')->can('server_delete');
        Route::post('networks/server/check/connection', 'ServerController@checkConnection')->name('server.check.connection')->can('server_check connection');

        // Pool Route
        Route::get('networks/pool', 'PoolController@index')->name('pool.index')->can('pool_list');
        Route::get('networks/pool/add', 'PoolController@create')->name('pool.add')->can('pool_add');
        Route::post('networks/pool/add', 'PoolController@store')->name('pool.add.post');
        Route::get('networks/pool/edit/{id}', 'PoolController@edit')->name('pool.edit')->can('pool_edit');
        Route::post('networks/pool/edit/{id}', 'PoolController@update')->name('pool.edit.post');
        Route::get('services/pool-delete/{id}', 'PoolController@destroy_pool')->name('pool.delete')->can('pool_delete');


        Route::get('services/bandwidth', 'BandwidthController@index')->name('bandwidth.index')->can('bandwidth_list');
        Route::get('services/bandwidth-add', 'BandwidthController@create')->name('bandwidth.add')->can('bandwidth_add');
        Route::post('services/bandwidth-add', 'BandwidthController@store')->name('bandwidth.add.post');
        Route::get('services/bandwidth-edit/{id}', 'BandwidthController@edit')->name('bandwidth.edit')->can('bandwidth_edit');
        Route::post('services/bandwidth-edit/{id}', 'BandwidthController@update')->name('bandwidth.edit.post');

        Route::get('services/plan', 'PlanController@index')->name('plan.index')->can('plan_list');
        Route::get('services/plan-add', 'PlanController@create')->name('plan.add')->can('plan_add');
        Route::post('services/plan-add', 'PlanController@store')->name('plan.add.post');
        Route::get('services/plan-edit/{id}', 'PlanController@edit')->name('plan.edit')->can('plan_edit');
        Route::post('services/plan-edit/{id}', 'PlanController@update')->name('plan.edit.post');
        Route::get('services/plan-delete/{id}', 'PlanController@destroy_plan')->name('plan.delete')->can('plan_delete');

        Route::get('services/plan-pppoe', 'PlanController@pppoeList')->name('plan.pppoe')->can('plan_list');
        Route::get('services/plan-hotspot', 'PlanController@hotspotList')->name('plan.hotspot')->can('plan_list');
        Route::get('services/plan-ip', 'PlanController@ipList')->name('plan.ip')->can('plan_list');

        //      RESELLER PLAN ROUTE
        Route::get('services/reseller/plan', 'PlanController@resellerPlanList')->name('reseller.plan.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|reseller_plan list');
        Route::get('services/reseller/plan-add', 'PlanController@resellerPlanCreate')->name('reseller.plan.add')->can('reseller_plan add');
        Route::post('services/reseller/plan-add', 'PlanController@resellerPlanStore')->name('reseller.plan.add.post');
        Route::get('services/reseller/plan-edit/{id}', 'PlanController@resellerPlanEdit')->name('reseller.plan.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|reseller_plan edit');
        Route::post('services/reseller/plan-edit/{id}', 'PlanController@resellerPlanUpdate')->name('reseller.plan.edit.post');
        Route::get('services/reseller/plan-delete/{id}', 'PlanController@resellerPlanDelete')->name('reseller.plan.delete')
            ->middleware('role:Super-Admin');

        // Reseller invoice controll here
        Route::get('reseller/all-invoices', 'ResellerController@getAllResellerInvoice')->name('reseller.invoice.list')->middleware('role:Super-Admin');

        Route::get('reseller', 'ResellerController@index')->name('reseller.index')->can('reseller_list');
        Route::put('deactivateReseller/{id}', 'ResellerController@deactive_reseller')->name('reseller.deactive')->can('reseller_list');
        Route::put('reseller/extra_charge/{id}', 'ResellerController@toggleExtraCharge')
            ->name('reseller.extra_charge')
            ->can('reseller_list');
        Route::put('reseller/plan_price/{id}', 'ResellerController@togglePlan_Price')
            ->name('reseller.plan_price')
            ->can('reseller_list');

        Route::get('reseller/add', 'ResellerController@create')->name('reseller.add')->can('reseller_add');
        Route::post('reseller/add', 'ResellerController@store')->name('reseller.add.post');
        Route::get('reseller/edit/{id}', 'ResellerController@edit')->name('reseller.edit')->can('reseller_edit');
        Route::post('reseller/edit/{id}', 'ResellerController@update')->name('reseller.edit.post');
        Route::get('reseller/reseller-view/{id}', 'ResellerController@show')->name('reseller.view')->can('reseller_profile');

        Route::get('reseller/reseller-clients', 'ClientController@resellerClient')->name('client.reseller')->can('reseller_active client list');
        Route::get('reseller/reseller-inactive-clients', 'ClientController@resellerInactiveClient')->name('client.reseller.inactive')->can('reseller_inactive client list');
        Route::get('reseller/reseller-old-clients', 'ClientController@resellerOldClient')->name('client.reseller.old')->can('reseller_old client list');

        Route::get('reseller/{id}/reseller-payment', 'ResellerController@payment')->name('reseller.payment')->can('reseller_recharge balance');
        Route::post('reseller/{id}/reseller-payment', 'ResellerController@paymentStore')->name('reseller.payment.post');
        //            Route::get('reseller/invoice', 'InvoiceController@resellerInvoiceList')->name('invoice.seller');

        Route::get('reseller/client/invoice', 'InvoiceController@resellerClientInvoiceList')->name('reseller.client.invoice')
            ->middleware('role_or_permission:Super-Admin|Reseller|reseller_invoice list');
        Route::get('reseller/receipt', 'MoneyReceiptController@resellerReceiptList')->name('receipt.seller')
            ->middleware('role_or_permission:Super-Admin|Reseller|reseller_receipt list');
        Route::get('reseller/receipt/view/{id}', 'MoneyReceiptController@resellerShow')->name('receipt.seller.show')
            ->middleware('role_or_permission:Super-Admin|Reseller|reseller_receipt view');
        Route::get('reseller/receipt/print', 'MoneyReceiptController@resellerReceiptPrint')->name('reseller.receipt.print')
            ->middleware('role_or_permission:Super-Admin|Reseller|reseller_receipt view');

        Route::get('recharge', 'ResellerController@resellerPay')->name('reseller.pay')->role('Reseller');

        //Clients Route
        //=======================CLIENT============================//
        Route::get('clients', 'ClientController@index')->name('client.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_list');

        // All client Up Time Information
        Route::get('clients-uptime', 'ClientController@getClientUptime')->name('client.uptime');

        Route::get('clients-due', 'ClientController@dueClients')->name('client.due')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_due client list');

        Route::put('clients-due/{id}', 'ClientController@saveDueClients')->name('save.client.due')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_due client list');

        Route::get('clients/active-clients', 'ClientController@activeClient')->name('active.client')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_active client list');
        Route::get('clients/inactive-clients', 'ClientController@inactiveClient')->name('inactive.client')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_inactive client list');
        Route::get('clients/old-clients', 'ClientController@oldClient')->name('old.client')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_old client list');

        // Manage Clients Bulk Expire Date Start
        Route::get('clients/bulk-expireDate', 'ClientController@bulkClientsExpiration')->name('expire.bulk.clients')
            ->middleware('role_or_permission:Super-Admin');
        Route::post('clients/bulk-expireDateUpdate', 'ClientController@bulkClientsExpirationUpdate')->name('expire.bulk.clientsUpdate')
            ->middleware('role_or_permission:Super-Admin');
        // Manage Clients Bulk Expire Date END

        Route::get('clients/deleted-clients', 'ClientController@deletedClients')->name('deleted.client.index')
            ->middleware('role_or_permission:Super-Admin');

        Route::post('clients/deleted-clients', 'ClientController@deletedSingleClient')
            ->name('deleted.client')
            ->middleware('role_or_permission:Super-Admin');

        Route::post('clients/restore-client', 'ClientController@restoreSingleClient')
            ->name('restore.client')
            ->middleware('role_or_permission:Super-Admin');

        Route::delete('clients/delete-all-clients', 'ClientController@deleteAllClients')
            ->name('delete.all.client')
            ->middleware('role_or_permission:Super-Admin');

        Route::post('clients/delete-all-clients', 'ClientController@restoreAllClients')
            ->name('restore.all.client')
            ->middleware('role_or_permission:Super-Admin');



        Route::get('clients/client-view/{id}', 'ClientController@show')->name('client.view')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_profile');
        Route::post('clients/client-view/{id}', 'ClientController@increaseExpDate')->name('client.increase.exp.date')->can('client_increase expire date');
        Route::post('clients/client-views/{id}', 'ClientController@serverProfileUpdate')->name('client.change.plan')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_change plan');
        Route::post('clients/client/transfer', 'ClientController@transferClient')->name('client.transfer')->can('client_transfer client');

        Route::get('clients/client-active', 'ClientController@clientActive')->name('client.active.get');
        Route::post('clients/client-active', 'ClientController@clientActive')->name('client.active')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_activate');
        //        Route::get('clients/client-inactive', 'ClientController@clientInactive')->name('client.inactive.get');
        Route::post('clients/client-inactive', 'ClientController@clientInactive')->name('client.inactive')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_deactivate');
        Route::get('clients/client-add-server', 'ClientController@clientAddServer')->name('client.add.server.get');
        Route::post('clients/client-add-server', 'ClientController@clientAddServer')->name('client.add.server')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_add to server');
        Route::get('clients/client-delete-server', 'ClientController@clientDelServer')->name('client.del.server.get');
        Route::post('clients/client-delete-server', 'ClientController@clientDelServer')->name('client.del.server')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_delete from server');
        Route::post('clients/client-bulk-active', 'ClientController@clientBulkActive')->name('client.active.bulk')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_activate');
        Route::post('clients/client-bulk-inactive', 'ClientController@clientBulkInactive')->name('client.inactive.bulk')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_deactivate');
        Route::post('clients/client-bulk-add-server', 'ClientController@clientBulkAddServer')->name('client.add.server.bulk')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_add to server');
        Route::post('clients/client-bulk-delete-server', 'ClientController@clientBulkDelServer')->name('client.delete.server.bulk')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_delete from server');

       Route::get('clients/pppoe-client-connected', 'ClientController@pppoeClientConnected')
            ->name('pppoe.client.connected')
            ->middleware('role_or_permission:Super-Admin|Reseller|report_pppoe connected client');

        Route::get('clients/pppoe-client-connected-data', 'ClientController@pppoeClientConnectedData')
            ->name('pppoe.client.connected.data')
            ->middleware('role_or_permission:Super-Admin|Reseller|report_pppoe connected client');

            
        Route::post('clients/pppoe-client-connected', 'ClientController@pppoeClientConnectedDel')->name('pppoe.client.connected.del');
        Route::get('clients/pppoe-client-connected-fresh', 'ClientController@pppoeClientConnectedFresh')->name('pppoe.client.connected.fresh');
        Route::get('clients/hotspot-client-connected', 'ClientController@hotspotClientConnected')->name('hotspot.client.connected');
        Route::get('clients/ip-client-connected', 'ClientController@IPClientConnected')->name('ip.client.connected');
        Route::get('clients/due/{id}', 'ClientController@payDue')->name('client.pay.due')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_payment collection');
        Route::post('clients/due/{id}', 'ClientController@payDueStore')->name('client.pay.due.post');


        Route::get('clients/client-add', 'ClientController@create')->name('client.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_add');
        Route::post('clients/client-add', 'ClientController@store')->name('client.add.post');
        Route::get('clients/client-edit/{id}', 'ClientController@edit')->name('client.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_edit');
        Route::post('clients/client-edit/{id}', 'ClientController@update')->name('client.edit.post');
        Route::get('clients/client-delete/{id}', 'ClientController@destroy')->name('client.delete')
            ->middleware('role_or_permission:Super-Admin|Reseller|client_delete');

        Route::get('clients/import-excel-data', 'ClientController@clientAddFromExcel')->name('client.add.excel');
        Route::post('clients/import-excel-data', 'ClientController@clientAddFromExcel')->name('client.add.excel.post');

        Route::get('clients/branch-clients', 'ClientController@branchClient')->name('client.branch');
        Route::get('clients/branch-due', 'ClientController@dueClientsBranch')->name('client.due.branch');
        Route::get('clients/branch-active-clients', 'ClientController@branchActiveClient')->name('client.branch.active');
        Route::get('clients/branch-inactive-clients', 'ClientController@branchInactiveClient')->name('client.branch.inactive');
        Route::get('clients/branch-old-clients', 'ClientController@branchOldClient')->name('client.branch.old');

        // Distribution Area Route
        Route::get('setting/distribution', 'DistributionController@index')->name('distribution.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|distribution_list');
        Route::get('setting/distribution/add', 'DistributionController@create')->name('distribution.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|distribution_add');
        Route::post('setting/distribution/add', 'DistributionController@store')->name('distribution.add.post');
        Route::get('setting/distribution/edit/{id}', 'DistributionController@edit')->name('distribution.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|distribution_edit');
        Route::post('setting/distribution/edit/{id}', 'DistributionController@update')->name('distribution.edit.post');


        Route::group(['middleware' => 'role_or_permission:Super-Admin|Reseller'], function () {
            Route::get('setting', 'ConfigController@index')->name('config.index');
            Route::post('setting', 'ConfigController@store')->name('config.store');
            Route::get('setting/payment-gateway', 'ConfigController@paymentApi')->name('config.payment.api');
            Route::post('setting/bkash-api', 'ConfigController@bkashApiStore')->name('config.bkash.api.post');
            Route::post('setting/nagad-api', 'ConfigController@nagadApiStore')->name('config.nagad.api.post');

            Route::get('setting/sms-content', 'ConfigController@smsSetup')->name('config.sms.content');
            Route::post('setting/sms-content', 'ConfigController@smsSetupUpdate')->name('config.sms.content.update');
            Route::post('setting/sms-api', 'ConfigController@smsAPIUpdate')->name('config.sms.api');
            Route::post('setting/custom-sms-send', 'ConfigController@customSMSSend')->name('custom.sms.send');
        });

        Route::group(['middleware' => ['roles'], 'roles' => [1, 2, 5]], function () {

            Route::get('invoice/branches', 'InvoiceController@branchesInvoiceList')->name('invoice.branches');
            Route::get('invoice/due-invoice', 'InvoiceController@branchesDueInvoiceList')->name('invoice.due.branches');
            Route::get('invoice/resellers', 'InvoiceController@resellersInvoiceList')->name('invoice.resellers');

            Route::get('receipt/branches', 'MoneyReceiptController@branchesReceiptList')->name('receipt.branches');
            Route::get('transaction/branches', 'TransactionController@branchList')->name('transaction.branches');

            Route::get('reports/branch-income', 'ReportController@incomeReportBranch')->name('report.income.branch');
            Route::post('reports/branch-income', 'ReportController@byDayIncomeReportBranch')->name('report.income.list.branch');
            Route::get('reports/branch-income-this-month', 'ReportController@thisMonthIncomeReportBranch')->name('report.income.month.branch');
            Route::get('reports/branch-income-last-month', 'ReportController@lMonthIncomeReportBranch')->name('report.income.lmonth.branch');

            Route::get('reports/branch-expanse', 'ReportController@expanseReportBranch')->name('report.expanse.branch');
            Route::post('reports/branch-expanse', 'ReportController@byDayExpanseReportBranch')->name('report.expanse.list.branch');
            Route::get('reports/branch-expanse-this-month', 'ReportController@thisMonthExpanseReportBranch')->name('report.expanse.month.branch');
            Route::get('reports/branch-expanse-last-month', 'ReportController@lMonthExpanseReportBranch')->name('report.expanse.lmonth.branch');

            Route::get('inventory/branch-products', 'InventoryController@productsBranch')->name('product.branch.index');
            Route::get('inventory/branch-purchases', 'InventoryController@purchasesBranch')->name('purchases.branch');

            Route::get('inventory/branch-stock-items', 'InventoryController@stockItemBranch')->name('inventory.stock.item.branch');
            Route::get('inventory/branch-used-items', 'InventoryController@usedItemBranch')->name('inventory.used.item.branch');
            Route::get('inventory/branch-sold-items', 'InventoryController@soldItemBranch')->name('inventory.sold.item.branch');
            Route::get('inventory/branch-refund-items', 'InventoryController@refundItemBranch')->name('inventory.refund.item.branch');
            Route::get('inventory/branch-lost-items', 'InventoryController@lostItemBranch')->name('inventory.lost.item.branch');

            Route::get('inventory/branch-items-detail', 'InventoryController@itemsDetailBranch')->name('inventory.items.detail.branch');
        });


        //=======================ACCOUNTS============================//
        Route::get('account', 'AccountController@index')->name('account.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|account_list');
        Route::get('account/add', 'AccountController@create')->name('account.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|account_add');
        Route::post('account/add', 'AccountController@store')->name('account.add.post');
        Route::get('account/edit/{id}', 'AccountController@edit')->name('account.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|account_edit');
        Route::post('account/edit/{id}', 'AccountController@update')->name('account.edit.post');
        Route::get('account/transfer', 'AccountController@transferCreate')->name('account.transfer')
            ->middleware('role_or_permission:Super-Admin|Reseller|account_balance transfer');
        Route::post('account/transfer', 'AccountController@transferStore')->name('account.transfer.post');

        //=======================EXPENSE============================//
        Route::get('expanse-cat-list', 'ExpenseController@expanseCatList')->name('expanse.cat.list')
            ->middleware('role_or_permission:Super-Admin|Reseller|expense_category list');
        Route::get('expanse-cat-add', 'ExpenseController@expanseCatAdd')->name('expanse.cat.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|expense_category add');
        Route::post('expanse-cat-add', 'ExpenseController@expanseCatStore')->name('expanse.cat.add.post');
        Route::get('expanse-cat-edit/{id}', 'ExpenseController@expanseCatEdit')->name('expanse.cat.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|expense_category edit');
        Route::post('expanse-cat-edit/{id}', 'ExpenseController@expanseCatUpdate')->name('expanse.cat.edit.post');

        Route::get('expanse-list', 'ExpenseController@index')->name('expanse.list')
            ->middleware('role_or_permission:Super-Admin|Reseller|expense_list');
        Route::get('branch-expanse-list', 'ExpenseController@branchExpanseList')->name('branch.expanse.list');
        Route::get('expanse-add', 'ExpenseController@create')->name('expanse.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|expense_add');
        Route::post('expanse-add', 'ExpenseController@store')->name('expanse.add.post');
        Route::get('expanse-edit/{id}', 'ExpenseController@edit')->name('expanse.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|expense_edit');
        Route::post('expanse-edit/{id}', 'ExpenseController@update')->name('expanse.edit.post');


        //=======================INCOME============================//
        Route::get('income-cat-list', 'IncomeController@incomeCatList')->name('income.cat.list')
            ->middleware('role_or_permission:Super-Admin|Reseller|income_category list');
        Route::get('income-cat-add', 'IncomeController@incomeCatAdd')->name('income.cat.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|income_category add');
        Route::post('income-cat-add', 'IncomeController@incomeCatStore')->name('income.cat.add.post');
        Route::get('income-cat-edit/{id}', 'IncomeController@incomeCatEdit')->name('income.cat.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|income_category edit');
        Route::post('income-cat-edit/{id}', 'IncomeController@incomeCatUpdate')->name('income.cat.edit.post');

        Route::get('income-list', 'IncomeController@index')->name('income.list')
            ->middleware('role_or_permission:Super-Admin|Reseller|income_list');
        Route::get('branch-income-list', 'IncomeController@branchExpanseList')->name('branch.income.list');
        Route::get('income-add', 'IncomeController@create')->name('income.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|income_add');
        Route::post('income-add', 'IncomeController@store')->name('income.add.post');
        Route::get('income-edit/{id}', 'IncomeController@edit')->name('income.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|income_edit');
        Route::post('income-edit/{id}', 'IncomeController@update')->name('income.edit.post');


        //=======================COMPLAIN============================//
        Route::get('complain/pending', 'ComplainController@pending')->name('complain.pending')
            ->middleware('role_or_permission:Super-Admin|Reseller|ticket_pending list');
        Route::get('complain/solved', 'ComplainController@solved')->name('complain.solved')
            ->middleware('role_or_permission:Super-Admin|Reseller|ticket_solved list');
        Route::get('complain/create', 'ComplainController@create')->name('complain.create')
            ->middleware('role_or_permission:Super-Admin|Reseller|ticket_add');
        Route::post('complain/store', 'ComplainController@store')->name('complain.store');
        Route::get('complain/solve/{id}', 'ComplainController@solveComplain')->name('complain.solve')
            ->middleware('role_or_permission:Super-Admin|Reseller|ticket_mark as solved');
        Route::get('complain/unsolve/{id}', 'ComplainController@unsolveComplain')->name('complain.unsolve')
            ->middleware('role_or_permission:Super-Admin|Reseller|ticket_mark as unsolved');
        Route::get('complain/edit/{id}', 'ComplainController@edit')->name('complain.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|ticket_edit');
        Route::post('complain/update/{id}', 'ComplainController@update')->name('complain.update');
        Route::post('complain/delete/{id}', 'ComplainController@destroy')->name('complain.delete')
            ->middleware('role_or_permission:Super-Admin|Reseller|ticket_delete');


        //=======================INVOICE============================//
        Route::get('invoice', 'InvoiceController@index')->name('invoice.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_list');
        Route::get('invoice/due-invoice-list', 'InvoiceController@dueInvoice')->name('invoice.due')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_due invoices');

        Route::get('/check-client-payment', 'InvoiceController@checkClientPayment')->name('check.client.payment')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_add');

        Route::get('invoice/add', 'InvoiceController@create')->name('invoice.add')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_add');
        Route::post('invoice/add', 'InvoiceController@store')->name('invoice.add.post');
        Route::get('invoice/edit/{id}', 'InvoiceController@edit')->name('invoice.edit')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_edit');
        Route::post('invoice/edit/{id}', 'InvoiceController@update')->name('invoice.edit.post');
        Route::get('invoice/show/{id}', 'InvoiceController@show')->name('invoice.show')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_view');

        Route::get('invoice/showAll/{id}', 'InvoiceController@all_In_OneInvoicesListPrint')->name('invoice.showAll')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_view');

        Route::get('invoice/paid/{id}', 'InvoiceController@paid')->name('invoice.pay');
        Route::post('invoice/paid/{id}', 'InvoiceController@paidUpdate')->name('invoice.pay.post');
        Route::post('invoice/bulk/pay', 'InvoiceController@bulkInvoicePaid')->name('invoice.bulk.pay.post');
        Route::post('invoice/bulk/print', 'InvoiceController@bulkInvoicePrint')->name('invoice.bulk.print')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_bulk print');
        Route::get('invoice/del/{id}', 'InvoiceController@destroy')->name('invoice.del')
            ->middleware('role_or_permission:Super-Admin|Reseller|invoice_delete');
        Route::get('invoice/trash', 'InvoiceController@trashInvoice')->name('invoice.trash')
            ->middleware('role:Super-Admin');
        Route::get('invoice/restore/{id}', 'InvoiceController@restore')->name('invoice.restore')
            ->middleware('role:Super-Admin');
        Route::delete('invoice/delete', 'InvoiceController@deleteBulkInvoice')->name('invoice.delete.bulk')
            ->middleware('role:Super-Admin');

        Route::delete('invoice/bulk_trash', 'InvoiceController@trushBulkInvoice')->name('invoice.trash.bulk')
            ->middleware('role_or_permission:Super-Admin|Reseller');

        //=======================MONEY RECEIPT============================//
        Route::get('receipt', 'MoneyReceiptController@index')->name('receipt.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|receipt_list');

        Route::get('receipt/data', 'MoneyReceiptController@allData')->name('receipt.all_data.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|receipt_list');

        Route::get('receipt/view/{id}', 'MoneyReceiptController@show')->name('receipt.show')
            ->middleware('role_or_permission:Super-Admin|Reseller|receipt_view');
        Route::get('receipt/print/{id}', 'MoneyReceiptController@print')->name('receipt.print')
            ->middleware('role_or_permission:Super-Admin|Reseller|receipt_print');
        Route::get('receipt/bulk/print', 'MoneyReceiptController@bulkReceiptPrint')->name('receipt.bulk.print')
            ->middleware('role_or_permission:Super-Admin|Reseller|receipt_bulk print');

        Route::get('profile', 'ConfigController@profileEdit')->name('profile.edit');
        Route::post('profile', 'ConfigController@profileUpdate')->name('profile.edit.post');

        //=======================REPORT============================//
        Route::get('transaction', 'TransactionController@index')->name('transaction.index')
            ->middleware('role_or_permission:Super-Admin|Reseller|report_transaction');

        Route::group(['middleware' => 'role_or_permission:Super-Admin|Reseller|report_income'], function () {
            Route::get('reports/income', 'ReportController@incomeReport')->name('report.income');
            Route::get('reports/online_pay', 'ReportController@onlinePay')->name('report.onlinePay');
            Route::get('reports/profit_report', 'ReportController@resellerProfitReports')->name('report.reseller.profit')->middleware('role_or_permission:Reseller');
            Route::post('reports/income', 'ReportController@byDayIncomeReport')->name('report.income.list');
            Route::get('reports/income-this-month', 'ReportController@thisMonthIncomeReport')->name('report.income.month');
            Route::get('reports/income-last-month', 'ReportController@lMonthIncomeReport')->name('report.income.lmonth');
        });

        Route::group(['middleware' => 'role_or_permission:Super-Admin|Reseller|report_expense'], function () {
            Route::get('reports/expanse', 'ReportController@expanseReport')->name('report.expanse');
            Route::post('reports/expanse', 'ReportController@byDayExpanseReport')->name('report.expanse.list');
            Route::get('reports/expanse-this-month', 'ReportController@thisMonthExpanseReport')->name('report.expanse.month');
            Route::get('reports/expanse-last-month', 'ReportController@lMonthExpanseReport')->name('report.expanse.lmonth');
        });

        Route::get('reports/account-b', 'ReportController@dailyReport')->name('report.daily');
        Route::get('reports/pl-report', 'ReportController@accountsSummery')
            ->name('report.account.summary')
            ->middleware('role:Super-Admin');

        Route::get('clients/list-of-btrc', 'ClientController@clientListOfBtrc')->name('client.btrc')->can('report_btrc client list');

        // Client Status Report - New Report
        Route::get('reports/client-status', 'ClientController@clientStatusForNewAndOld')->name('client.status')->middleware('role_or_permission:Super-Admin|Reseller|report_client status');



        //=======================INVENTORY============================//
        Route::get('inventory/product-category-add', 'InventoryController@productCatAdd')->name('product.cat.add');
        Route::post('inventory/product-category-add', 'InventoryController@productCatStore')->name('product.cat.add.post');
        Route::get('inventory/product-category-edit/{id}', 'InventoryController@productCatEdit')->name('product.cat.edit');
        Route::post('inventory/product-category-edit/{id}', 'InventoryController@productCatUpdate')->name('product.cat.edit.post');
        Route::get('inventory/product-add', 'InventoryController@productAdd')->name('product.add');
        Route::post('inventory/product-add', 'InventoryController@productStore')->name('product.add.post');
        Route::get('inventory/product-edit/{id}', 'InventoryController@productEdit')->name('product.edit');
        Route::post('inventory/product-edit/{id}', 'InventoryController@productUpdate')->name('product.edit.post');

        Route::get('inventory/products', 'InventoryController@products')->name('product.index');
        Route::get('inventory/purchases', 'InventoryController@purchases')->name('purchases');
        Route::get('inventory/purchase-add', 'InventoryController@purchaseAdd')->name('purchase.add');
        Route::post('inventory/purchase-add', 'InventoryController@purchaseStore')->name('purchase.add.post');
        Route::get('inventory/purchase-edit/{id}', 'InventoryController@purchaseEdit')->name('purchase.edit');
        Route::post('inventory/purchase-edit/{id}', 'InventoryController@purchaseUpdate')->name('purchase.edit.post');
        Route::get('inventory/purchase-view/{id}', 'InventoryController@purchaseShow')->name('purchase.show');
        Route::get('inventory/maintain', 'InventoryController@inventoryMaintain')->name('inventory.maintain');
        Route::get('inventory/stock-maintain', function () {
            return redirect()->route('admin.dashboard');
        });
        Route::post('inventory/stock-maintain', 'InventoryController@inventoryStockMaintain')->name('inventory.stock.maintain');
        Route::get('inventory/used-maintain', function () {
            return redirect()->route('admin.dashboard');
        });
        Route::post('inventory/used-maintain', 'InventoryController@inventoryUsedMaintain')->name('inventory.used.maintain');

        Route::get('inventory/stock-items', 'InventoryController@stockItem')->name('inventory.stock.item');
        Route::get('inventory/used-items', 'InventoryController@usedItem')->name('inventory.used.item');
        Route::get('inventory/sold-items', 'InventoryController@soldItem')->name('inventory.sold.item');
        Route::get('inventory/refund-items', 'InventoryController@refundItem')->name('inventory.refund.item');
        Route::get('inventory/lost-items', 'InventoryController@lostItem')->name('inventory.lost.item');
        Route::get('inventory/items-detail', 'InventoryController@itemsDetail')->name('inventory.items.detail');


        Route::group(['middleware' => ['ajax']], function () {

            Route::get('select-plan', 'AjaxController@selectPlan')->name('ajaxSelect.plan');
            Route::get('select-invoice-plan', 'AjaxController@selectInvoicePlan')->name('ajaxSelect.invoice.plan');
            Route::get('purchases-select-product', 'InventoryController@purchaseSelectProduct')->name('purchase.select.product');
            Route::get('inventory-select-stock-product', 'InventoryController@inventorySelectStockProduct')->name('stock.maintain.product.select');
            Route::get('inventory-select-used-product', 'InventoryController@inventorySelectUsedProduct')->name('use.maintain.product.select');
            Route::get('purchases-product-sl', 'InventoryController@purchaseProductSl')->name('purchase.product.sl');
            Route::get('select-pool', 'AjaxController@selectPool')->name('ajaxSelect.pool');
            Route::get('select-server', 'AjaxController@selectServer')->name('ajaxSelect.server');
            Route::get('credit-limit', 'AjaxController@creditLimit')->name('ajaxSelect.credit.limit');
            Route::get('get-pppoe-traffic', 'AjaxController@pppoeRealTimeTraffic')->name('get.pppoe.traffic');
        });

        Route::group(['middleware' => 'role:Super-Admin'], function () {
            // Deposit Route
            Route::get('invest-list', 'InvestController@index')->name('invest.list');
            Route::get('invest-add', 'InvestController@create')->name('invest.add');
            Route::post('invest-add', 'InvestController@store')->name('invest.add.post');
            Route::get('invest-edit/{id}', 'InvestController@edit')->name('invest.edit');
            Route::post('invest-edit/{id}', 'InvestController@update')->name('invest.edit.post');

            Route::get('loan-list', 'LoanController@index')->name('loan.list');
            Route::get('loan-add', 'LoanController@create')->name('loan.add');
            Route::post('loan-add', 'LoanController@store')->name('loan.add.post');
            Route::get('loan-edit/{id}', 'LoanController@edit')->name('loan.edit');
            Route::post('loan-edit/{id}', 'LoanController@update')->name('loan.edit.post');

            Route::get('loan-pay-list', 'LoanController@loanPayList')->name('loan.pay.list');
            Route::get('loan-pay-add', 'LoanController@loanPayAdd')->name('loan.pay.add');
            Route::post('loan-pay-add', 'LoanController@loanPayStore')->name('loan.pay.add.post');
            Route::get('loan-pay-edit/{id}', 'LoanController@loanPayEdit')->name('loan.pay.edit');
            Route::post('loan-pay-edit/{id}', 'LoanController@loanPayUpdate')->name('loan.pay.edit.post');

            Route::get('investor', 'InvestorController@index')->name('investor.index');
            Route::get('investor/add', 'InvestorController@create')->name('investor.add');
            Route::post('investor/add', 'InvestorController@store')->name('investor.add.post');
            Route::get('investor/edit/{id}', 'InvestorController@edit')->name('investor.edit');
            Route::post('investor/edit/{id}', 'InvestorController@update')->name('investor.edit.post');

            Route::get('loan-payer', 'LoanPayerController@index')->name('loan.payer.index');
            Route::get('loan-payer/add', 'LoanPayerController@create')->name('loan.payer.add');
            Route::post('loan-payer/add', 'LoanPayerController@store')->name('loan.payer.add.post');
            Route::get('loan-payer/edit/{id}', 'LoanPayerController@edit')->name('loan.payer.edit');
            Route::post('loan-payer/edit/{id}', 'LoanPayerController@update')->name('loan.payer.edit.post');
        });
    });
});


require base_path('routes/bkash_checkout.php');
require base_path('routes/rocket_billPay.php');