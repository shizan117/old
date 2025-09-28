<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\LoanPayer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoanPayerController extends Controller
{
    public function index()
    {
        $loanPayers = LoanPayer::where('resellerId', Auth::user()->resellerId)->get();
        return view('admin.pages.loan_payer_list', compact('loanPayers'));
    }

    public function create()
    {
        return view('admin.pages.loan_payer_add');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:30',
        ]);

        $inputs = [
            'name' => $request->name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if (LoanPayer::create($inputs)) {
            Session::flash('message', 'loan Payer Add Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('loan.payer.index');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $loanPayer = LoanPayer::where('resellerId', Auth::user()->resellerId)->find($id);
        if ($loanPayer != '') {
            if ($loanPayer->pay_amount > $loanPayer->remain) {
                Session::flash('message', 'This Data Not Editable!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('loan.payer.index');
            } else {
                return view('admin.pages.loan_payer_edit', compact('loanPayer'));
            }
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('loan.payer.index');
        }
    }

    public function update(Request $request, $id)
    {

        $loanPayer = LoanPayer::find($id);
        $this->validate($request, [
            'name' => 'required|max:30'
        ]);

        $inputs = [
            'name' => $request->name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if ($loanPayer->update($inputs)) {
            Session::flash('message', 'loan Payer Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('loan.payer.index');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }
}
