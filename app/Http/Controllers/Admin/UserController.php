<?php

namespace App\Http\Controllers\Admin;

use App\Branch;
use App\Reseller;
use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::get();
        $page_title = 'Users List';
        return view('admin.pages.user.index', compact('users', 'page_title'));
    }

    public function create()
    {
        $roles = Role::whereNot('name', 'Super-Admin')->get();
        $branches = Branch::get();
        $resellers = Reseller::get();
        $page_title = 'Add User';
        return view('admin.pages.user.create', compact('roles', 'page_title', 'branches', 'resellers'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:4|confirmed',
            'password_confirmation' => 'required',
            'role_name' => 'required',
            //            'branch_name' => 'required_if:role_name,==,3',
            'reseller_name' => 'required_if:role_name,==,Reseller',
        ]);

        $inputs = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'branchId' => $request->branch_name,
            'resellerId' => $request->reseller_name,
            'active' => $request->status
        ];

        $user = User::create($inputs);
        if ($user) {
            $user->assignRole($request->role_name);

            Session::flash('message', 'User Add Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('config.users');
        } else {
            Session::flash('message', 'Data Insert Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $user_Data = User::find($id);
        if ($user_Data != '') {
            $userData = $user_Data;
            $roles = Role::whereNot('name', 'Super-Admin')->get();
            $branches = Branch::get();
            $resellers = Reseller::get();
            $page_title = 'Edit User';
            return view('admin.pages.user.edit', compact('userData', 'roles', 'page_title', 'branches', 'resellers'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('config.users');
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $user->id . '',
            'role_name' => 'required',
            //            'branch_name' => 'required_if:role_name,==,3',
            'reseller_name' => 'required_if:role_name,==,Reseller',
        ]);

        $inputs = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'branchId' => $request->branch_name,
            'resellerId' => $request->reseller_name,
            'active' => $request->status
        ];

        if ($request->password != '' && $this->validate($request, ['password' => 'required|min:4|confirmed'])) {
            $inputs['password'] = bcrypt($request->password);
        }

        $update = $user->update($inputs);
        if ($update) {
            $user->syncRoles([$request->role_name]);

            if ($request->role_name == 'Reseller' && $request->status == 0) {
                if (method_exists($this, 'resellerClientInactive')) {
                    $this->resellerClientInactive($request->reseller_name);
                } else {
                    // Handle the scenario where the method doesn't exist
                    // For now, we can log a message or do nothing
                    // Log::warning('Method resellerClientInactive does not exist.');
                }
            }
            Session::flash('message', 'User Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('config.users');
        } else {
            Session::flash('message', 'Data Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function secretLogin($id)
    {
        $user = User::findOrFail(decrypt($id));

        auth('admin')->login($user, true);

        return redirect()->route('admin.dashboard');
    }
}
