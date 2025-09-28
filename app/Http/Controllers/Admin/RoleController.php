<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        $roles = Role::whereNotIn('name',['Super-Admin','Reseller'])->get();
        return view("admin.pages.role.index" , compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission_groups['dashboard'] = Permission::where('name','like','dashboard_%')->get();
        $permission_groups['client'] = Permission::where('name','like','client_%')->get();
        $permission_groups['reseller'] = Permission::where('name','like','reseller_%')->get();
        $permission_groups['server'] = Permission::where('name','like','server_%')->get();
        $permission_groups['pool'] = Permission::where('name','like','pool_%')->get();
        $permission_groups['bandwidth'] = Permission::where('name','like','bandwidth_%')->get();
        $permission_groups['plan'] = Permission::where('name','like','plan_%')->get();
        $permission_groups['distribution'] = Permission::where('name','like','distribution_%')->get();
        $permission_groups['account'] = Permission::where('name','like','account_%')->get();
        $permission_groups['income'] = Permission::where('name','like','income_%')->get();
        $permission_groups['expense'] = Permission::where('name','like','expense_%')->get();
        $permission_groups['ticket'] = Permission::where('name','like','ticket_%')->get();
        $permission_groups['invoice'] = Permission::where('name','like','invoice_%')->get();
        $permission_groups['report'] = Permission::where('name','like','report_%')->get();
        $permission_groups['receipt'] = Permission::where('name','like','receipt_%')->get();
        return view('admin.pages.role.create',compact('permission_groups'));
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
            'name'   => 'required|unique:roles',
        ];

        $this->validate($request, $validate);

        $role = new Role();
        $role->name = $request->name;

        if($role->save())
        {
            $role->givePermissionTo($request->permission);
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('roles');
        } else {
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
        $role = Role::whereNotIn('name',['Super-Admin','Reseller'])->findOrFail($id);
        if($role != ''){
            $permission_groups['dashboard'] = Permission::where('name','like','dashboard_%')->get();
            $permission_groups['client'] = Permission::where('name','like','client_%')->get();
            $permission_groups['reseller'] = Permission::where('name','like','reseller_%')->get();
            $permission_groups['server'] = Permission::where('name','like','server_%')->get();
            $permission_groups['pool'] = Permission::where('name','like','pool_%')->get();
            $permission_groups['bandwidth'] = Permission::where('name','like','bandwidth_%')->get();
            $permission_groups['plan'] = Permission::where('name','like','plan_%')->get();
            $permission_groups['distribution'] = Permission::where('name','like','distribution_%')->get();
            $permission_groups['account'] = Permission::where('name','like','account_%')->get();
            $permission_groups['income'] = Permission::where('name','like','income_%')->get();
            $permission_groups['expense'] = Permission::where('name','like','expense_%')->get();
            $permission_groups['ticket'] = Permission::where('name','like','ticket_%')->get();
            $permission_groups['invoice'] = Permission::where('name','like','invoice_%')->get();
            $permission_groups['report'] = Permission::where('name','like','report_%')->get();
            $permission_groups['receipt'] = Permission::where('name','like','receipt_%')->get();
            return view("admin.pages.role.edit" , compact('role','permission_groups'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
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
        $validate = [
            'name'   => 'required|unique:roles,name,'.$id.'',
        ];
        $this->validate($request, $validate);

        // dd($request->name,$id);

        $role = Role::find($id);
        $role->name = $request->name;

        // dd($role->name);

        if($role->save())
        {
            $role->syncPermissions($request->permission);
            Session::flash('message', 'Data Updated Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('roles');
        }else{
            Session::flash('message', 'Data Updating Failed!');
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
