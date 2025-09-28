<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        if(Auth::user()->branchId != ''){
            $accounts = Account::where('branchId', Auth::user()->branchId)->orderBY('id', 'ASC')->get();
        } else {
            $accounts = Account::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();
        }

        if($accounts != ''){
            return view("admin.pages.account.index" , ['accounts' => $accounts]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $branchs = \App\Branch::all();
        return view('admin.pages.account.create', compact('branchs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'account_name'   => 'required|max:30',
            'account_type'     => 'required',
            'account_balance'   => 'required|numeric'
        ]);
        $inputs = $request->all();


        if($account = Account::create($inputs))
        {
            if($request->account_balance > 0){

                $tr_inputs = [
                    'account_id' => $account->id,
                    'tr_type' => 'Opening Balance of '. $account->account_name,
                    'tr_category' => 'Income',
                    'tr_amount' => $request->account_balance,
                    'cr' => $request->account_balance,
                    'user_id' => Auth::user()->id,
                    'branchId' => Auth::user()->branchId,
                    'resellerId' => Auth::user()->resellerId,
                    'trans_date' => date('Y-m-d')
                ];

                Transaction::create($tr_inputs);
            }
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('account.index');
        }else{
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->branchId != ''){
            $accountData = Account::where('branchId', Auth::user()->branchId)->find($id);
        } else {
            $accountData = Account::where('resellerId', Auth::user()->resellerId)->find($id);
        }
        $branchs = \App\Branch::all();
        if($accountData != ''){
            return view("admin.pages.account.edit" , compact('accountData', 'branchs'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('account.index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $account = Account::find($id);
        $this->validate($request, [
            'account_name'   => 'required|max:30',
            'account_type'     => 'required'
        ]);

        $inputs = $request->all();

        if($account->update($inputs))
        {
            Session::flash('message', 'Data Updated Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('account.index');
        }else{
            Session::flash('message', 'Data Updated Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function transferCreate()
    {
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        return view('admin.pages.account.transfer_add', compact('accounts'));
    }
    public function transferStore(Request $request)
    {
        $this->validate($request, [
            'transfer_from'   => 'required|integer',
            'transfer_to'     => 'required|integer|different:transfer_from',
            'amount'     => 'required|numeric',
        ]);

        $user = Auth::user();
        $fromAccount = Account::find($request->transfer_from);
        $toAccount = Account::find($request->transfer_to);

        if($request->amount > $fromAccount->account_balance){
            return back()->withErrors(['amount' => 'Insufficient Balance In This Account',]);
        }
        if($user->branchId != '' && $fromAccount->branchId != $user->branchId ){
            Session::flash('message', 'You have no permission to do this!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        if($user->resellerId != '' && $fromAccount->resellerId != $user->resellerId ){
            Session::flash('message', 'You have no permission to do this!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        $tr_from_inputs = [
            'account_id' => $request->transfer_from,
            'tr_type' => 'Balance Transfer to '. $toAccount->account_name,
            'tr_category' => 'Transfer',
            'tr_amount' => $request->amount,
            'dr' => $request->amount,
            'user_id' => $user->id,
            'branchId' => $user->branchId,
            'resellerId' => $user->resellerId,
            'trans_date' => date('Y-m-d')
        ];

        $tr_to_inputs = [
            'account_id' => $request->transfer_to,
            'tr_type' => 'Balance Received from '. $fromAccount->account_name,
            'tr_category' => 'Transfer',
            'tr_amount' => $request->amount,
            'cr' => $request->amount,
            'user_id' => $user->id,
            'branchId' => $user->branchId,
            'resellerId' => $user->resellerId,
            'trans_date' => date('Y-m-d')
        ];

        $tr_from_id = Transaction::create($tr_from_inputs)->id;
        $tr_to_id = Transaction::create($tr_to_inputs)->id;

        if(!empty($tr_from_id) && !empty($tr_to_id)){
            $from_account_balance = $fromAccount->account_balance - $request->amount;
            $to_account_balance = $toAccount->account_balance + $request->amount;

            $from_account_update = $fromAccount->update(['account_balance' => $from_account_balance]);
            $to_account_update = $toAccount->update(['account_balance' => $to_account_balance]);
            if($from_account_update && $to_account_update){
                Session::flash('message', 'Balance Transfer Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('account.index');
            } else {
                Session::flash('message', 'Data Updated Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'Data Updated Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }
}
