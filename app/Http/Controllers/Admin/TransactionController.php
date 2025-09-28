<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transaction, App\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    private $from_date;
    private $to_date;
    private $range;
    public function __construct()
    {
        if (\request()->from_date != '') {
            $this->from_date = \request()->from_date;
            $this->to_date = \request()->to_date ?? date("Y-m-d");
        } else {
            $this->from_date = date("Y-m-d", strtotime('-30 days'));
            $this->to_date = date("Y-m-d");
        }

        //		$this->range = [$this->from_date.' 00:00:00',$this->to_date.' 23:59:59'];
        $this->range = [$this->from_date . ' 00:00:00', $this->to_date];
    }

    public function index(Request $request)
    {
        if (Auth::user()->branchId != '') {
            $transactions = Transaction::where("branchId", Auth::user()->branchId)
                ->where('tr_amount', '!=', 0);
            $opening_bal = Transaction::where("branchId", Auth::user()->branchId)
                ->where('tr_amount', '!=', 0);
        } else {
            $transactions = Transaction::where('resellerId', Auth::user()
                ->resellerId)->where('tr_amount', '!=', 0);
            $opening_bal = Transaction::where('resellerId', Auth::user()
                ->resellerId)->where('tr_amount', '!=', 0);
        }

        $transactions =  $transactions->whereBetween('trans_date', $this->range);


        if ($request->accountId != '') {
            $account = Account::where('resellerId', Auth::user()->resellerId)->get();
            if (!empty($account)) {
                $transactions =  $transactions->where('account_id', $request->accountId);
                $opening_bal =  $opening_bal->where('account_id', $request->accountId);
            }
        }

        $opening_balance = $opening_bal->where('trans_date', '<', $this->from_date)->sum(DB::raw('cr-dr'));
        $closing_balance = $transactions->sum(DB::raw('cr-dr')) + $opening_balance;

        $transactions = $transactions->orderBY('trans_date', 'DESC')
            ->orderBY('id', 'DESC')->get();

            // $transactions = Transaction::with('invoice.client')
            //     ->orderBy('trans_date', 'DESC')
            //     ->orderBy('id', 'DESC')
            //     ->get();


            // invoice_id => tracnsactions, clieent_id => Invoice, username =>clients;

        if ($request->from_date != '') {
            $page_title = 'Transaction List - ' . $this->from_date . ' to ' . $this->to_date;
        } else {
            $page_title = 'Transaction List - Last 30 Days';
        }

        if (Auth::user()->branchId != '') {
            $accounts = Account::where('branchId', Auth::user()->branchId)->orderBY('id', 'ASC')->get();
        } else {
            $accounts = Account::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();
        }
        return view(
            'admin.pages.report.transaction_list',
            compact('transactions', 'page_title', 'accounts', 'opening_balance', 'closing_balance')
        );
    }

    public function branchList()
    {

        $transactions = Transaction::where('resellerId', Auth::user()->resellerId)->where('tr_amount', '!=', 0)->whereNotNull("branchId")->orderBY('id', 'DESC')->get();

        $page_title = 'Branches Transaction List';


        return view('admin.pages.report.transaction_list', compact('transactions', 'page_title'));
    }
}
