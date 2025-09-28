<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transaction, App\LoanPayer, App\Loan, App\Account, App\PayLoan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoanController extends Controller
{
    public function index()
    {
        $page_title = 'All Loan List';
        $role_id = Auth::user()->roleId;
        $loans = Loan::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'DESC')->get();

        return view('admin.pages.loan_list', compact('page_title', 'role_id', 'loans'));
    }

    public function create()
    {
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        $loanPayers = LoanPayer::where('resellerId', Auth::user()->resellerId)->get();
        return view('admin.pages.loan_add', compact('accounts', 'loanPayers'));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'pay_amount' => 'required|numeric|gt:0',
            'loanPayer' => 'required'
        ]);

        $ac = Account::find($request->account);

        $account_balance = $ac->account_balance + $request->amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_type' => 'Loan',
            'tr_category' => 'Deposit',
            'tr_amount' => $request->amount,
            'cr' => $request->amount,
            'user_id' => Auth::user()->id,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => date('Y-m-d')
        ];

        $inputs = [
            'amount' => $request->amount,
            'pay_amount' => $request->pay_amount,
            'date' => date('Y-m-d'),
            'loan_payer_id' => $request->loanPayer,
            'user_id' => Auth::user()->id,
            'resellerId' => Auth::user()->resellerId,
        ];


        $loanPayer = LoanPayer::find($request->loanPayer);
        $loanPayer_input = [
            'loan_amount' => $loanPayer->loan_amount + $request->amount,
            'pay_amount' => $loanPayer->pay_amount + $request->pay_amount,
            'remain' => $loanPayer->remain + $request->pay_amount
        ];


        $tr_id = Transaction::create($tr_inputs)->id;

        if (!empty($tr_id)) {
            $inputs['tr_id'] = $tr_id;
            if (Loan::create($inputs)) {
                $ac->update($ac_inputs);
                $loanPayer->update($loanPayer_input);
                Session::flash('message', 'Loan Add Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('loan.list');
            } else {
                Session::flash('message', 'Data Save Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }

        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $loan = Loan::where('resellerId', Auth::user()->resellerId)->find($id);
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        $loanPayers = LoanPayer::where('resellerId', Auth::user()->resellerId)->get();
        if ($loan != '') {
            if (Auth::user()->id != $loan->user_id) {
                Session::flash('message', 'Only have permission this person who add this entry!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('loan.list');
            } else {
                return view('admin.pages.loan_edit', compact('loan', 'accounts', 'loanPayers'));
            }

        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('loan.list');
        }
    }

    public function update(Request $request, $id)
    {
        $loan = Loan::find($id);

        $this->validate($request, [
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'pay_amount' => 'required|numeric|gt:0',
            'loanPayer' => 'required',
        ]);


        $tr = Transaction::find($loan->tr_id);
        $ac = Account::find($request->account);

        if ($request->account != $loan->transaction->account->id) {
            $old_ac = Account::find($loan->transaction->account_id);
            $old_ac_update['account_balance'] = $old_ac->account_balance - $loan->amount;
            $old_ac->update($old_ac_update);
            $ac_balance = $ac->account_balance;
        } else {
            $ac_balance = $ac->account_balance - $loan->amount;
        }
        $account_balance = $ac_balance + $request->amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];


        $loanPayer = LoanPayer::find($request->loanPayer);

        if ($request->loanPayer != $loan->loan_payer_id) {
            $old_loanPayer = LoanPayer::find($loan->loan_payer_id);
            $old_loanPayer_update = [
                'loan_amount' => $old_loanPayer->loan_amount - $loan->amount,
                'pay_amount' => $old_loanPayer->pay_amount - $loan->pay_amount,
                'remain' => $old_loanPayer->remain - $loan->pay_amount
            ];
            $old_loanPayer->update($old_loanPayer_update);
            $loanPayer_amount = $loanPayer->loan_amount;
            $loanPayer_pay_amount = $loanPayer->pay_amount;
            $loanPayer_remain = $loanPayer->pay_amount;
        } else {

            $loanPayer_amount = $loanPayer->loan_amount - $loan->amount;
            $loanPayer_pay_amount = $loanPayer->pay_amount - $loan->pay_amount;
            $loanPayer_remain = $loanPayer->remian - $loan->pay_amount;


        }

        $loanPayer_update = [
            'loan_amount' => $loanPayer_amount + $request->amount,
            'pay_amount' => $loanPayer_pay_amount + $request->pay_amount,
            'remain' => $loanPayer_pay_amount + $request->pay_amount
        ];


        $tr_inputs = [
            'account_id' => $request->account,
            'tr_amount' => $request->amount,
            'cr' => $request->amount,
            'trans_date' => date('Y-m-d')
        ];

        $inputs = [
            'amount' => $request->amount,
            'pay_amount' => $request->pay_amount,
            'loan_payer_id' => $request->loanPayer
        ];


        if ($tr->update($tr_inputs)) {
            if ($loan->update($inputs)) {
                $ac->update($ac_inputs);
                $loanPayer->update($loanPayer_update);
                Session::flash('message', 'Loan Update Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('loan.list');
            } else {
                Session::flash('message', 'Deposit Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }

        } else {
            Session::flash('message', 'Deposit Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }



    public function loanPayList()
    {
        $page_title = 'All Paid Loan List';
        $role_id = Auth::user()->roleId;
        $loans = PayLoan::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'DESC')->get();
        return view('admin.pages.loan_pay_list', compact('page_title', 'role_id', 'loans'));
    }

    public function loanPayAdd()
    {
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        $loanPayers = LoanPayer::where('resellerId', Auth::user()->resellerId)->get();
        return view('admin.pages.loan_pay_add', compact('accounts', 'loanPayers'));
    }

    public function loanPayStore(Request $request)
    {

        $this->validate($request, [
            'account' => 'required',
            'pay_amount' => 'required|numeric|gt:0',
            'loanPayer' => 'required'
        ]);

        $ac = Account::find($request->account);
        $loanPayer = LoanPayer::find($request->loanPayer);

        if ($ac->account_balance < $request->pay_amount) {
            return back()->withErrors([
                'account' => 'Insufficient Balance In This Account',
                'pay_amount' => 'Insufficient Balance',
            ]);
        }

        if ($loanPayer->remain < $request->pay_amount) {
            return back()->withErrors([
                'loanPayer' => 'This Payer Remain Amount '.$loanPayer->remain,
            ]);
        }

        $account_balance = $ac->account_balance - $request->pay_amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_type' => 'Loan',
            'tr_category' => 'Expanse',
            'tr_amount' => $request->pay_amount,
            'dr' => $request->pay_amount,
            'user_id' => Auth::user()->id,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => date('Y-m-d')
        ];

        $inputs = [
            'pay_amount' => $request->pay_amount,
            'date' => date('Y-m-d'),
            'loan_payer_id' => $request->loanPayer,
            'user_id' => Auth::user()->id,
            'resellerId' => Auth::user()->resellerId,
        ];

        $loanPayer_input = [
            'remain' => $loanPayer->remain - $request->pay_amount
        ];

        $tr_id = Transaction::create($tr_inputs)->id;

        if (!empty($tr_id)) {
            $inputs['tr_id'] = $tr_id;
            if (PayLoan::create($inputs)) {
                $ac->update($ac_inputs);
                $loanPayer->update($loanPayer_input);
                Session::flash('message', 'Loan Add Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('loan.pay.list');
            } else {
                Session::flash('message', 'Data Save Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }

        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function loanPayEdit($id)
    {
        $loan = PayLoan::where('resellerId', Auth::user()->resellerId)->find($id);
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        $loanPayers = LoanPayer::where('resellerId', Auth::user()->resellerId)->get();
        if ($loan != '') {
            if (Auth::user()->id != $loan->user_id) {
                Session::flash('message', 'Only have permission this person who add this entry!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('loan.pay.list');
            } else {
                return view('admin.pages.loan_pay_edit', compact('loan', 'accounts', 'loanPayers'));
            }

        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('loan.pay.list');
        }
    }

    public function loanPayUpdate(Request $request, $id)
    {
        $loan = PayLoan::find($id);

        $this->validate($request, [
            'account' => 'required',
            'pay_amount' => 'required|numeric|gt:0',
            'loanPayer' => 'required'
        ]);
        $rloanPayer = LoanPayer::find($request->loanPayer);

        if ($rloanPayer->remain < $request->pay_amount) {
            return back()->withErrors([
                'loanPayer' => 'This Payer Remain Amount '.$rloanPayer->remain,
            ]);
        }


        $tr = Transaction::find($loan->tr_id);
        $ac = Account::find($request->account);

        if ($request->account != $loan->transaction->account->id) {
            $old_ac = Account::find($loan->transaction->account_id);
            $old_ac_update['account_balance'] = $old_ac->account_balance + $loan->pay_amount;
            $old_ac->update($old_ac_update);
            $ac_balance = $ac->account_balance;
        } else {
            $ac_balance = $ac->account_balance + $loan->pay_amount;
        }
        $account_balance = $ac_balance - $request->pay_amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        if ($request->loanPayer != $loan->loan_payer_id) {
            $old_loanPayer = LoanPayer::find($loan->loan_payer_id);
            $old_loanPayer_update = [
                'remain' => $old_loanPayer->remain + $loan->pay_amount
            ];
            $old_loanPayer->update($old_loanPayer_update);
            $remain = $rloanPayer->remain;
        } else {
            $remain = $rloanPayer->remain + $loan->pay_amount;
        }

        $loanPayer_update = [
            'remain' => $remain - $request->pay_amount
        ];

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_amount' => $request->amount,
            'dr' => $request->amount,
            'trans_date' => date('Y-m-d')
        ];

        $inputs = [
            'amount' => $request->amount,
            'pay_amount' => $request->pay_amount,
            'loan_payer_id' => $request->loanPayer
        ];


        if ($tr->update($tr_inputs)) {
            if ($loan->update($inputs)) {
                $ac->update($ac_inputs);
                $rloanPayer->update($loanPayer_update);
                Session::flash('message', 'Loan Update Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('loan.pay.list');
            } else {
                Session::flash('message', 'Loan Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }

        } else {
            Session::flash('message', 'Loan Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }
}
