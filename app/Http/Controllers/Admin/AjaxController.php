<?php
/**
 * Created by PhpStorm.
 * User: SAIEF
 * Date: 9/1/2018
 * Time: 9:31 PM
 */

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Plan;
use App\Reseller;
use App\ResellerPlan;
use App\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MikrotikAPI\Commands\Interfaces\PPPoEClient;
use MikrotikAPI\Commands\PPP\Active;
use MikrotikAPI\Commands\Queues\Simple;
use MikrotikAPI\Roar\Roar;


class AjaxController extends Controller
{
    public function selectServer(Request $request)
    {
        $serverData = Server::where('status',1)->get();
        return response()->json($serverData);
    }

    public function selectPool(Request $request)
    {
        if (!empty($request->server_id)) {
            $poolData = Server::find($request->server_id)->pools;
            return response()->json($poolData);
        }
    }

    public function selectPlan(Request $request)
    {
        if (Auth::user()->resellerId == '') {
            if (!empty($request->server_id)) {
                $planData = Server::find($request->server_id)->plans->where('type', $request->type)
                    ->where('branchId', Auth::user()->branchId)
                    ->where('resellerId', Auth::user()->resellerId);
                return response()->json($planData);
            }
        } else {
            if (!empty($request->type)) {
                $planData = Plan::where('type',$request->type)
                    ->whereHas('resellerPlan', function($query){
                        $query->where('reseller_sell_price','>','0')->whereHas('reseller', function($s_query){
                            $s_query->where('resellerId', Auth::user()->resellerId);
                        });
                    })->get();
                return response()->json($planData);
            }
        }
    }

    public function selectInvoicePlan(Request $request)
    {
        $clientData = Client::with([
            'plan' => function ($query) {
                $query->get();
            },
            'plan.bandwidth' => function ($query) {
                $query->get();
            },
        ])->find($request->client_id);

        $clientData['reseller_plan'] = ResellerPlan::where('plan_id',$clientData->plan_id)->where('resellerId',$clientData->resellerId)->first();

        // Check if client has payment
        $hasPayment = \App\ClientPayment::where('client_id', $clientData->id)->exists();

        // Force otc_charge to 0.00 if payment exists
        if ($hasPayment) {
            $clientData->otc_charge = 0.00;
        }
        return response()->json($clientData);
    }

    public function creditLimit(Request $request)
    {
        $resellerData = Reseller::find($request->reseller_id);
        return response()->json($resellerData);
    }

    public function pppoeRealTimeTraffic(Request $request){
        $client = Client::findOrFail($request->id);
        $rows = array();
        $rows2 = array();
        $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
        if ($con->isConnected()) {
            if ($client->plan->type == 'PPPOE') {
                $ppoe_clients = new PPPoEClient($con);
                $Active = new Active($con);
                $ActiveId = $Active->getId($client->username);
                if ($ActiveId) {
                    $results = $ppoe_clients->getTraficByName($client->username);
                    if($results){
                        $rx = $results["rx-bits-per-second"] / 1024;
                        $tx = $results["tx-bits-per-second"] / 1024;    
                    } else {
                        $rx = 0;
                        $tx = 0;
                    }
                    
                    $rows['name'] = 'Tx';
                    $rows['data'][] = $tx;
                    $rows2['name'] = 'Rx';
                    $rows2['data'][] = $rx;
                }
            } elseif ($client->plan->type == 'IP'){
                $ppoe_clients = new Simple($con);
                $results = $ppoe_clients->detailByName($client->client_name.' ('. $client->username .')');
                $results = explode('/', $results['bytes']);
//                $rx = number_format($results[0], 1);
//                $tx = number_format($results[1], 1);
                $rx = 0;
                $tx = 0;
                $rows['name'] = 'Tx';
                $rows['data'][] = $tx;
                $rows2['name'] = 'Rx';
                $rows2['data'][] = $rx;
            }
        }

        $result = array();
        array_push($result,$rows);
        array_push($result,$rows2);
        return response()->json($result);
    }

}
