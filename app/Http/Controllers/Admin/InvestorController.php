<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  App\Investor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class InvestorController extends Controller
{
    public function index()
    {
        $investors = Investor::where('resellerId', Auth::user()->resellerId)->get();
        return view('admin.pages.investor.index', compact('investors'));
    }

    public function create()
    {
        return view('admin.pages.investor.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:30'
        ]);

        $inputs = [
            'name' => $request->name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if (Investor::create($inputs)) {
            Session::flash('message', 'Investor Add Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('investor.index');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $investor = Investor::where('resellerId', Auth::user()->resellerId)->find($id);
        if ($investor != '') {
            return view('admin.pages.investor.edit', compact('investor'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('investor.index');
        }
    }

    public function update(Request $request, $id)
    {

        $investor = Investor::find($id);
        $this->validate($request, [
            'name' => 'required|max:30'
        ]);

        $inputs = [
            'name' => $request->name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if ($investor->update($inputs)) {
            Session::flash('message', 'Investor Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('investor.index');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }
}
