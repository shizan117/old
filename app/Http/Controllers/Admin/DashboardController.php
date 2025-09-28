<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\ClientPayment;
use App\Complain;
use App\Config;
use App\Expanse;
use App\Invoice;
use App\Plan;
use App\Reseller;
use App\ResellerPayment;
use App\ResellerPlan;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $auth_user = Auth::user();
        $today = date('Y-m-d');
        $month_from = date('Y-m-1');
        $month_end = date('Y-m-t');
        $expire_client_days = setting('expire_client_days');
        $reseller_balance = '';
        $total_reseller_recharge = '';
        $resellerRechagable = '';
        if (Auth::user()->branchId != '') {
            $total_client = Client::where("branchId", Auth::user()->branchId)->where("server_status", 1)->count();
            $active_client = Client::where("branchId", Auth::user()->branchId)->where('status', 'On')->count();
            $old_client = Client::where("branchId", Auth::user()->branchId)->where('server_status', '!=', '1')->count();
            $total_due = Client::where("branchId", Auth::user()->branchId)->where('status', 'On')->sum('due');
            $total_due_client = Client::where("branchId", Auth::user()->branchId)->where('status', 'On')->where('due', '>', 0)->count();
            $total_paid_client = Invoice::where("branchId", Auth::user()->branchId)->where('bill_month', date('m'))
                ->where('bill_year', date('Y'))->where('paid_amount', '>', 0)->count();
            $total_discount_client = Client::where("branchId", Auth::user()->branchId)->where('status', 'On')->where('discount', '>', 0)->count();
            $total_charged_client = Client::where("branchId", Auth::user()->branchId)->where('status', 'On')->where('charge', '>', 0)->count();

            $today_income = ClientPayment::with(['client', 'user'])->whereHas("client", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->whereDate('payment_date', $today)->sum('new_paid');

            $this_month_income = ClientPayment::with(['client', 'user'])->whereHas("client", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->whereBetween(DB::raw('DATE(payment_date)'), [$month_from, $month_end])->sum('new_paid');
            $this_month_expanse = Expanse::where("branchId", Auth::user()->branchId)
                ->whereBetween(DB::raw('DATE(date)'), [$month_from, $month_end])->sum('amount');
            $exp_clients = Client::where('status', 'On')->where('status', 'On')
                ->where("branchId", Auth::user()->branchId)
                ->whereBetween(DB::raw('DATE(expiration)'), [$today, date('Y-m-d', strtotime("+" . $expire_client_days . "days"))])
                ->orderBy('id', 'desc')->get();
            $newClients = Client::where("branchId", Auth::user()->branchId)
                ->whereBetween(DB::raw('DATE(created_at)'), [$month_from . ' 00:00:00', $month_end . ' 23:59:59'])
                ->orderBy('id', 'desc')->get();
            $pendingComplain = Complain::where('is_solved', '0')
                ->whereHas("client", function ($query) {
                    $query->where("branchId", Auth::user()->branchId);
                })
                ->where('assign_to', Auth::user()->id)
                ->orderBy('complain_date', 'desc')->get();
            $total_plans = Plan::where("branchId", Auth::user()->branchId)->count();
            $this_month_otc_charge = ClientPayment::with(['client', 'user'])->whereHas("client", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->whereBetween(DB::raw('DATE(payment_date)'), [$month_from, $month_end])->sum('otc_charge');

        }

        else {

            $total_client = Client::where('resellerId', Auth::user()->resellerId)->where("server_status", 1)->count();
            $active_client = Client::where('resellerId', Auth::user()->resellerId)->where('status', 'On')->count();
            $old_client = Client::where('resellerId', Auth::user()->resellerId)->where('server_status', '!=', '1')->count();
            $total_due = Client::where('resellerId', Auth::user()->resellerId)->where('status', 'On')->sum('due');
            $total_due_client = Client::where('resellerId', Auth::user()->resellerId)->where('status', 'On')->where('due', '>', 0)->count();
            $total_paid_client = Invoice::where('resellerId', Auth::user()->resellerId)->where('bill_month', date('m'))
                ->where('bill_year', date('Y'))->where('paid_amount', '>', 0)->count();
            $total_discount_client = Client::where('resellerId', Auth::user()->resellerId)->where('status', 'On')->where('discount', '>', 0)->count();
            $total_charged_client = Client::where('resellerId', Auth::user()->resellerId)->where('status', 'On')->where('charge', '>', 0)->count();

            $today_income = ClientPayment::with(['client', 'user'])->whereHas("client", function ($query) {
                $query->where("resellerId", Auth::user()->resellerId);
            })->whereDate('payment_date', $today)->sum('new_paid');

            $this_month_income = ClientPayment::with(['client', 'user'])->whereHas("client", function ($query) {
                $query->where("resellerId", Auth::user()->resellerId);
            })->whereBetween(DB::raw('DATE(payment_date)'), [$month_from, $month_end])->sum('new_paid');

            $this_month_expanse = Expanse::where('resellerId', Auth::user()->resellerId)
                ->whereBetween(DB::raw('DATE(date)'), [$month_from, $month_end])->sum('amount');
            $exp_clients = Client::where('status', 'On')
                ->where("resellerId", Auth::user()->resellerId)
                ->whereBetween(DB::raw('DATE(expiration)'), [$today, date('Y-m-d', strtotime("+" . $expire_client_days . "days"))])
                ->orderBy('id', 'desc')->get();
            $newClients = Client::where("resellerId", Auth::user()->resellerId)
                ->whereBetween(DB::raw('DATE(created_at)'), [$month_from . ' 00:00:00', $month_end . ' 23:59:59'])
                ->orderBy('id', 'desc')->get();
            $pendingComplain = Complain::where('is_solved', '0')
                ->whereHas("client", function ($query) {
                    $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
                });
            $this_month_otc_charge = ClientPayment::with(['client', 'user'])->whereHas("client", function ($query) {
                $query->where("resellerId", Auth::user()->resellerId);
            })->whereBetween(DB::raw('DATE(payment_date)'), [$month_from, $month_end])->sum('otc_charge');

            if (!$auth_user->hasRole(['Super-Admin', 'Reseller'])) {
                $pendingComplain =  $pendingComplain->where('assign_to', Auth::user()->id);
            }

            $pendingComplain = $pendingComplain->orderBy('complain_date', 'desc')->get();
            $total_plans = Plan::count();
        }

        $this_month_reseller_revenue = ResellerPayment::whereBetween(DB::raw('DATE(created_at)'), [$month_from, $month_end])->sum('recharge_amount');

        // Calculate reseller profit here for curent month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        
        $auth_user = Auth::user();
        $resellerId = $auth_user->resellerId;
        $resellers = Reseller::get();
        
        // Initialize the invoice query
        $invoiceData = Invoice::with(['client'])->whereHas("client")
            ->where('paid_amount', '>', 0);
        
        // Add the reseller condition if $resellerId is not empty
        if (!empty($resellerId)) {
            $invoiceData = $invoiceData->where('resellerId', $resellerId);
        }
        
        // Apply the date range filter [only for curent month]
        $invoiceData = $invoiceData->whereBetween('created_at', [$startOfMonth, $endOfMonth])->get();

        // Format the profit to two decimal places
        $total_this_month_reseller_profit = $invoiceData->sum(function ($invoice) {
            return $invoice->paid_amount - $invoice->buy_price;
        });

        // Calculate reseller profit here for current month end here

        if (Auth::user()->resellerId != '') {
            $total_plans = ResellerPlan::where('resellerId', Auth::user()->resellerId)->count();
            $reseller_balance = Reseller::find(Auth::user()->resellerId)->balance;
            $total_reseller_recharge = ResellerPayment::where('resellerId', Auth::user()->resellerId)->sum('recharge_amount');

            $totalBuyPrice = Invoice::whereHas("client", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where('server_status', 1);
            })->where('paid_amount', '=', 0)->sum('buy_price');
            $resellerRechagable = $totalBuyPrice - $reseller_balance;
        }

        if (Auth::user()->resellerId != null) {
            $sms_client_id = Reseller::where('resellerId', Auth::user()->resellerId)->value('sms_client_id');
        } else {
            $sms_client_id = setting('sms_client_id');
        }
        // $sms_balance = '';
        // if ($sms_api_key != '') {
        //     $sms_balance = file_get_contents('http://sms.deelko.net/api/balance?api_key=' . $sms_api_key);
        // }
        $sms_balance = '';
        if ($sms_client_id != '') {
            // try {
            //     $sms_balance = file_get_contents('http://smpp.revesms.com/sms/smsConfiguration/smsClientBalance.jsp?client=' . $sms_client_id);


            //     $decoded_response = json_decode($sms_balance, true);

            //     // Check if Balance is present in the response and get its value
            //     if (isset($decoded_response['Balance'])) {
            //         $sms_balance = $decoded_response['Balance'] . " TK";
            //     } else {
            //         // Handle if Balance is not present or response is invalid
            //         $sms_balance = 'Balance not found or invalid response';
            //     }
            // } catch (\Exception $e) {
            //     $sms_balance = 'API_ERROR';
            // }
             $sms_balance = 'Checking balance…';
        } 
        else {
            $sms_balance = 'Not configured';
        }
//dd($sms_client_id);

        return view('admin.pages.dashboard', compact(
            'total_client',
            'active_client',
            'old_client',
            'today_income',
            'this_month_income',
            'this_month_expanse',
            'total_due',
            'total_reseller_recharge',
            'resellerRechagable',
            'exp_clients',
            'newClients',
            'pendingComplain',
            'reseller_balance',
            'total_plans',
            'this_month_reseller_revenue',
            'total_due_client',
            'total_paid_client',
            'total_discount_client',
            'total_charged_client',
            'sms_balance',
            'total_this_month_reseller_profit',
            'sms_client_id',
            'this_month_otc_charge'
        ));
    }
}
