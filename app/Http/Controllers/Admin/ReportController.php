<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Client;
use App\ClientPayment;
use App\Expanse;
use App\ResellerPayment;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Reseller;

class ReportController extends Controller
{

    private $from_date;
    private $to_date;
    private $range;

    public function __construct()
    {
        $this->range = [Carbon::now()->startOfMonth() . ' 00:00:00', Carbon::now()->endOfMonth() . ' 23:59:59'];
        if (\request()->from_date != '') {
            $this->from_date = \request()->from_date ?? date("Y-m-d");
            $this->to_date = \request()->to_date ?? date("Y-m-d");
            $this->range = [$this->from_date . ' 00:00:00', $this->to_date . ' 23:59:59'];
        }
    }

    public function incomeReport()
    {
        $fdate = '';
        $edate = '';
        $report_date = 'Last 30 Days Report';
        $post_url = 'report.income.list';
        $this_month_url = 'report.income.month';
        $last_month_url = 'report.income.lmonth';
        $reports = Transaction::where('branchId', Auth::user()->branchId)
            ->where('tr_amount', '!=', 0)
            ->where('resellerId', Auth::user()->resellerId)
            ->where('tr_category', 'Income')
            ->whereDate('trans_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('trans_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
        $page_title = 'Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function incomeReportBranch()
    {
        $fdate = '';
        $edate = '';
        $report_date = 'Full Report';
        $post_url = 'report.income.list.branch';
        $this_month_url = 'report.income.month.branch';
        $last_month_url = 'report.income.lmonth.branch';
        $reports = Transaction::where('resellerId', Auth::user()->resellerId)->whereNotNull('branchId')->where('tr_amount', '!=', 0)->where('tr_category', 'Income')->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Branch Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function byDayIncomeReport(Request $request)
    {
        $post_url = 'report.income.list';
        $this_month_url = 'report.income.month';
        $last_month_url = 'report.income.lmonth';
        $fdate = $request->fdate ?? Carbon::now();
        $edate = $request->edate ?? Carbon::now();
        $report_date = '';
        $reports = Transaction::where('branchId', Auth::user()->branchId)->where('resellerId', Auth::user()->resellerId)->where('tr_amount', '!=', 0)->where('tr_category', 'Income')->whereBetween(DB::raw('DATE(trans_date)'), [$fdate, $edate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function byDayIncomeReportBranch(Request $request)
    {
        $post_url = 'report.income.list.branch';
        $this_month_url = 'report.income.month.branch';
        $last_month_url = 'report.income.lmonth.branch';
        $fdate = $request->fdate ?? Carbon::now();
        $edate = $request->edate ?? Carbon::now();
        $report_date = '';
        $reports = Transaction::where('resellerId', Auth::user()->resellerId)->where('tr_amount', '!=', 0)->whereNotNull('branchId')->where('tr_category', 'Income')->whereBetween(DB::raw('DATE(trans_date)'), [$fdate, $edate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Branch Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function thisMonthIncomeReport()
    {
        $post_url = 'report.income.list';
        $this_month_url = 'report.income.month';
        $last_month_url = 'report.income.lmonth';
        $sdate = date('Y-m-1');
        $enddate = date('Y-m-t');
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . date('F-Y');
        $reports = Transaction::where('branchId', Auth::user()->branchId)->where('tr_amount', '!=', 0)->where('resellerId', Auth::user()->resellerId)->where('tr_category', 'Income')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function thisMonthIncomeReportBranch()
    {
        $post_url = 'report.income.list.branch';
        $this_month_url = 'report.income.month.branch';
        $last_month_url = 'report.income.lmonth.branch';
        $sdate = date('Y-m-1');
        $enddate = date('Y-m-t');
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . date('F-Y');
        $reports = Transaction::whereNotNull('branchId')->where('tr_amount', '!=', 0)->where('resellerId', Auth::user()->resellerId)->where('tr_category', 'Income')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Branch Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function lMonthIncomeReport()
    {
        $post_url = 'report.income.list';
        $this_month_url = 'report.income.month';
        $last_month_url = 'report.income.lmonth';
        $sdate = date('Y-m-d', strtotime('first day of last month'));
        $enddate = date('Y-m-d', strtotime('last day of last month'));
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . Date('F-Y', strtotime(date('F') . " last month"));
        $reports = Transaction::where('branchId', Auth::user()->branchId)->where('tr_amount', '!=', 0)->where('resellerId', Auth::user()->resellerId)->where('tr_category', 'Income')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function lMonthIncomeReportBranch()
    {
        $post_url = 'report.income.list.branch';
        $this_month_url = 'report.income.month.branch';
        $last_month_url = 'report.income.lmonth.branch';
        $sdate = date('Y-m-d', strtotime('first day of last month'));
        $enddate = date('Y-m-d', strtotime('last day of last month'));
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . Date('F-Y', strtotime(date('F') . " last month"));
        $reports = Transaction::where('resellerId', Auth::user()->resellerId)->whereNotNull('branchId')->where('tr_amount', '!=', 0)->where('tr_category', 'Income')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Income Reports';
        return view(
            'admin.pages.report.report_income',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function expanseReport()
    {
        $fdate = '';
        $edate = '';
        $report_date = 'Full Report';
        $post_url = 'report.expanse.list';
        $this_month_url = 'report.expanse.month';
        $last_month_url = 'report.expanse.lmonth';
        $reports = Transaction::where('branchId', Auth::user()->branchId)
            ->where('resellerId', Auth::user()->resellerId)
            ->where('tr_amount', '!=', 0)
            ->where('tr_category', 'Expanse')
            ->orderBY('trans_date', 'DESC')
            ->orderBY('id', 'DESC')
            ->get();
        $page_title = 'Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function expanseReportBranch()
    {
        $fdate = '';
        $edate = '';
        $report_date = 'Full Report';
        $post_url = 'report.expanse.list.branch';
        $this_month_url = 'report.expanse.month.branch';
        $last_month_url = 'report.expanse.lmonth.branch';
        $reports = Transaction::where('resellerId', Auth::user()->resellerId)->whereNotNull('branchId')->where('tr_amount', '!=', 0)->where('tr_category', 'Expanse')->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Branch Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function byDayExpanseReport(Request $request)
    {
        $post_url = 'report.expanse.list';
        $this_month_url = 'report.expanse.month';
        $last_month_url = 'report.expanse.lmonth';
        $fdate = $request->fdate ?? Carbon::now();
        $edate = $request->edate ?? Carbon::now();
        $report_date = '';
        $reports = Transaction::where('branchId', Auth::user()->branchId)->where('resellerId', Auth::user()->resellerId)->where('tr_amount', '!=', 0)->where('tr_category', 'Expanse')->whereBetween(DB::raw('DATE(trans_date)'), [$fdate, $edate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function byDayExpanseReportBranch(Request $request)
    {
        $post_url = 'report.expanse.list.branch';
        $this_month_url = 'report.expanse.month.branch';
        $last_month_url = 'report.expanse.lmonth.branch';
        $fdate = $request->fdate ?? Carbon::now();
        $edate = $request->edate ?? Carbon::now();
        $report_date = '';
        $reports = Transaction::where('resellerId', Auth::user()->resellerId)->whereNotNull('branchId')->where('tr_amount', '!=', 0)->where('tr_category', 'Expanse')->whereBetween(DB::raw('DATE(trans_date)'), [$fdate, $edate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Branch Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function thisMonthExpanseReport()
    {
        $post_url = 'report.expanse.list';
        $this_month_url = 'report.expanse.month';
        $last_month_url = 'report.expanse.lmonth';
        $sdate = date('Y-m-1');
        $enddate = date('Y-m-t');
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . date('F-Y');
        $reports = Transaction::where('branchId', Auth::user()->branchId)->where('resellerId', Auth::user()->resellerId)->where('tr_amount', '!=', 0)->where('tr_category', 'Expanse')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function thisMonthExpanseReportBranch()
    {
        $post_url = 'report.expanse.list.branch';
        $this_month_url = 'report.expanse.month.branch';
        $last_month_url = 'report.expanse.lmonth.branch';
        $sdate = date('Y-m-1');
        $enddate = date('Y-m-t');
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . date('F-Y');
        $reports = Transaction::whereNotNull('branchId')->where('resellerId', Auth::user()->resellerId)->where('tr_amount', '!=', 0)->where('tr_category', 'Expanse')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Branch Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function lMonthExpanseReport()
    {
        $post_url = 'report.expanse.list';
        $this_month_url = 'report.expanse.month';
        $last_month_url = 'report.expanse.lmonth';
        $sdate = date('Y-m-d', strtotime('first day of last month'));
        $enddate = date('Y-m-d', strtotime('last day of last month'));
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . Date('F-Y', strtotime(date('F') . " last month"));
        $reports = Transaction::where('branchId', Auth::user()->branchId)->where('resellerId', Auth::user()->resellerId)->where('tr_amount', '!=', 0)->where('tr_category', 'Expanse')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }

    public function lMonthExpanseReportBranch()
    {
        $post_url = 'report.expanse.list.branch';
        $this_month_url = 'report.expanse.month.branch';
        $last_month_url = 'report.expanse.lmonth.branch';
        $sdate = date('Y-m-d', strtotime('first day of last month'));
        $enddate = date('Y-m-d', strtotime('last day of last month'));
        $fdate = '';
        $edate = '';
        $report_date = 'Report Of ' . Date('F-Y', strtotime(date('F') . " last month"));

        $reports = Transaction::where('resellerId', Auth::user()->resellerId)->whereNotNull('branchId')->where('tr_amount', '!=', 0)->where('tr_category', 'Expanse')->whereBetween(DB::raw('DATE(trans_date)'), [$sdate, $enddate])->orderBY('trans_date', 'DESC')->get();
        $page_title = 'Expanse Reports';
        return view(
            'admin.pages.report.report_expanse',
            compact('reports', 'page_title', 'fdate', 'edate', 'post_url', 'report_date', 'this_month_url', 'last_month_url')
        );
    }


    public function dailyReport()
    {
        $paydetails = Transaction::select(DB::raw('DATE(trans_date) as date'))
            ->groupBy('date')->get();
        foreach ($paydetails as $pay) {
            $cr = Transaction::whereDate('trans_date', $pay->date)->sum('cr');
            $dr = Transaction::whereDate('trans_date', $pay->date)->sum('dr');
            $fDay = '1970-0-0';
            $day_before = date('Y-m-d', strtotime($pay->date . ' -1 day'));
            $pre_c = Transaction::whereBetween(DB::raw('DATE(trans_date)'), [$fDay, $day_before])->sum('cr');
            $pre_d = Transaction::whereBetween(DB::raw('DATE(trans_date)'), [$fDay, $day_before])->sum('dr');

            echo '<table><tr><td>' . $pay->date . '</td><td>' . ($pre_c - $pre_d) . '</td><td>' . $cr . '<br>' . $dr . '</td><td>' . (($pre_c - $pre_d) + ($cr - $dr)) . '</td></tr></table>';
        }

        $date = '2019-12-10';
        $cDate = date($date . " H:i:s");

        $newDate = date("Y-d-m H:i:s", strtotime($cDate));

        echo $cDate . '<br>';


        $cr = \App\Transaction::sum('cr');
        $dr = \App\Transaction::sum('dr');
        echo $cr - $dr;
    }


    // public function accountsSummery(Request $request)
    // {
    //     $page_title = 'Accounts Summary - All';
    //     if($request->from_date != ''){
    //         $from_date = $request->from_date??date("Y-m-1");
    //         $to_date = $request->to_date??date("Y-m-t");
    //         $date_range = [$from_date,$to_date];
    //     }

    //     $client_payment = ClientPayment::where('resellerId', Auth::user()->resellerId)
    //         ->where("branchId", Auth::user()->branchId);
    //     $service_charge = ClientPayment::where('resellerId', Auth::user()->resellerId)
    //         ->where("branchId", Auth::user()->branchId);
    //     $reseller_recharge = ResellerPayment::query();
    //     $expense = Expanse::where('resellerId', Auth::user()->resellerId)
    //         ->where("branchId", Auth::user()->branchId);

    //     if(isset($date_range)) {
    //         $page_title = 'Accounts Summary - '.$from_date.' to '.$to_date;
    //         $client_payment = $client_payment->whereBetween('payment_date', $date_range);
    //         $reseller_recharge = $reseller_recharge->whereBetween('created_at', $date_range);
    //         $expense = $expense->whereBetween('date', $date_range);
    //     }

    //     $ac_receivable = Client::where('resellerId', Auth::user()->resellerId)
    //         ->where("branchId", Auth::user()->branchId)
    //         ->where('status','On')->sum('due');

    //     $client_payment = $client_payment->sum('new_paid');
    //     $service_charge =  $service_charge->sum('service_charge');
    //     $reseller_recharge =  $reseller_recharge->sum('recharge_amount');

    //     $data['total_client_payment'] = $client_payment - $service_charge;
    //     $data['total_reseller_recharge'] = $reseller_recharge;
    //     $data['total_service_charge'] = $service_charge;

    //     $data['total_income'] = $data['total_client_payment']+$data['total_reseller_recharge']+$data['total_service_charge'];
    //     $data['total_expense'] = $expense->sum('amount');
    //     $data['ac_receivable'] = $ac_receivable;

    //     return view('admin.pages.report.account_summary',  compact('data','page_title'));
    // }

    public function accountsSummery(Request $request)
    {
        $page_title = 'Accounts Summary - All';
        if ($request->from_date != '') {
            $from_date = $request->from_date ?? date("Y-m-1");
            $to_date = $request->to_date ?? date("Y-m-t");
            $date_range = [$from_date, $to_date];
        }
        $client_payment = ClientPayment::where('resellerId', Auth::user()->resellerId)
            ->where("branchId", Auth::user()->branchId);
        $client_payment_otc_charge = ClientPayment::where('resellerId', Auth::user()->resellerId)
            ->where("branchId", Auth::user()->branchId);
        $service_charge = ClientPayment::where('resellerId', Auth::user()->resellerId)
            ->where("branchId", Auth::user()->branchId);
        $reseller_recharge = ResellerPayment::query();
        $expense = Expanse::where('resellerId', Auth::user()->resellerId)
            ->where("branchId", Auth::user()->branchId);
        if (isset($date_range)) {
            $page_title = 'Accounts Summary - ' . $from_date . ' to ' . $to_date;
            $client_payment = $client_payment->whereBetween('payment_date', $date_range);
            $client_payment_otc_charge = $client_payment_otc_charge->whereBetween('payment_date', $date_range);
            $service_charge = $service_charge->whereBetween('created_at', $date_range);
            $reseller_recharge = $reseller_recharge->whereBetween('created_at', $date_range);
            $expense = $expense->whereBetween('date', $date_range);
        }

        $ac_receivable = Client::where('resellerId', Auth::user()->resellerId)
            ->where("branchId", Auth::user()->branchId)
            ->where('status', 'On')->sum('due');
        $client_payment = $client_payment->sum('new_paid');
        $client_payment_otc_charge = $client_payment_otc_charge->sum('otc_charge');
        $service_charge = $service_charge->sum('service_charge');
        $reseller_recharge = $reseller_recharge->sum('recharge_amount');
        $data['total_client_payment'] = $client_payment - $service_charge - $client_payment_otc_charge;
        $data['total_client_otc_charge'] = $client_payment_otc_charge;
        $data['total_reseller_recharge'] = $reseller_recharge;
        $data['total_service_charge'] = $service_charge;
        $data['total_income'] = $data['total_client_payment'] + $data['total_reseller_recharge'] + $data['total_service_charge'] + $data['total_client_otc_charge'];
        $data['total_expense'] = $expense->sum('amount');
        $data['ac_receivable'] = $ac_receivable;
        return view('admin.pages.report.account_summary', compact('data', 'page_title'));
    }


   public function onlinePay(Request $request)
    {
        $page_title = 'Online Payments';
        $auth_user = Auth::user();

        $resellers = DB::table('resellers')->select('resellerId', 'resellerName')->get();
        $selectedResellerId = $request->input('resellerId');

        $query = DB::table('bkash_webhook_transactions as bwt')
            ->leftJoin('clients as c', 'bwt.transactionReference', '=', 'c.username')
            ->select(
                'bwt.*',
                'c.client_name',
                'c.username',
                'c.phone',
                'c.resellerId as client_reseller_id'
            )
            ->orderBy('bwt.id', 'desc');

        if (!empty($selectedResellerId)) {
            $query->where('c.resellerId', $selectedResellerId);
        }

        $allBkashWebHookTrans = $query->get();

        $bkash_account_info = DB::table('accounts')
            ->where('account_type', 'bKash')
            ->where('account_name', 'bkash')
            ->first();

        return view('admin.pages.report.report_onlinePay', compact(
            'page_title',
            'allBkashWebHookTrans',
            'bkash_account_info',
            'resellers',
            'selectedResellerId'
        ));
    }

    public function resellerProfitReports(Request $request)
    {
        $auth_user = Auth::user();
        $resellerId = ($auth_user->resellerId != '') ? $auth_user->resellerId : $request->resellerId;
        $resellers = Reseller::get();
        if ($resellerId != '') {
            $invoiceData = Invoice::with(['client'])->whereHas("client")
                ->where('resellerId', $resellerId)->where('paid_amount', '>', 0);
        }
        if ($request->from_date != '') {
            $invoiceData = $invoiceData->whereBetween('created_at', $this->range);
        }
        $invoiceData = $invoiceData->whereBetween('created_at', $this->range)->get();


        $page_title = 'Profit Reports';
        $admin = false;
        $route_url = 'report.reseller.profit';

        return view(
            'admin.pages.report.report_reseller_profit',
            compact('invoiceData', 'resellers', 'route_url', 'page_title', 'admin')
        );
    }
}
