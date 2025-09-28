<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use MikrotikAPI\Commands\IP\ARP;
use MikrotikAPI\Commands\IP\Firewall\FirewallAddressList;
use MikrotikAPI\Commands\IP\Hotspot\HotspotActive;
use MikrotikAPI\Commands\IP\Hotspot\HotspotUsers;
use MikrotikAPI\Commands\PPP\Active;
use MikrotikAPI\Commands\PPP\Secret;
use MikrotikAPI\Commands\Queues\Simple;
use MikrotikAPI\Roar\Roar;

class Mikrotik extends Model
{
    public function addClientToMikrotik($client, $plan, $activate=false)
    {
        $con = Roar::connect($plan->server->server_ip, $plan->server->server_port, $plan->server->username, encrypt_decrypt('decrypt', $plan->server->password));
        if ($con->isConnected()) {
            if ($plan->type == 'PPPOE') {
                $clientData = [
                    'name' => $client->username,
                    'service' => 'pppoe',
                    'password' => $client->server_password,
                    'profile' => $activate?$plan->plan_name:'Redirect Profile'
                ];

                $addToServer = new Secret($con);
                $addToServer->add($clientData);
                $new_id = $addToServer->getId($client->username);

            }
            elseif ($plan->type == 'Hotspot') {
                $clientData = [
                    'name' => $client->username,
                    'password' => $client->server_password,
                    'profile' => $activate?$plan->plan_name:'Redirect Profile'
                ];
                $addToServer = new HotspotUsers($con);
                $addToServer->add($clientData);
                $new_id = $addToServer->getId($client->username);
            }
            else {
                $b = Bandwidth::find($plan->bandwidth_id);
                ($b->rate_down_unit == 'Kbps') ? $unitdown = 'K' : $unitdown = 'M';
                ($b->rate_up_unit == 'Kbps') ? $unitup = 'K' : $unitup = 'M';
                $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;
                $clientData = [
                    'name' => $client->client_name . " (" . $client->username . ")",
                    'target' => $client->client_ip,
                    'max-limit' => $rate
                ];

                $addToServer = new Simple($con);
                $addToServer->add($clientData);
                $new_id = $addToServer->getId($client->client_name . " (" . $client->username . ")");
                if (!$activate) {
                    $firewall_address = new FirewallAddressList($con);
                    $fire_ip_data = [
                        'address' => $client->client_ip,
                        'list' => 'Redirect IP'
                    ];
                    $firewall_address->add($fire_ip_data);
                }

            }

            if (!empty($new_id)) {
                return true;
            }
        }

        return false;
    }

    public function removeClientFromMikrotik($client, $plan)
    {
        $con = Roar::connect($plan->server->server_ip, $plan->server->server_port, $plan->server->username, encrypt_decrypt('decrypt', $plan->server->password));
        if ($con->isConnected()) {
            if ($plan->type == 'PPPOE') {
                $Server = new Secret($con);
                $serverUserId = $Server->getId($client->username);
                $delActive = new Active($con);
                $oldActiveId = $delActive->getId($client->username);
                $delActive->delete($oldActiveId);

            } elseif ($plan->type == 'Hotspot') {
                $Server = new HotspotUsers($con);
                $serverUserId = $Server->getId($client->username);
                $delActive = new HotspotActive($con);
                $oldActiveId = $delActive->getId($client->username);
                $delActive->delete($oldActiveId);

            } else {
                $Server = new Simple($con);
                $serverUserId = $Server->getId($client->client_name . " (" . $client->username . ")");
                $ServerArp = new ARP($con);
                $serverArpId = $ServerArp->getId($client->client_ip);
                $ServerArp->delete($serverArpId);
                $firewall_address = new FirewallAddressList($con);
                $get_f_id = $firewall_address->getId($client->client_ip);
                $firewall_address->delete($get_f_id);
            }

            if (!empty($serverUserId) && $Server->delete($serverUserId)) {
                return true;
            }
        }

        return false;

    }

