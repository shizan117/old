<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Plan;
use App\Reseller;
use App\ResellerPlan;
use App\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MikrotikAPI\Roar\Roar,
    MikrotikAPI\Commands\PPP\Profile,
    MikrotikAPI\Commands\IP\Hotspot\HotspotUserProfiles;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->branchId != '') {
            $planData = Plan::with(['server'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('branchId', Auth::user()->branchId)->get();
        } elseif (Auth::user()->resellerId != '') {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('resellerId', Auth::user()->resellerId)->get();
        } else {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->get();
        }

        if ($planData != '') {
            return view("admin.pages.plan.index", [
                'planData' => $planData,
                'page_title' => 'All Plan List',
            ]);
        }
    }

    public function pppoeList()
    {
        if (Auth::user()->branchId != '') {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'PPPOE')->where('branchId', Auth::user()->branchId)->get();
        } elseif (Auth::user()->resellerId != '') {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'PPPOE')->where('resellerId', Auth::user()->resellerId)->get();
        } else {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'PPPOE')->get();
        }

        if ($planData != '') {
            return view("admin.pages.plan.index", [
                'planData' => $planData,
                'page_title' => 'PPPOE Plan List'
            ]);
        }
    }

    public function hotspotList()
    {
        if (Auth::user()->branchId != '') {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'Hotspot')->where('branchId', Auth::user()->branchId)->get();
        } elseif (Auth::user()->resellerId != '') {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'Hotspot')->where('resellerId', Auth::user()->resellerId)->get();
        } else {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'Hotspot')->get();
        }

        if ($planData != '') {
            return view("admin.pages.plan.index", [
                'planData' => $planData,
                'page_title' => 'Hotspot Plan List'
            ]);
        }
    }

    public function ipList()
    {
        if (Auth::user()->branchId != '') {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'IP')->where('branchId', Auth::user()->branchId)->get();
        } elseif (Auth::user()->resellerId != '') {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'IP')->where('resellerId', Auth::user()->resellerId)->get();
        } else {
            $planData = Plan::with(['server', 'reseller', 'branch'])->whereHas('server',function($query){
                $query->where('status',1);
            })->where('type', 'IP')->get();
        }

        if ($planData != '') {
            return view("admin.pages.plan.index", [
                'planData' => $planData,
                'page_title' => 'IP Plan List'
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
        $servers = Server::where('status',1)->get();
        $bandwidths = \App\Bandwidth::all();
        $branches = \App\Branch::all();
        return view('admin.pages.plan.create', compact('servers', 'bandwidths', 'branches'));
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
            'plan_name' => 'required|unique:plans',
            'bandwidth_id' => 'required',
            'type' => 'required',
            'server_id' => 'required',
            'pool_id' => 'required_if:type,==,PPPOE',
            'shared_users' => 'required_if:type,==,Hotspot',
            'duration' => 'required|numeric',
            'plan_price' => 'required|numeric'
        ];
        $this->validate($request, $validate);

        if(setting('using_mikrotik')) {
            $server = Server::find($request['server_id']);
            $con = Roar::connect($server['server_ip'], $server['server_port'], $server['username'], encrypt_decrypt('decrypt', $server['password']));


            if ($con->isConnected()) {
                $b = \App\Bandwidth::find($request->bandwidth_id);
                ($b->rate_down_unit == 'Kbps') ? $unitdown = 'K' : $unitdown = 'M';
                ($b->rate_up_unit == 'Kbps') ? $unitup = 'K' : $unitup = 'M';
                $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;
                $inputs = $request->all();
                if (Plan::create($inputs)) {
                    if ($request->type == 'PPPOE') {
                        $pool = \App\Pool::find($request->pool_id);
                        $dataProfile = [
                            'name' => $request->plan_name,
                            'local-address' => $pool->pool_name,
                            'remote-address' => $pool->pool_name,
                            'rate-limit' => $rate
                        ];
                        $pppProfile = new Profile($con);
                        $pppProfile->add($dataProfile);
                        $newProfile = $pppProfile->getId($request->plan_name);
                        if (!empty($newProfile)) {
                            Session::flash('message', 'Data Save Successful!');
                            Session::flash('m-class', 'alert-success');
                            return redirect()->route('plan.index');
                        } else {
                            Session::flash('message', 'PPPOE Profile Add Failed To Server!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    } elseif ($request->type == 'Hotspot') {
                        $dataProfile = [
                            'name' => $request->plan_name,
                            'shared-users' => $request->shared_users,
                            'rate-limit' => $rate
                        ];
                        $pppProfile = new HotspotUserProfiles($con);
                        $pppProfile->add($dataProfile);
                        $newProfile = $pppProfile->getId($request->plan_name);
                        if (!empty($newProfile)) {
                            Session::flash('message', 'Data Save Successful!');
                            Session::flash('m-class', 'alert-success');
                            return redirect()->route('plan.index');
                        } else {
                            Session::flash('message', 'Hotspor User Profile Add Failed To Server!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    } else {
                        Session::flash('message', 'Data Save Successful!');
                        Session::flash('m-class', 'alert-success');
                        return redirect()->route('plan.index');
                    }

                } else {
                    Session::flash('message', 'Data Save Failed!');
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
            if (Plan::create($inputs)) {
                Session::flash('message', 'Data Save Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('plan.index');
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
        if (Auth::user()->branchId != '') {
            $planData = Plan::where('branchId', Auth::user()->branchId)->find($id);
        } else {
            $planData = Plan::find($id);
        }


        if ($planData != '') {
            $bandwidthData = \App\Bandwidth::select('id', 'bandwidth_name')->get();
            $serverData = \App\Server::select('id', 'server_name')->find($planData->server_id);
            $branchData = \App\Branch::select('branchId', 'branchName')->get();
            $poolData = \App\Pool::select('id', 'pool_name')->get();
            return view("admin.pages.plan.edit", compact('planData', 'serverData', 'branchData', 'bandwidthData', 'poolData'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('plan.index');
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
        $plan_Data = Plan::find($id);
        if (Auth::user()->resellerId == '') {
            $validate = [
                'plan_name' => 'required|unique:plans,plan_name,' . $plan_Data->id . '',
                'bandwidth_id' => 'required',
                'type' => 'required',
                'server_id' => 'required',
                'pool_id' => 'required_if:type,==,PPPOE',
                'shared_users' => 'required_if:type,==,Hotspot',
                'duration' => 'required|numeric',
                'plan_price' => 'required|numeric'
            ];
        }

        $this->validate($request, $validate);
        $inputs = $request->all();
        $inputs['server_id'] = $plan_Data->server_id;

        if (Auth::user()->resellerId == '') {
            $b = \App\Bandwidth::find($request->bandwidth_id);
            $pool = \App\Pool::find($request->pool_id);
            if ($b->rate_down_unit == 'Kbps') {
                $unitdown = 'K';
            } else {
                $unitdown = 'M';
            }
            if ($b->rate_up_unit == 'Kbps') {
                $unitup = 'K';
            } else {
                $unitup = 'M';
            }
            $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;

            if(setting('using_mikrotik')) {
                $server = Server::find($plan_Data['server_id']);
                $con = Roar::connect($server['server_ip'], $server['server_port'], $server['username'], encrypt_decrypt('decrypt', $server['password']));

                if ($con->isConnected()) {
                    if ($request->type == 'PPPOE') {
                        $dataProfile = [
                            'name' => $request->plan_name,
                            'local-address' => $pool->pool_name,
                            'remote-address' => $pool->pool_name,
                            'rate-limit' => $rate
                        ];
                        $planProfile = new Profile($con);
                        $planId = $planProfile->getId($plan_Data['plan_name']);
                        if (!empty($planId)) {
                            $planProfile->set($dataProfile, $planId);
                            if ($plan_Data->update($inputs)) {
                                Session::flash('message', 'Data Update Successful!');
                                Session::flash('m-class', 'alert-success');
                                return redirect()->route('plan.index');
                            } else {
                                Session::flash('message', 'Data Update Failed!');
                                Session::flash('m-class', 'alert-danger');
                                return redirect()->back();
                            }
                        } else {
                            Session::flash('message', 'Data Update Failed To Server!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }


                    } elseif ($request->type == 'Hotspot') {
                        $dataProfile = [
                            'name' => $request->plan_name,
                            'shared-users' => $request->shared_users,
                            'rate-limit' => $rate
                        ];
                        $planProfile = new HotspotUserProfiles($con);
                        $planId = $planProfile->getId($plan_Data['plan_name']);
                        if (!empty($planId)) {
                            $planProfile->set($dataProfile, $planId);
                            if ($plan_Data->update($inputs)) {
                                Session::flash('message', 'Data Update Successful!');
                                Session::flash('m-class', 'alert-success');
                                return redirect()->route('plan.index');
                            } else {
                                Session::flash('message', 'Data Update Failed!');
                                Session::flash('m-class', 'alert-danger');
                                return redirect()->back();
                            }
                        } else {
                            Session::flash('message', 'Data Update Failed To Server!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    } else {
                        if ($plan_Data->update($inputs)) {
                            Session::flash('message', 'Data Update Successful!');
                            Session::flash('m-class', 'alert-success');
                            return redirect()->route('plan.index');
                        } else {
                            Session::flash('message', 'Data Update Failed!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }
                    }

                } else {
                    Session::flash('message', 'Server Connect Failed!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            } else {
                if ($plan_Data->update($inputs)) {
                    Session::flash('message', 'Data Updated Successfully!');
                    Session::flash('m-class', 'alert-success');
                    return redirect()->route('plan.index');
                } else {
                    Session::flash('message', 'Data Updating Failed!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            }

        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy_plan(Request $request, $id)
    {
       
        $plan_Data =\App\Plan::find($id);
        $client =\App\Client::where('plan_id', $plan_Data->id)   

        ->get();
        
        $b = \App\Bandwidth::find($plan_Data['bandwidth_id']);
            $pool = \App\Pool::find($plan_Data['pool_id']);
            if ($b->rate_down_unit == 'Kbps') {
                $unitdown = 'K';
            } else {
                $unitdown = 'M';
            }
            if ($b->rate_up_unit == 'Kbps') {
                $unitup = 'K';
            } else {
                $unitup = 'M';
            }
            $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;
       
        if(setting('using_mikrotik'))          
             
            {
                $server = Server::find($plan_Data['server_id']);
                $con = Roar::connect($server['server_ip'], $server['server_port'], $server['username'], encrypt_decrypt('decrypt', $server['password']));

                if ($con->isConnected()){
                
                   
                        $dataProfile = [
                            'name' => $request->plan_name,
                            'local-address' => $pool->pool_name,
                            'remote-address' => $pool->pool_name,
                            'rate-limit' => $rate,
                        ];
                        $planProfile = new Profile($con);
                        $planId = $planProfile->getId($plan_Data['plan_name']);
                        
                        
                        if (!count($client)) 
                        {
                            
                            $planProfile->set($dataProfile,$planId);
                            $delete_plan=$planProfile->delete($planId);
                            
                            // $planProfile->set($dataProfile, $planId);
                            if ($plan_Data->delete()) {
                                Session::flash('message', 'Plan Delete Successful!');
                                Session::flash('m-class', 'alert-success');
                                return redirect()->route('plan.index');
                            } else {
                                Session::flash('message', 'Plan Delete Failed!');
                                Session::flash('m-class', 'alert-danger');
                                return redirect()->back();
                            }
                        } else {
                            Session::flash('message', 'Client is exist with this plan!');
                            Session::flash('m-class', 'alert-danger');
                            return redirect()->back();
                        }

                }}
                 
                    
                else{

         
            
                if(!count($client) ){                
                    if ($plan_Data->delete()) 
                    {
                        Session::flash('message', 'Plan Deleted Successfully!');
                        Session::flash('m-class', 'alert-success');
                        return redirect()->route('plan.index');
                    }              

                
                else {
                        Session::flash('message', 'Plan Deletion Failed!');
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                } 

            
              else{
                    Session::flash('message', 'Client is exist with this plan!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }

         
            }
           

            }




//    ================ RESELLER PLAN CONTROL ===================

    public function resellerPlanList(Request $request)
    {
        $resellers = Reseller::get();
        if (Auth::user()->branchId != '') {
            $planData = ResellerPlan::with(['plan'])->whereHas('plan',function($query){
                $query->whereHas('server',function($s){
                    $s->where('status',1);
                });
            })->where('branchId', Auth::user()->branchId);
        } elseif (Auth::user()->resellerId != '') {
            $planData = ResellerPlan::with(['plan'])->whereHas('plan',function($query){
                $query->whereHas('server',function($s){
                    $s->where('status',1);
                });
            })->where('resellerId', Auth::user()->resellerId);
        } else {
            $planData = ResellerPlan::with(['plan'])->whereHas('plan',function($query){
                $query->whereHas('server',function($s){
                    $s->where('status',1);
                });
            });
        }

        if($request->resellerId != ''){
            $planData =  $planData->where('resellerId',$request->resellerId);
        }
        $planData = $planData->get();
        $reseller_has_plan_price = Reseller::where('resellerId',Auth::user()->resellerId)
            ->value('plan_price');  // fetch only extra_charge column


        if ($planData != '') {
            return view("admin.pages.plan.reseller_plan_list", [
                'planData' => $planData,
                'page_title' => 'Reseller Plan List',
                'resellers' => $resellers,
                'route_url' => 'reseller.plan.index',
                'reseller_has_plan_price'=>$reseller_has_plan_price,
            ]);
        }
    }

    public function resellerPlanCreate()
    {
        $plans = Plan::whereHas('server',function($query){
            $query->where('status',1);
        })->get();
        $resellers = \App\Reseller::all();
        return view('admin.pages.plan.reseller_plan_add', compact('plans', 'resellers'));
    }

    public function resellerPlanStore(Request $request)
    {
        $pre_plan = ResellerPlan::where('plan_id',$request->plan_id)->where('resellerId',$request->resellerId)->first();
        if (!empty($pre_plan)){
            Session::flash('message', 'Plan exist for this reseller!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        $validate = [
            'plan_id' => 'required',
            'resellerId' => 'required',
            'sell_price' => 'required|numeric',
        ];
        $this->validate($request, $validate);

        $inputs = $request->all();
        if (ResellerPlan::create($inputs)){
                Session::flash('message', 'Data Save Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('reseller.plan.index');
        } else {
            Session::flash('message', 'Failed to Save Data!!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

    }

    public function resellerPlanEdit($id)
    {
        $planData = ResellerPlan::find($id);
        $serverData = \App\Server::select('id', 'server_name')->get();

        if ($planData != '') {
            $plans = Plan::all();
            $resellerData = \App\Reseller::select('resellerId', 'resellerName')->get();
            return view("admin.pages.plan.reseller_plan_edit", compact('planData', 'plans', 'resellerData','serverData'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('reseller.plan.index');
        }
    }

    public function resellerPlanUpdate(Request $request, $id)
    {
        $plan_Data = ResellerPlan::find($id);
        if (Auth::user()->resellerId == '') {
            $validate = [
                'plan_id' => 'required',
                'resellerId' => 'required',
                'sell_price' => 'required|numeric',
            ];
        } else {
            $validate = [
                'reseller_sell_price' => 'required|numeric'
            ];
        }

        $this->validate($request, $validate);
        $inputs = $request->all();

        if ($plan_Data->update($inputs)) {
            Session::flash('message', 'Data Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('reseller.plan.index');
        } else {
            Session::flash('message', 'Data Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

    }

    public function resellerPlanDelete($id)
    {
        $reseller_plan = ResellerPlan::find($id);

        $client = Client::where('plan_id', $reseller_plan->plan_id)
            ->where('resellerId', $reseller_plan->resellerId)
            ->get();

        if(!count($client)){
            if ($reseller_plan->delete()) {
                Session::flash('message', 'Reseller Plan Deleted Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Reseller Plan Deletion Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'Client is exist with this plan!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

    }

}
