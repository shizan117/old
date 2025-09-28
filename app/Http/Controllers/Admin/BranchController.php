<?php

namespace App\Http\Controllers\Admin;

use App\Branch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class BranchController extends Controller
{
    public function index(){
        $data = Branch::orderBY('branchId', 'ASC')->get();
        if($data != ''){
            return view("admin.pages.branch.index" , [
                'data' => $data
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
        return view('admin.pages.branch.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = [
            'branchName'        => 'required|unique:branches|max:40',
            'branchLocation'    => 'required'
        ];

        $this->validate($request, $validate);

        $inputs     = $request->all();

        if(Branch::create($inputs))
        {
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('branch.index');
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
        $data = Branch::find($id);
        if($data != ''){
            return view("admin.pages.branch.edit" , [
                'data' => $data
            ]);
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('branch.index');
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
        $data = Branch::find($id);
        $validate = [
            'branchName'        => 'required|max:30|unique:branches,branchName,'.$data->branchId.',branchId',
            'branchLocation'    => 'required'
        ];

        $this->validate($request, $validate);

        $inputs     = $request->all();

        if($data->update($inputs))
        {
            Session::flash('message', 'Data Updated Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('branch.index');
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
}