    public function activateClientInMikrotik($client)
    {
        $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
        if ($con->isConnected()) {
            if ($client->plan->type == 'PPPOE') {
                $clientData['profile'] = $client->plan->plan_name;
                $updateToServer = new Secret($con);
                $serverUserId = $updateToServer->getId($client->username);

            } elseif ($client->plan->type == 'Hotspot') {
                $clientData['profile'] = $client->plan->plan_name;
                $updateToServer = new HotspotUsers($con);
                $serverUserId = $updateToServer->getId($client->username);

            } else {
                $updateToServer = new Simple($con);
                $serverUserId = $updateToServer->getId($client->client_name . " (" . $client->username . ")");
            }

            if (!empty($serverUserId)) {
                if ($client->plan->type == 'PPPOE') {
                    $updateToServer->set($clientData, $serverUserId);
                    $delActive = new Active($con);
                    $oldActiveId = $delActive->getId($client->username);
                    $delActive->delete($oldActiveId);
                } elseif ($client->plan->type == 'Hotspot') {
                    $updateToServer->set($clientData, $serverUserId);
                    $delActive = new HotspotActive($con);
                    $oldActiveId = $delActive->getId($client->username);
                    $delActive->delete($oldActiveId);
                } else {
                    $firewall_address = new FirewallAddressList($con);
                    $get_f_id = $firewall_address->getId($client->client_ip);
                    $firewall_address->delete($get_f_id);
                }

                return true;
            }
        }

        return false;
    }

    public function deactivateClientInMikrotik($client){
        $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
        if ($con->isConnected()) {
            if ($client->plan->type == 'PPPOE') {
                $clientData['profile'] = 'Redirect Profile';
                $updateToServer = new Secret($con);
                $serverUserId = $updateToServer->getId($client->username);

            } elseif ($client->plan->type == 'Hotspot') {
                $clientData['profile'] = 'Redirect Profile';
                $updateToServer = new HotspotUsers($con);
                $serverUserId = $updateToServer->getId($client->username);

            } else {
                $updateToServer = new Simple($con);
                $serverUserId = $updateToServer->getId($client->client_name . " (" . $client->username . ")");
            }

            if (!empty($serverUserId)) {
                if ($client->plan->type == 'PPPOE') {
                    $updateToServer->set($clientData, $serverUserId);
                    $delActive = new Active($con);
                    $oldActiveId = $delActive->getId($client->username);
                    $delActive->delete($oldActiveId);
                } elseif ($client->plan->type == 'Hotspot') {
                    $updateToServer->set($clientData, $serverUserId);
                    $delActive = new HotspotActive($con);
                    $oldActiveId = $delActive->getId($client->username);
                    $delActive->delete($oldActiveId);
                    
                    
                } else {
                    $firewall_address = new FirewallAddressList($con);
                    $fire_ip_data = [
                        'address' => $client->client_ip,
                        'list' => 'Redirect IP'
                    ];
                    $firewall_address->add($fire_ip_data);
                }

                return true;
            }
        }

        return false;
    }

    public function updateInfoToMikrotik($client,$info)
    {
        $con = Roar::connect($client->plan->server->server_ip, $client->plan->server->server_port, $client->plan->server->username, encrypt_decrypt('decrypt', $client->plan->server->password));
        if ($con->isConnected()) {
            if ($client->plan->type == 'PPPOE') {
                $clientData = [
                    'name' => $info['username'],
                    'service' => 'pppoe',
                    'password' => $info['server_password'],
                    'profile' => $client->status == 'Off'?'Redirect Profile':$info['plan_name']
                ];
                $updateToServer = new Secret($con);
                $serverUserId = $updateToServer->getId($client->username);

            } elseif ($client->plan->type == 'Hotspot') {
                $clientData = [
                    'name' => $info['username'],
                    'password' => $info['server_password'],
                    'profile' => $client->status == 'Off'?'Redirect Profile':$info['plan_name']
                ];
                $updateToServer = new HotspotUsers($con);
                $serverUserId = $updateToServer->getId($client->username);

            } else {
                $b = Bandwidth::find($client->plan->bandwidth_id);
                ($b->rate_down_unit == 'Kbps') ? $unitdown = 'K' : $unitdown = 'M';
                ($b->rate_up_unit == 'Kbps') ? $unitup = 'K' : $unitup = 'M';
                $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;
                $clientData = [
//                            'name' => $request->client_name . " (" . $request->username . ")",
//                            $clientData['target'] = $request->client_ip,
                    'max-limit' => $rate
                ];

                $updateToServer = new Simple($con);
                $serverUserId = $updateToServer->getId($info['client_name'] . " (" . $info['username'] . ")");
                if ($client->status == 'Off') {
                    $firewall_address = new FirewallAddressList($con);
                    $fire_ip_data = [
                        'address' => $info['client_ip'],
                        'list' => 'Redirect IP'
                    ];
                    $firewall_address->add($fire_ip_data);
                }

            }

            if (!empty($serverUserId)) {
                if ($updateToServer->set($clientData, $serverUserId)) {
                    return true;
                }
            }
        }

        return false;

    }
}