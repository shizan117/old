<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transaction,  App\Invest, App\Investor, App\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvestController extends Controller
{
    public function index()
    {
        $page_title = 'All invest List';
        $role_id = Auth::user()->roleId;
        $invests = Invest::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'DESC')->get();

        return view('admin.pages.invest.index', compact('page_title', 'role_id', 'invests'));
    }

    public function create()
    {
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        $investors = Investor::where('resellerId', Auth::user()->resellerId)->get();
        return view('admin.pages.invest.create', compact('accounts', 'investors'));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'investor' => 'required'
        ]);

        $ac = Account::find($request->account);

        $account_balance = $ac->account_balance + $request->amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_type' => 'Invest',
            'tr_category' => 'Deposit',
            'tr_amount' => $request->amount,
            'cr' => $request->amount,
            'user_id' => Auth::user()->id,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => date('Y-m-d')
        ];

        $inputs = [
            'amount' => $request->amount,
            'date' => date('Y-m-d'),
            'investor_id' => $request->investor,
            'user_id' => Auth::user()->id,
            'resellerId' => Auth::user()->resellerId,
        ];


        $investor = Investor::find($request->investor);
        $investor_input['amount'] = $investor->amount + $request->amount;


        $tr_id = Transaction::create($tr_inputs)->id;

        if (!empty($tr_id)) {
            $inputs['tr_id'] = $tr_id;
            if (Invest::create($inputs)) {
                $ac->update($ac_inputs);
                $investor->update($investor_input);
                Session::flash('message', 'Invest Add Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('invest.list');
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
        $invest = Invest::where('resellerId', Auth::user()->resellerId)->find($id);
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        $investors = Investor::where('resellerId', Auth::user()->resellerId)->get();
        if ($invest != '') {
            if (Auth::user()->id != $invest->user_id) {
                Session::flash('message', 'Only have permission this person who add this entry!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('invest.list');
            } else {
                return view('admin.pages.invest.edit', compact('invest', 'accounts', 'investors'));
            }

        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('invest.list');
        }
    }

    public function update(Request $request, $id)
    {
        $invest = Invest::find($id);

        $this->validate($request, [
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'investor' => 'required',
        ]);


        $tr = Transaction::find($invest->tr_id);
        $ac = Account::find($request->account);

        if ($request->account != $invest->transaction->account->id) {
            $old_ac = Account::find($invest->transaction->account_id);
            $old_ac_update['account_balance'] = $old_ac->account_balance - $invest->amount;
            $old_ac->update($old_ac_update);
            $ac_balance = $ac->account_balance;
        } else {
            $ac_balance = $ac->account_balance - $invest->amount;
        }
        $account_balance = $ac_balance + $request->amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];


        $investor = Investor::find($request->investor);

        if ($request->investor != $invest->investor_id) {
            $old_investor = Investor::find($invest->investor);
            $old_investor_update['amount'] = $old_investor->amount - $invest->amount;

            $old_investor->update($old_investor_update);
            $investor_amount = $investor->amount;
        } else {

            $investor_amount = $investor->amount - $invest->amount;


        }

        $investor_balance['amount'] = $investor_amount + $request->amount;


        $tr_inputs = [
            'account_id' => $request->account,
            'tr_amount' => $request->amount,
            'cr' => $request->amount,
            'trans_date' => date('Y-m-d')
        ];

        $inputs = [
            'amount' => $request->amount,
            'date' => date('Y-m-d'),
            'investor_id' => $request->investor
        ];


        if ($tr->update($tr_inputs)) {
            if ($invest->update($inputs)) {
                $ac->update($ac_inputs);
                $investor->update($investor_balance);
                Session::flash('message', 'Invest Update Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('invest.list');
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
}
