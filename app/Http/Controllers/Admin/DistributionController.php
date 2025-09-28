<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Distribution;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class DistributionController extends Controller
{
    public function index()
    {
        $distributions = Distribution::where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)
            ->withCount(['clients as clients_count' => function ($query) {
                $query->where('server_status', '1')
                    ->whereIn('status', ['On', 'Off']);
            }])
            ->withSum(['clients as total_due' => function ($query) {
                $query->where('server_status', '1')
                    ->whereIn('status', ['On', 'Off']);
            }], 'due')
            ->get();

        return view('admin.pages.distribution.index', compact('distributions'));
    }

    public function create()
    {
        return view('admin.pages.distribution.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'distribution' => 'required|unique:distributions,distribution',
        ], [
            'distribution.unique' => 'This distribution already exists!',
        ]);

        $inputs = [
            'distribution' => trim($request->distribution),
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
        ];

        try {
            if (Distribution::create($inputs)) {
                Session::flash('message', 'Distribution Add Successful');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('distribution.index');
            }
        } catch (\Exception $e) {
            Session::flash('message', 'Distribution Add Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }


    public function edit($id)
    {
        $distribution = Distribution::find($id);
        if (!empty($distribution)) {
            return view('admin.pages.distribution.edit', compact('distribution'));
        } else {
            Session::flash('message', 'Distribution Add Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        $distribution = Distribution::find($id);
        $this->validate($request, [
            'distribution' => 'required',
            //            'distribution' => 'required|unique:distributions,distribution,' . $distribution->id . '',
        ]);
        if ($distribution->update($request->all())) {
            Session::flash('message', 'Distribution Update Successful');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('distribution.index');
        } else {
            Session::flash('message', 'Distribution Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }
}
