<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Validation\Rule;
use App\Pool;
use App\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Plan;
use Illuminate\Support\Facades\Session;
use MikrotikAPI\Roar\Roar;
use MikrotikAPI\Commands\IP\Pool as PoolApi;
use Illuminate\Support\Facades\Log;

class PoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $poolData = Pool::with('server')->get();


        if ($poolData != '') {
            return view("admin.pages.pool.index", [
                'poolData' => $poolData
            ]);
        }else{
            Session::flash('message', 'No Data Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('admin.dashboard');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $servers = Server::where('status',1)->get();
        return view('admin.pages.pool.create', compact('servers'));
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
            'pool_name' =>['required','max:30',
                Rule::unique('pools', 'pool_name')->whereNull('deleted_at'),
            ],
            'server_id' => 'required',
            'range_ip' => [
                Rule::unique('pools', 'range_ip')->whereNull('deleted_at'),
            ],
        ]);

        if(setting('using_mikrotik')){

            $server = Server::find($request->server_id);
            $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));

            $data = [
                'name' => $request->pool_name,
                'ranges' => $request->range_ip
            ];


            if ($con->isConnected()) {
                $pool = new PoolApi($con);
                $pool->add($data);
                $new_pool = $pool->getId($request->pool_name);
                if (!empty($new_pool)) {
                    $inputs = $request->all();
                    if (Pool::create($inputs)) {
                        Session::flash('message', 'Data Save Successful!');
                        Session::flash('m-class', 'alert-success');
                        return redirect()->route('pool.index');
                    } else {
                        Session::flash('message', 'Data Save Failed!');
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('message', 'Pool Add Failed To Server!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            } else {
                Session::flash('message', 'Server Connect Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            $inputs = $request->all();
            if (Pool::create($inputs)) {
                Session::flash('message', 'Data Save Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('pool.index');
            } else {
                Session::flash('message', 'Data Save Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
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
        $pool_Data = Pool::find($id);
        if ($pool_Data != '') {
            $server_data = Server::where('status',1)->get();
            return view("admin.pages.pool.edit", [
                'poolData' => $pool_Data,
                'serverData' => $server_data,
            ]);
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('pool.index');
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
        $pool_Data = Pool::find($id);
        $validate = [
            'pool_name' => 'required|max:30|unique:pools,pool_name,' . $pool_Data->id . '',
            'range_ip' => 'required|unique:pools,range_ip,' . $pool_Data->id . '',
            'server_id' => 'required'
        ];

        // $name = explode('-', $request->range_ip);
        $this->validate($request, $validate);

        if(setting('using_mikrotik')) {
            $server = Server::find($request->server_id);
            $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));

            $data = [
                'name' => $request->pool_name,
                'ranges' => $request->range_ip
            ];

            if ($con->isConnected()) {
                $pool = new PoolApi($con);
                $s_pool_id = $pool->getId($pool_Data->pool_name);
                $pool->set($data, $s_pool_id);
                $update_pool = $pool->detailById($s_pool_id);
                if ($update_pool['name'] == $request->pool_name && $update_pool['ranges'] == $request->range_ip) {
                    $inputs = $request->all();
                    if ($pool_Data->update($inputs)) {
                        Session::flash('message', 'Data Updated Successful!');
                        Session::flash('m-class', 'alert-success');
                        return redirect()->route('pool.index');
                    } else {
                        Session::flash('message', 'Data Update Failed!');
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('message', 'Pool Update Failed To Server!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }


            } else {
                Session::flash('message', 'Server Connect Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            $inputs = $request->all();
            if ($pool_Data->update($inputs)) {
                Session::flash('message', 'Data Updated Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('pool.index');
            } else {
                Session::flash('message', 'Data Updating Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_pool(Request $request, $id)
    {
        $pool_Data = Pool::find($id);
        $plan_Data=Plan::where('pool_id',$pool_Data->id)->get();
        

        // $name = explode('-', $request->range_ip); $this->validate($request, $validate);

        if(setting('using_mikrotik')) {
            // $server = Server::find($request->server_id);
            $server = Server::find($pool_Data['server_id']);
            $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));

            $data = [
                'name' => $request->pool_name,
                'ranges' => $request->range_ip
            ];

            if ($con->isConnected()) {
                $pool = new PoolApi($con);
                $s_pool_id = $pool->getId($pool_Data->pool_name);
                // $planProfile = new Profile($con);
                // $plan_id=$planProfile->getId($plan_Data->plan_name);
                $pool->set($data, $s_pool_id);
                
                if (!count($plan_Data)) {
                    
                    $delete_pool = $pool->delete($s_pool_id);
                    
                    if ($pool_Data->delete()) {
                        Session::flash('message', 'Pool Deleted Successful!');
                        Session::flash('m-class', 'alert-success');
                        return redirect()->back();
                    } else {
                        Session::flash('message', 'Pool Deleted Failed!');
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('message', 'Plan is exist with this pool!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }


            } else {
                Session::flash('message', 'Server Connect Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {

        if(!count($plan_Data)) {            
           
            if ($pool_Data->delete()){
                Session::flash('message', 'Pool Delete Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('pool.index');
            } else {
                Session::flash('message', 'Data Updating Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }

        else{
            Session::flash('message', 'Plan is exist with this plan!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
        }
    }
}
