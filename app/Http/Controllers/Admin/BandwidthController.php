<?php

namespace App\Http\Controllers\Admin;

use App\Bandwidth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class BandwidthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bandwidthData = Bandwidth::orderBY('id', 'ASC')->get();
        if($bandwidthData != ''){
            return view("admin.pages.bandwidth.index" , [
                'bandwidthData' => $bandwidthData
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.bandwidth.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = [
            'bandwidth_name'    => 'required|unique:bandwidths|max:30',
            'rate_down'         => 'required|numeric',
            'rate_down_unit'    => 'required',
            'rate_up'           => 'required|numeric',
            'rate_up_unit'      => 'required',
            'bandwidth_allocation_mb'      => 'required'
        ];

        $this->validate($request, $validate);

        $inputs     = $request->all();

        if(Bandwidth::create($inputs))
        {
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('bandwidth.index');
        }else{
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $bandwidth_Data = Bandwidth::find($id);
        if($bandwidth_Data != ''){
            return view("admin.pages.bandwidth.edit" , [
                'bandwidthData' => $bandwidth_Data
            ]);
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('bandwidth.index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $bandwidth_Data = Bandwidth::find($id);
        $validate = [
            'bandwidth_name'    => 'required|max:30|unique:bandwidths,bandwidth_name,' . $bandwidth_Data->id . '',
            'rate_down'         => 'required|numeric',
            'rate_down_unit'    => 'required',
            'rate_up'           => 'required|numeric',
            'rate_up_unit'      => 'required',
            'bandwidth_allocation_mb'      => 'required'
        ];

        $this->validate($request, $validate);

        $inputs     = $request->all();

        if($bandwidth_Data->update($inputs))
        {
            Session::flash('message', 'Data Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('bandwidth.index');
        }else{
            Session::flash('message', 'Data Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
