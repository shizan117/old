<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Bandwidth;
use App\Branch;
use App\Config;
use App\ClientPayment;
use App\Complain;
use App\Invoice;
use App\Distribution;
use App\Mikrotik;
use App\Plan;
use App\Reseller;
use App\ResellerPlan;
use App\Server;
use App\SMS\DeelkoSMS;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Client;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use MikrotikAPI\Util\SentenceUtil;
use MikrotikAPI\Roar\Roar,
    MikrotikAPI\Commands\PPP\Secret,
    MikrotikAPI\Commands\PPP\Active,
    MikrotikAPI\Commands\IP\Hotspot\HotspotUsers,
    MikrotikAPI\Commands\IP\Hotspot\HotspotActive,
    MikrotikAPI\Commands\IP\Firewall\FirewallAddressList,
    MikrotikAPI\Commands\IP\ARP,
    MikrotikAPI\Commands\Queues\Simple;
use Illuminate\Support\Facades\File;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $from_date;
    private $to_date;
    private $range;

    public function __construct()
    {
        if (\request()->from_date != '') {
            $this->from_date = \request()->from_date ?? date("Y-m-d");
            $this->to_date = \request()->to_date ?? date("Y-m-d");
        }
        $this->range = [$this->from_date . ' 00:00:00', $this->to_date . ' 23:59:59'];
    }

    public function index(Request $request)
    {
        $clientData = Client::with(['plan'])->where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)->where('server_status', 1);
        $admin = false;

        if ($request->from_date != '') {
            $clientData = $clientData->whereBetween('created_at', $this->range);
        }

        $clientData = $clientData->orderBy(DB::raw('LENGTH(username) , username'))->get();

        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Client List',
            'main_url' => 'client.index',
            'branch_url' => 'client.branch',
            'admin' => $admin
        ]);
    }

    public function getClientUptime(Request $request)
    {
        // Fetch clients with additional fields
        $clients = Client::select(
            'id as client_id',
            'username',
            'status',
            'client_name',   // Fetch client name
            'due',           // Fetch due amount or status
            'expiration'     // Fetch expiration date
        )
            ->where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)
            ->where('server_status', 1)
            ->get();

        // Prepare MikroTik server connections
        $servers = Server::where('status', 1)->get();
        $upTimes = [];

        // Loop through MikroTik servers to fetch active user uptime data
        foreach ($servers as $server) {
            // Establish connection to MikroTik router
            $con = Roar::connect(
                $server->server_ip,
                $server->server_port,
                $server->username,
                encrypt_decrypt('decrypt', $server->password)
            );

            if ($con->isConnected()) {
                $activeUsers = new Active($con); // Get all active users
                $users = $activeUsers->getAll();

                if (is_array($users)) {
                    foreach ($users as $user) {
                        // Map uptime data by username
                        $upTimes[$user['name']] = $user['uptime'];
                    }
                }
            }
        }

        $admin = false;

        // Check MikroTik connection status for the software
        $checkMicroTikConnectionForSoftware = DB::table('configs')
            ->where('config_title', 'using_mikrotik')
            ->value('value');

        // Map uptime data to clients
        $response = $clients->map(function ($client) use ($upTimes, $checkMicroTikConnectionForSoftware) {
            if ($checkMicroTikConnectionForSoftware == 0) {
                $client->uptime = 'using_no_microtik';
            } else {
                $client->uptime = isset($upTimes[$client->username])
                    ? $this->formatUptime($upTimes[$client->username])
                    : 'router_off'; // Default value
            }
            return $client;
        });

        return view("admin.pages.client.client_status", [
            'clientData' => $response,
            'page_title' => 'Clients Status',
            'main_url' => 'client.index',
            'branch_url' => 'client.branch',
            'admin' => $admin
        ]);
    }

    private function formatUptime($uptime)
    {
        // Initialize variables for each time component
        $years = 0;
        $months = 0;
        $weeks = 0;
        $days = 0;
        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        // Use specific regular expressions to avoid overlapping abbreviations
        if (preg_match('/(\d+)y/', $uptime, $matches)) {
            $years = (int)$matches[1];
        }
        if (preg_match('/(\d+)mo(?![nt])/', $uptime, $matches)) {  // Match "mo" only for months
            $months = (int)$matches[1];
        }
        if (preg_match('/(\d+)w/', $uptime, $matches)) {
            $weeks = (int)$matches[1];
        }
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $days = (int)$matches[1];
        }
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $hours = (int)$matches[1];
        }
        if (preg_match('/(\d+)m(?![os])/', $uptime, $matches)) {  // Match "m" only for minutes
            $minutes = (int)$matches[1];
        }
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $seconds = (int)$matches[1];
        }

        // Build the formatted uptime string based on available data
        $formattedUptime = '';
        if ($years > 0) {
            $formattedUptime .= $years . ' year' . ($years > 1 ? 's ' : ' ');
        }
        if ($months > 0) {
            $formattedUptime .= $months . ' month' . ($months > 1 ? 's ' : ' ');
        }
        if ($weeks > 0) {
            $formattedUptime .= $weeks . ' week' . ($weeks > 1 ? 's ' : ' ');
        }
        if ($days > 0) {
            $formattedUptime .= $days . ' day' . ($days > 1 ? 's ' : ' ');
        }
        if ($hours > 0) {
            $formattedUptime .= $hours . ' hour' . ($hours > 1 ? 's ' : ' ');
        }
        if ($minutes > 0) {
            $formattedUptime .= $minutes . ' minute' . ($minutes > 1 ? 's ' : ' ');
        }
        if ($seconds > 0 || $formattedUptime === '') { // Include seconds if no other units or zero uptime
            $formattedUptime .= $seconds . ' second' . ($seconds > 1 ? 's' : '');
        }

        return trim($formattedUptime);
    }

    public function dueClients()
    {
        $clientData = Client::with(['plan'])->where('server_status', 1)
            ->where('due', '>', '0')
            ->where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)
            ->orderBy(DB::raw('LENGTH(username) , username'))
            ->get();

        return view("admin.pages.client.due_client_list", [
            'clientData' => $clientData,
            'page_title' => 'All Due Clients',
            'main_url' => 'client.due',
            'branch_url' => 'client.due.branch',
        ]);
    }

    public function saveDueClients(Request $request, $id)
    {
        $request->validate([
            'due_client_note' => 'nullable|string|max:1028',
        ]);
        $client = Client::findOrFail($id);
        $client->due_client_note = $request->due_client_note;
        $client->save();
        return redirect()->back()->with('success', 'Due client note saved successfully.');
    }

    public function dueClientsBranch()
    {
        $clientData = Client::with(['plan'])->where('server_status', 1)
            ->where('due', '>', '0')
            ->where('resellerId', Auth::user()->resellerId)
            ->whereNotNull('branchId')
            ->orderBy(DB::raw('LENGTH(username) , username'))
            ->get();

        return view("admin.pages.client.due_client_list", [
            'clientData' => $clientData,
            'page_title' => 'All Due Clients',
            'main_url' => 'client.due',
            'branch_url' => 'client.due.branch',
        ]);
    }

    public function activeClient()
    {

        $clientData = Client::with(['plan'])
            ->where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)
            ->where('status', 'On')
            ->orderBy(DB::raw('LENGTH(username) , username'))
            ->get();

        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Active Client List',
            'main_url' => 'active.client',
            'branch_url' => 'client.branch.active',
        ]);
    }

    public function inactiveClient()
    {
        $clientData = Client::with(['plan'])
            ->where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)
            ->where('server_status', 1)
            ->where('status', 'Off')
            ->orderBy(DB::raw('LENGTH(username) , username'))
            ->get();

        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Inactive Client List',
            'main_url' => 'inactive.client',
            'branch_url' => 'client.branch.inactive',
        ]);
    }

    public function resellerClient(Request $request)
    {
        $resellers = Reseller::get();
        $clientData = Client::with(['plan', 'reseller'])->whereNotNull('resellerId')
            ->where('server_status', 1)
            ->where('status', 'On');

        if ($request->from_date != '') {
            $clientData = $clientData->whereBetween('created_at', $this->range);
        }
        if ($request->resellerId != '') {
            $clientData = $clientData->where('resellerId', $request->resellerId);
        }
        $clientData = $clientData->orderBy(DB::raw('LENGTH(username) , username'))->get();

        return view("admin.pages.reseller_client_list", [
            'resellers' => $resellers,
            'clientData' => $clientData,
            'page_title' => 'Reseller Active Client List',
            'route_url' => 'client.reseller',
        ]);
    }

    public function resellerInactiveClient(Request $request)
    {
        $resellers = Reseller::get();
        $clientData = Client::with(['plan', 'reseller'])->whereNotNull('resellerId')
            ->where('server_status', 1)
            ->where('status', 'Off');

        if ($request->from_date != '') {
            $clientData = $clientData->whereBetween('created_at', $this->range);
        }
        if ($request->resellerId != '') {
            $clientData = $clientData->where('resellerId', $request->resellerId);
        }
        $clientData = $clientData->orderBy(DB::raw('LENGTH(username) , username'))->get();

        return view("admin.pages.reseller_client_list", [
            'resellers' => $resellers,
            'clientData' => $clientData,
            'page_title' => 'Reseller Inactive Client List',
            'route_url' => 'client.reseller.inactive',
        ]);
    }

    public function resellerOldClient(Request $request)
    {
        $resellers = Reseller::get();
        $clientData = Client::with(['plan', 'reseller'])->whereNotNull('resellerId')
            ->where('server_status', 2)
            ->where('status', 'Off');

        if ($request->from_date != '') {
            $clientData = $clientData->whereBetween('created_at', $this->range);
        }
        if ($request->resellerId != '') {
            $clientData = $clientData->where('resellerId', $request->resellerId);
        }
        $clientData = $clientData->orderBy(DB::raw('LENGTH(username) , username'))->get();

        return view("admin.pages.reseller_client_list", [
            'resellers' => $resellers,
            'clientData' => $clientData,
            'page_title' => 'Reseller Old Client List',
            'route_url' => 'client.reseller.old',
        ]);
    }

    public function oldClient(Request $request)
    {
        $clientData = Client::with(['plan'])
            ->where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)
            ->where('server_status', '!=', 1);

        if ($request->from_date != '') {
            $clientData = $clientData->whereBetween('server_inactive_date', $this->range);
        }
        $clientData = $clientData->orderBy(DB::raw('LENGTH(username) , username'))->get();
        $admin = false;

        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Old Client List',
            'main_url' => 'old.client',
            'branch_url' => 'client.branch.old',
        ]);
    }

    public function deletedClients(Request $request)
    {

        // Retrieve the soft-deleted records
        $clientData = Client::onlyTrashed()->get();

        return view("admin.pages.client.deleted_clients", [
            'clientData' => $clientData,
            'page_title' => 'All Deleted Client List',
            'main_url' => 'deleted.client',
            'branch_url' => 'client.branch.old',
        ]);
    }

    public function deletedSingleClient(Request $request)
    {
        $id = $request->input('id'); // Retrieve the ID from the request

        // Find the soft-deleted client by ID
        $client = Client::onlyTrashed()->find($id);

        // Check if client exists
        if (!$client) {
            return redirect()->back()->with('error', 'Client not found.');
        } else {
            DB::table('permanent_deleted_clients')->insert([
                'client_name' => $client->client_name,
                'username' => $client->username,
                'client_id' => $client->id,
                'deleted_by' => Auth::id()
            ]);
            // Permanently delete the client
            $client->forceDelete();

            // Redirect with success message
            return redirect()->back()->with('success', 'Client permanently deleted.');
        }
    }

    public function restoreSingleClient(Request $request)
    {
        $id = $request->input('id'); // Retrieve the ID from the request

        // Validate the ID (ensure it's a valid integer)
        $request->validate([
            'id' => 'required|integer|exists:clients,id'
        ]);

        try {
            // Find the soft-deleted client by ID
            $client = Client::onlyTrashed()->find($id);

            // Check if client exists
            if (!$client) {
                return redirect()->back()->with('error', 'Client not found.');
            }

            // Restore the client record
            $client->restore();

            // Redirect with success message
            return redirect()->back()->with('success', 'Client restored successfully!');
        } catch (\Exception $e) {
            // Handle any exceptions
         //   \Log::error('Error restoring client: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while restoring the client.');
        }
    }

    public function deleteAllClients(Request $request)
    {
        try {
            // Retrieve all soft-deleted clients
            $clients = Client::onlyTrashed()->get();

            // Check if there are any clients to delete
            if ($clients->isEmpty()) {
                return redirect()->back()->with('info', 'No soft-deleted clients to delete.');
            }

            // Permanently delete all soft-deleted clients
            foreach ($clients as $client) {
                DB::table('permanent_deleted_clients')->insert([
                    'client_name' => $client->client_name,
                    'username' => $client->username,
                    'client_id' => $client->id,
                    'deleted_by' => Auth::id()
                ]);
                $client->forceDelete();
            }

            // Redirect with success message
            return redirect()->back()->with('success', 'All soft-deleted clients have been permanently deleted.');
        } catch (\Exception $e) {
            // Log and handle any exceptions
         //   \Log::error('Error deleting all soft-deleted clients: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while deleting the clients.');
        }
    }

    public function restoreAllClients(Request $request)
    {
        try {
            // Retrieve all soft-deleted clients
            $clients = Client::onlyTrashed()->get();

            // Check if there are any clients to delete
            if ($clients->isEmpty()) {
                return redirect()->back()->with('info', 'No soft-deleted clients to restore.');
            }

            // Permanently delete all soft-deleted clients
            foreach ($clients as $client) {
                $client->restore();
            }

            // Redirect with success message
            return redirect()->back()->with('success', 'All soft-deleted clients have been restored successfully!');
        } catch (\Exception $e) {
            // Log and handle any exceptions
          //  \Log::error('Error restoring all soft-deleted clients: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while restoring the clients.');
        }
    }

    public function branchInactiveClient()
    {
        $clientData = Client::with(['plan'])
            ->where('resellerId', Auth::user()->resellerId)
            ->whereNotNull('branchId')
            ->where('server_status', 1)
            ->where('status', 'Off')
            ->orderBy(DB::raw('LENGTH(username) , username'))
            ->get();
        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Branch Inactive Client List',
            'main_url' => 'inactive.client',
            'branch_url' => 'client.branch.inactive',
        ]);
    }

    public function branchClient()
    {
        $clientData = Client::with(['plan'])->where('resellerId', Auth::user()->resellerId)
            ->whereNotNull('branchId')
            ->orderBy(DB::raw('LENGTH(username) , username'))->get();
        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Branch Client List',
            'main_url' => 'client.index',
            'branch_url' => 'client.branch',
        ]);
    }

    public function branchActiveClient()
    {
        $clientData = Client::with(['plan'])
            ->where('resellerId', Auth::user()->resellerId)
            ->whereNotNull('branchId')
            ->where('status', 'On')
            ->orderBy(DB::raw('LENGTH(username) , username'))
            ->get();
        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Branch Active Client List',
            'main_url' => 'active.client',
            'branch_url' => 'client.branch.active',
        ]);
    }

    public function branchOldClient()
    {
        $clientData = Client::with(['plan'])
            ->where('resellerId', Auth::user()->resellerId)
            ->whereNotNull('branchId')
            ->where('server_status', '!=', 1)
            ->orderBy(DB::raw('LENGTH(username) , username'))
            ->get();

        return view("admin.pages.client.index", [
            'clientData' => $clientData,
            'page_title' => 'All Branch Old Client List',
            'main_url' => 'old.client',
            'branch_url' => 'client.branch.old',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        if ($user->resellerId != '' || $user->branchId != '') {
            $client = Client::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->find($id);
        } else {
            $client = Client::find($id);
        }
        if (empty($client)) {
            Session::flash('message', 'Client Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.index');
        } else {
            $complainData = Complain::where('client_id', $client->id)->orderBy('complain_date', 'DESC')->get();
            $invoices = Invoice::where('client_id', $client->id)->orderBy('created_at', 'DESC')->get();
            $receipts = ClientPayment::where('client_id', $client->id)->orderBy('payment_date', 'DESC')->get();
            $paydetails = ClientPayment::where('client_id', $client->id)->where('new_paid', '>', 0)
                ->select(DB::raw('DATE(payment_date) as date'))
                ->groupBy('date')->get();

            $lsPay = ClientPayment::where('client_id', $client->id)->where('new_paid', '>', 0)->select('payment_date', 'created_at', 'new_paid')->orderBy('payment_date', 'DESC')->first();

            if (Auth::user()->resellerId == '' && $client->resellerId == null) {
                $planDatas = \App\Server::find($client->plan->server_id)->plans->where('type', $client->plan->type)
                    ->where('branchId', Auth::user()->branchId)
                    ->where('resellerId', Auth::user()->resellerId);
            } else {
                $planDatas = \App\Plan::whereHas('resellerPlan', function ($query) use ($client) {
                    $query->whereHas('reseller', function ($s_query) use ($client) {
                        $s_query->where('resellerId', $client->resellerId);
                    });
                })->get();
            }

            $resellers = Reseller::all();
            $config = Config::pluck('value', 'config_title');

            $currentDate = now()->format('d/m/Y'); // Formats to 'DD/MM/YYYY'

            return view(
                'admin.pages.client.show',
                compact(
                    'config',
                    'currentDate',
                    'client',
                    'invoices',
                    'receipts',
                    'paydetails',
                    'lsPay',
                    'planDatas',
                    'complainData',
                    'resellers'
                )
            );
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $auth_user = Auth::user();

        if ($auth_user->resellerId != '') {
            $prefix = Reseller::find($auth_user->resellerId)->prefix;
        } else {
            $prefix = Config::where('config_title', 'prefix')->first()->value;
        }

        $distributions = Distribution::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        $branches = Branch::all();
        $reseller_has_extra_charge = Reseller::where('resellerId', Auth::user()->resellerId)
            ->value('extra_charge');  // fetch only extra_charge column

        return view('admin.pages.client.create', compact('distributions', 'branches', 'prefix', 'reseller_has_extra_charge'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //	    CHECK CLIENT LIMIT
        $client_count = Client::where('server_status', 1)->count();
        if ($client_count >= env('ALLOWED_CLIENTS')) {
            Session::flash('message', 'You have no limit to create new Client. Contact with upstream!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        if (Auth::user()->resellerId != '') {
            $prefix = Reseller::find(Auth::user()->resellerId)->prefix;
        } else {
            $prefix = Config::where('config_title', 'prefix')->first()->value;
        }


        $request->request->set('username', $prefix . $request->username);

        $deletedClientCheck = Client::withTrashed()->where('username', $request->username)->count();
        if ($deletedClientCheck > 0) {
            Session::flash('message', 'This client already have in Database! First delete from Deleted Client List!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }


        $this->validate($request, [
            'client_name' => 'required',
            'username' => ['required', Rule::unique('clients')->whereNull('deleted_at')],
            'password' => 'required|min:1',
            'address' => 'required',
            'type_of_connection' => 'required',
            'cable_type' => 'required',
            'type_of_connectivity' => 'required',
            'type' => 'required',
            'plan_id' => 'required',
            'server_password' => 'required_if:type,==,PPPOE|required_if:type,==,Hotspot|min:1|nullable',
            'client_ip' => 'required_if:type,==,IP|unique:clients|nullable',
            'olt_type' => 'required|in:Solitine,VSOL,BDCOM,DBC,HSGQ,CORELINK,WISEE,TBS',
            'discount' => 'required|numeric|min:0',
            'charge' => 'required|numeric|min:0',
            'otc_charge' => 'numeric|min:0',
            'client_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'other_documents.*' => 'nullable|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
        ]);


        $clientPhotoName = null;
        $otherDocs = [];

// Step 1: Validate file uploads
        $validator = \Validator::make($request->all(), [
            'client_photo' => 'nullable|image|max:2048', // Max 2MB (2048 KB)
            'other_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,gif,bmp,webp|max:5120', // Max 5MB each
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

// Step 2: Handle client photo upload
        if ($request->hasFile('client_photo')) {
            $photo = $request->file('client_photo');

            $clientPhotoName = md5($photo->getClientOriginalName() . time()) . '.' . $photo->getClientOriginalExtension();
            $photo->move(('assets/uploads/client_photos/'), $clientPhotoName);
        }

// Step 3: Handle other documents upload
        if ($request->hasFile('other_documents')) {
            foreach ($request->file('other_documents') as $file) {
                $fileName = md5($file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
                $file->move(('assets/uploads/client_documents/'), $fileName);
                $otherDocs[] = $fileName;
            }
        }


        $inputs = [
            'client_name' => $request->client_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'house_no' => $request->house_no,
            'road_no' => $request->road_no,
            'address' => $request->address,
            'thana' => $request->thana,
            'district' => $request->district,
            'clientNid' => $request->clientNid,
            'active' => 1,
            'branchId' => $request->branchId,
            'resellerId' => $request->resellerId,
            'type_of_connection' => $request->type_of_connection,
            'type_of_connectivity' => $request->type_of_connectivity,
            'plan_id' => $request->plan_id,
            'status' => 'Off',
            'cable_type' => $request->cable_type,
            'olt_type' => $request->olt_type,
            'server_status' => 1,
            'server_active_date' => date('Y-m-d'),
            'distribution_id' => $request->distribution,
            'discount' => $request->discount,
            'charge' => $request->charge,
            'otc_charge' => $request->otc_charge ?? 0.00,
            'expiration' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
            'client_photo' => $clientPhotoName,
            'other_documents' => json_encode($otherDocs),
        ];

        $planData = \App\Plan::with('server', 'bandwidth')->find($request->plan_id);

        if (Auth::user()->resellerId != '') {

            $reseller_plan = ResellerPlan::where([
                ["plan_id", $request->plan_id],
                ["resellerId", Auth::user()->resellerId]
            ])->first();
            $p_price = $reseller_plan->reseller_sell_price;

            if ($request->discount >= ($p_price + $request->charge)) {
                Session::flash('message', 'You can not give full discount');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
        if ($request->password != '' && $this->validate($request, ['password' => 'required|min:1'])) {
            $inputs['password'] = bcrypt($request->password);
        }
        if ($request->type == 'IP') {
            $inputs['client_ip'] = $request->client_ip;
            $inputs['server_password'] = '';
        } else {
            $inputs['client_ip'] = '';
            $inputs['server_password'] = $request->server_password;
        }

        if (setting('using_mikrotik')) {
            $mikrotik = new Mikrotik();
            if ($mikrotik->addClientToMikrotik($request, $planData)) {
                if ($client = Client::create($inputs)) {
                    //========NEW CLIENT SMS=======
                    $deelkoSMS = new DeelkoSMS();
                    $deelkoSMS->sendSMS($client, 'sms_new_client', null, null, $request->password);

                    Session::flash('message', 'Client Added Successful!');
                    Session::flash('m-class', 'alert-success');
                    return redirect()->route('client.index');
                } else {
                    Session::flash('message', 'Data Save Failed!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            } else {
                Session::flash('message', 'Something Went wrong! Please check mikrotik connection and corresponding pool/plan!');
                Session::flash('m-class', 'alert-warning');
                return redirect()->back();
            }
        } else {
            if ($client = Client::create($inputs)) {
                //========NEW CLIENT SMS=======
                $deelkoSMS = new DeelkoSMS();
                $deelkoSMS->sendSMS($client, 'sms_new_client', null, null, $request->password);

                Session::flash('message', 'Client Added Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('client.index');
            } else {
                Session::flash('message', 'Data Saving Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (Auth::user()->branchId != '') {
            $clientData = Client::where('branchId', Auth::user()->branchId)->find($id);
            $clientPlanData = \App\Plan::find($clientData->plan_id);
            $planData = \App\Plan::where('branchId', Auth::user()->branchId)->where('type', $clientPlanData->type)->select('id', 'plan_name')->get();
        } elseif (Auth::user()->resellerId != '') {
            $clientData = Client::where('resellerId', Auth::user()->resellerId)->find($id);
            $clientPlanData = \App\Plan::find($clientData->plan_id);
            $planData = \App\Plan::where('type', $clientPlanData->type)
                ->whereHas('resellerPlan', function ($query) {
                    $query->whereHas('reseller', function ($s_query) {
                        $s_query->where('resellerId', Auth::user()->resellerId);
                    });
                })->get();
        } else {
            $clientData = Client::find($id);
            $clientPlanData = \App\Plan::with('server')->find($clientData->plan_id);
            $planData = \App\Plan::where('type', $clientPlanData->type)->select('id', 'plan_name')->get();
        }

        if ($clientData != '') {
            $serverData = \App\Server::select('id', 'server_name')->get();
            $distributions = Distribution::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
            $branches = Branch::all();
            $reseller_has_extra_charge = Reseller::where('resellerId', Auth::user()->resellerId)
                ->value('extra_charge');  // fetch only extra_charge column


            $clientData->other_documents = $clientData->other_documents ? json_decode($clientData->other_documents, true) : [];

            return view('admin.pages.client.edit', compact('clientData', 'serverData', 'planData', 'clientPlanData', 'distributions', 'branches', 'reseller_has_extra_charge'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        //  Log::info('Request all files:', $request->allFiles());

        //	    CHECK CREDIT LIMIT
        $client_Data = Client::find($id);

        $this->validate($request, [
            'client_name' => 'required',
            'username' => ['required', Rule::unique('clients')->ignore($client_Data->id)->whereNull('deleted_at')],
            //            'username' => 'required|unique:clients,username,' . $client_Data->id . '',
            'address' => 'required',
            'type_of_connection' => 'required',
            'type_of_connectivity' => 'required',
            'cable_type' => 'required',
            //            'type' => 'required',
            //            'plan_id' => 'required',
            'server_password' => 'required_if:type,==,PPPOE|required_if:type,==,Hotspot|min:1|nullable',
            'client_ip' => 'required_if:type,==,IP|unique:clients,client_ip,' . $client_Data->id . '|nullable',
            'discount' => 'required|numeric|min:0',
            'charge' => 'required|numeric|min:0',
            'otc_charge' => 'numeric|min:0',
            'olt_type' => 'required|in:Solitine,VSOL,BDCOM,DBC,HSGQ,CORELINK,WISEE,TBS',
            'client_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'other_documents.*' => 'nullable|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120',
        ]);

        $update_data = [];

// Existing client photo name
        $clientPhotoName = $client_Data->client_photo;

// Handle client photo upload and removal
        if ($request->hasFile('client_photo')) {
            $photo = $request->file('client_photo');

            if (!$photo->isValid()) {
                throw new \Exception('Client photo upload failed: ' . $photo->getErrorMessage());
            }

            if ($photo->getSize() > 2 * 1024 * 1024) { // 2MB
                throw new \Exception('Client photo is too large. Max allowed is 2MB.');
            }

            // Delete old photo
            if ($clientPhotoName) {
                $oldPhotoPath = 'assets/uploads/client_photos/' . $clientPhotoName;
                if (File::exists($oldPhotoPath)) {
                    File::delete($oldPhotoPath);
                }
            }

            $path = 'assets/uploads/client_photos/';
            $clientPhotoName = md5($photo->getClientOriginalName() . time()) . '.' . $photo->getClientOriginalExtension();
            $photo->move($path, $clientPhotoName);

        } elseif ($request->has('remove_client_photo') && $request->remove_client_photo == '1') {
            if ($clientPhotoName) {
                $photoPath = 'assets/uploads/client_photos/' . $clientPhotoName;
                if (File::exists($photoPath)) {
                    File::delete($photoPath);
                }
            }
            $clientPhotoName = null;
        }

        $update_data['client_photo'] = $clientPhotoName;

// Handle other documents upload and removal
        $otherDocs = json_decode($client_Data->other_documents, true) ?? [];

        if ($request->has('remove_documents')) {
            foreach ($request->remove_documents as $index) {
                if (isset($otherDocs[$index])) {
                    $docPath = 'assets/uploads/client_documents/' . $otherDocs[$index];
                    if (File::exists($docPath)) {
                        File::delete($docPath);
                    }
                    unset($otherDocs[$index]);
                }
            }
            $otherDocs = array_values($otherDocs); // Reindex after deletion
        }

        if ($request->hasFile('other_documents')) {
            $path = 'assets/uploads/client_documents/';
            $files = $request->file('other_documents');

            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if (!$file->isValid()) {
                    throw new \Exception('One of the documents failed to upload: ' . $file->getErrorMessage());
                }

                if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
                    throw new \Exception('One of the documents is too large. Max allowed is 5MB.');
                }

                $fileName = md5($file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
                $file->move($path, $fileName);
                $otherDocs[] = $fileName;
            }
        }

        $update_data['other_documents'] = json_encode($otherDocs);


        if ($request->plan_id != '') {
            if (
                $request->plan_id != $client_Data->plan_id &&
                (Auth::user()->resellerId != '' || $client_Data->resellerId != '')
            ) {
                $reseller = Reseller::find($client_Data->resellerId);
                $new_client_credit = ResellerPlan::where('resellerId', $client_Data->resellerId)->where('plan_id', $request->plan_id)->value('sell_price');
                if ($reseller->balance < $new_client_credit) {
                    Session::flash('message', 'You do not have enough balance to change plan. Contact with upstream to recharge!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
            }
        }

        DB::beginTransaction();
        try {
            //CHECKING FULL DISCOUNT
            if (Auth::user()->resellerId != '') {
                $reseller_plan = ResellerPlan::where([
                    ["plan_id", $client_Data->plan_id],
                    ["resellerId", Auth::user()->resellerId]
                ])->first();
                $p_price = $reseller_plan->reseller_sell_price;

                if ($request->discount >= ($p_price + $request->charge)) {
                    throw new \Exception('You can not give full discount!');
                }
            }
            // check korbe ei client age jekono tk diye thake tahole ar parbe na, nahole ei tk prottek mashe change korte pare


            if ($request->otc_charge != ($client = \App\Client::findOrFail($id))->otc_charge && \App\ClientPayment::where('client_id', $client->id)->exists()) {
                throw new \Exception('You cannot change OTC Charge. It has already been paid!');
            }


            $update_client = ['otc_charge' => is_numeric($request->otc_charge) ? $request->otc_charge : 0];

            $update_client = [
                'client_name' => $request->client_name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'house_no' => $request->house_no,
                'road_no' => $request->road_no,
                'address' => $request->address,
                'thana' => $request->thana,
                'district' => $request->district,
                'cable_type' => $request->cable_type,
                'clientNid' => $request->clientNid,
                'branchId' => $request->branchId,
                'type_of_connection' => $request->type_of_connection,
                'type_of_connectivity' => $request->type_of_connectivity,
                'discount' => $request->discount,
                'charge' => $request->charge,
                'olt_type' => $request->olt_type,
                'otc_charge' => $request->otc_charge ?? 0,
                'distribution_id' => $request->distribution,
                'note' => $request->note,
                'client_photo' => $update_data['client_photo'],
                'other_documents' => $update_data['other_documents'],
            ];


            if ($request->password != '' && $this->validate($request, ['password' => 'required|min:1'])) {
                $update_client['password'] = bcrypt($request->password);
            }

            if ($request->type == 'IP') {
                $update_client['client_ip'] = $request->client_ip;
                $update_client['server_password'] = '';
            } else {
                $update_client['client_ip'] = '';
                $update_client['server_password'] = $request->server_password;
            }

            if ($request->plan_id != '') {
                $update_client['plan_id'] = $request->plan_id;
                $new_plan = Plan::with('server', 'bandwidth')->find($request->plan_id);
                $old_plan = Plan::with('server', 'bandwidth')->find($client_Data->plan_id);


                //CHECK IF PACKAGE IS CHANGED
                if ($client_Data->plan_id != $request->plan_id) {

                    //CHECKING IF PACKAGE IS CHANGED THIS MONTH BEFORE
                    if ($client_Data->plan_changed_at != '' && $client_Data->resellerId != null && Carbon::createFromDate($client_Data->plan_changed_at)->isCurrentMonth()) {
                        throw new \Exception('You can not change plan more than once a month!');
                    }
                    if (Auth::user()->resellerId != null) {
                        $update_client['plan_changed_at'] = date('Y-m-d');
                    }


                    //UPDATING CLIENT INVOICE AND RESELLER-ADMIN CALCULATION
                    $this_month_invoice = Invoice::where('bill_month', date('m'))
                        ->where('bill_year', date('Y'))
                        ->where('client_id', $id)->first();

                    //CHECKING IF INVOICE EXIST FOR CURRENT MONTH?
                    if (!empty($this_month_invoice)) {

                        //CHECK IF INVOICE HAS PAYMENT OR NOT
                        if ($this_month_invoice->paid_amount > 0) {

                            //DETERMINING OLD AND NEW PRICE
                            if ($client_Data->resellerId != null) {
                                $old_reseller_plan = ResellerPlan::where([
                                    ["plan_id", $client_Data->plan_id],
                                    ["resellerId", $client_Data->resellerId]
                                ])->first();
                                $new_reseller_plan = ResellerPlan::where([
                                    ["plan_id", $request->plan_id],
                                    ["resellerId", $client_Data->resellerId]
                                ])->first();

                                $old_buy_price = $old_reseller_plan->sell_price;
                                $new_buy_price = $new_reseller_plan->sell_price;
                                $old_plan_price = $old_reseller_plan->reseller_sell_price;
                                $new_plan_price = $new_reseller_plan->reseller_sell_price;
                                $_old_price = $old_buy_price;
                                $_new_price = $new_buy_price;
                            } else {
                                $old_buy_price = 0;
                                $new_buy_price = 0;
                                $old_plan_price = $old_plan->plan_price;
                                $new_plan_price = $new_plan->plan_price;
                                $_old_price = $old_plan_price;
                                $_new_price = $new_plan_price;
                            }

                            //CHECK IF PLAN UPGRADED/DOWNGRADED
                            if ($_new_price < $_old_price) { //DOWNGRADE
                                throw new \Exception('You can not downgrade plan when payment is completed!');
                            } else {

                                //UPGRADE
                                // ========== BUY PRICE CALCULATION ===========
                                $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
                                if ($invoice_system == 'fixed') {

                                    $now = Carbon::now();
                                    $daysInMonth = $now->daysInMonth;
                                    $days_used = $now->diffInDays(Carbon::now()->firstOfMonth());
                                    if (Carbon::createFromDate($client_Data->created_at)->isCurrentMonth())
                                        $days_used = $now->diffInDays(Carbon::createFromDate($client_Data->created_at));
                                    $days_remaining = $now->diffInDays(Carbon::now()->lastOfMonth());
                                    //Adjusting 1 days
                                    if ($days_used > 0) $days_remaining++;

                                    $used_buy_price = ($old_buy_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                    $used_plan_price = ($old_plan_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                    $remaining_buy_price = ($new_buy_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                                    $remaining_plan_price = ($new_plan_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                                } else {
                                    $expire_date = Carbon::createFromDate($client_Data->expiration);
                                    $previous_expire_date = Carbon::createFromDate($client_Data->expiration)->subMonth($old_plan->duration);
                                    $now = Carbon::now();

                                    $days_used = $previous_expire_date->diffInDays($now);
                                    $days_remaining = $expire_date->diffInDays($now);
                                    //Adjusting 1 days
                                    if ($days_used > 0) $days_remaining++;
                                    $daysInMonth = $expire_date->diffInDays($previous_expire_date);

                                    $used_buy_price = ($old_buy_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                    $used_plan_price = ($old_plan_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                    $remaining_buy_price = ($new_buy_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                                    $remaining_plan_price = ($new_plan_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                                }
                                //UPDATING CLIENT PROFILE DUE
                                $update_client['due'] = ($client_Data->due - $old_plan_price) + $used_plan_price + $remaining_plan_price;

                                $buy_price = $used_buy_price + $remaining_buy_price;
                                $plan_price = $used_plan_price + $remaining_plan_price;
                                $discount = $this_month_invoice->discount;
                                $charge = $this_month_invoice->charge;
                                $sub_total = $plan_price + $charge - $discount;

                                $note = "Previous: {$old_plan->plan_name}/{$days_used} days/BP-{$used_buy_price}/PP-{$used_plan_price} \n";
                                $note .= "Current: {$new_plan->plan_name}/{$days_remaining} days/BP-{$remaining_buy_price}/PP-{$remaining_plan_price}";

                                //UPDATING THIS MONTH INVOICE
                                $update_invoice = [
                                    'plan_id' => $request->plan_id,
                                    'bandwidth' => $new_plan->bandwidth->bandwidth_name,
                                    'note' => $note,
                                    'buy_price' => $buy_price,
                                    'plan_price' => $plan_price,
                                    'total' => $plan_price,
                                    'discount' => $discount,
                                    'all_total' => $sub_total,
                                    'sub_total' => $sub_total,
                                    'due' => $sub_total - $this_month_invoice->paid_amount,
                                    'duration' => $new_plan->duration,
                                    'duration_unit' => $new_plan->duration_unit
                                ];

                                $this_month_invoice->update($update_invoice);
                            }
                        } //

                        else { //IF INVOICE NOT PAID

                            $p_price = $new_plan->plan_price;

                            if ($client_Data->resellerId != null) {
                                $reseller_plan = ResellerPlan::where([
                                    ["plan_id", $request->plan_id],
                                    ["resellerId", $client_Data->resellerId]
                                ])->first();
                                $p_price = $reseller_plan->reseller_sell_price;
                            }

                            $discount = $this_month_invoice->discount;
                            $charge = $this_month_invoice->service_charge;
                            $sub_total = $p_price + $charge - $discount;
                            $pre_due = $client_Data->due - $this_month_invoice->sub_total;
                            $due = $pre_due + $sub_total;

                            $update_client['due'] = $due;

                            if ($client_Data->resellerId != null) {
                                $reseller = Reseller::find($client_Data->resellerId);
                                $vatRate = $reseller->vat_rate;
                                $buy_price = $reseller_plan->sell_price;

                                if ($buy_price <= 0) {
                                    throw new \Exception('Buy Price can not be zero(0)');
                                }
                            } else {
                                $vatData = Config::where('config_title', 'vatRate')->first();
                                $vatRate = $vatData->value;
                                $buy_price = 0;
                            }

                            $price = ceil((($sub_total) * 100) / (100 + $vatRate));
                            $vat = $sub_total - $price;
                            $plan_price = $p_price - $vat;

                            $update_invoice = [
                                'plan_id' => $request->plan_id,
                                'bandwidth' => $new_plan->bandwidth->bandwidth_name,
                                'buy_price' => $buy_price,
                                'plan_price' => $plan_price,
                                'service_charge' => $charge,
                                'total' => $plan_price + $this_month_invoice->service_charge,
                                'discount' => $discount,
                                'all_total' => $sub_total - $vat,
                                'vat' => $vat,
                                'sub_total' => $sub_total,
                                'due' => $sub_total,
                                'duration' => $new_plan->duration,
                                'duration_unit' => $new_plan->duration_unit
                            ];

                            $this_month_invoice->update($update_invoice);
                        }
                    }
                }

                //UPDATE PACKAGE IN MIKROTIK
                if (setting('using_mikrotik')) {

                    if (
                        $client_Data->username != $request->username || $client_Data->client_name != $request->client_name ||
                        $client_Data->server_password != $request->server_password || $client_Data->client_ip != $request->client_ip ||
                        $client_Data->plan_id != $request->plan_id
                    ) {

                        if ($new_plan->server->id != $old_plan->server->id) {
                            $oldServerConnect = Roar::connect($old_plan->server->server_ip, $old_plan->server->server_port, $old_plan->server->username, encrypt_decrypt('decrypt', $old_plan->server->password));
                            $newServerConncet = Roar::connect($new_plan->server->server_ip, $new_plan->server->server_port, $new_plan->server->username, encrypt_decrypt('decrypt', $new_plan->server->password));

                            if ($oldServerConnect->isConnected() && $newServerConncet->isConnected()) {
                                $mikrotik = new Mikrotik();
                                //Delete Data From Old Server
                                $deleted = $mikrotik->removeClientFromMikrotik($client_Data, $old_plan);
                                //Add Data To New Server
                                $activate = $client_Data->status == 'On' ? true : false;
                                $added = $mikrotik->addClientToMikrotik($client_Data, $new_plan, $activate);

                                if (!$deleted || !$added) {
                                    throw new \Exception('Please check if mikrotik connection, plan, pool and username is alright');
                                }
                            } else {
                                throw new \Exception('Mikrotik Connection Failed');
                            }
                        } else {
                            if ($new_plan->type != $old_plan->type) {
                                $mikrotik = new Mikrotik();
                                //Delete Data From Old Server
                                $deleted = $mikrotik->removeClientFromMikrotik($client_Data, $old_plan);
                                //Add Data To New Server
                                $activate = $client_Data->status == 'On' ? true : false;
                                $added = $mikrotik->addClientToMikrotik($client_Data, $new_plan, $activate);

                                if (!$deleted || !$added) {
                                    throw new \Exception('Please check if mikrotik connection, plan, pool and username is alright');
                                }
                            } else {
                                $mikrotik = new Mikrotik();

                                $info = [
                                    'client_name' => $request->client_name,
                                    'username' => $request->username,
                                    'server_password' => $request->server_password,
                                    'client_ip' => $request->client_ip,
                                    'plan_name' => $new_plan->plan_name,
                                ];
                                //Update data to Server
                                $updated = $mikrotik->updateInfoToMikrotik($client_Data, $info);
                                if (!$updated) {
                                    throw new \Exception('Please check if mikrotik connection, plan, pool and username is alright');
                                }
                            }
                        }
                    }
                }
            }

            if ($client_Data->update($update_client)) {
                DB::commit();
                Session::flash('message', 'Data Updated Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('client.index');
            } else {
                throw new \Exception('Data Saving Failed!');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Session::flash('message', $e->getMessage());
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function clientActive(Request $request)
    {
        if ($request->method('post')) {
            DB::beginTransaction();
            try {
                $client_Data = Client::find($request->id);

                //CHECK CREDIT LIMIT
                if (Auth::user()->resellerId != '') {
                    $reseller = Reseller::find(Auth::user()->resellerId);
                    $new_client_credit = ResellerPlan::where('resellerId', Auth::user()->resellerId)->where('plan_id', $client_Data->plan_id)->value('sell_price');
                    if ($reseller->balance < $new_client_credit) {
                        throw new \Exception('You do not have enough balance to activate client. Contact with upstream to recharge!');
                    }
                }

                $update_client['status'] = 'On';
                if (date('Y-m-d H:i:s') > $client_Data->expiration) {
                    $update_client['expiration'] = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                }

                //CREATE INVOICE IF THIS MONTH INVOICE NOT EXIST
                $invoice = new Invoice();
                $inv_due = $invoice->createCurrentMonthInvoice($client_Data);
                if ($inv_due) {
                    $update_client['due'] = $client_Data->due + $inv_due;
                }

                if (setting('using_mikrotik')) {
                    $mikrotik = new Mikrotik();
                    if ($mikrotik->activateClientInMikrotik($client_Data)) {
                        if ($client_Data->update($update_client)) {
                            DB::commit();
                            Session::flash('message', 'Client Activated Successfully');
                            Session::flash('m-class', 'alert-success');
                            return response()->json();
                        } else {
                            throw new \Exception('Client Activation Failed!');
                        }
                    } else {
                        throw new \Exception('Something Went wrong! Please check mikrotik connection and cossmatch pool/plan!');
                    }
                } else {
                    if ($client_Data->update($update_client)) {
                        DB::commit();
                        Session::flash('message', 'Activated Successfully');
                        Session::flash('m-class', 'alert-success');
                        return response()->json();
                    } else {
                        throw new \Exception('Client Activation Failed!');
                    }
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                if ($inv_due) {
                    Invoice::where([
                        ['client_id', '=', $client_Data->id],
                        ['bill_month', '=', date('m')],
                        ['bill_year', '=', date('Y')]
                    ])->first()->forceDelete();
                }
                Session::flash('message', $e->getMessage() ?? 'Connection lost or session expired');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'You Have Not Permission To view this page!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.index');
        }
    }

    public function clientInactive(Request $request)
    {
        if ($request->method('post')) {
            $client_Data = Client::find($request->id);
            $inputs['status'] = 'Off';
            if (!empty($client_Data)) {

                if (setting('using_mikrotik')) {
                    $mikrotik = new Mikrotik();
                    if ($mikrotik->deactivateClientInMikrotik($client_Data)) {
                        if ($client_Data->update($inputs)) {
                            Session::flash('message', 'Inactivate Successful!');
                            Session::flash('m-class', 'alert-success');
                            return response()->json();
                        } else {
                            Session::flash('message', 'Failed Inactive This!');
                            Session::flash('m-class', 'alert-danger');
                            return response()->json();
                        }
                    } else {
                        Session::flash('message', 'Failed Inactive This!');
                        Session::flash('m-class', 'alert-danger');
                        return response()->json();
                    }
                } else {
                    //IF NOT CONNECTED TO MIKROTIK
                    if ($client_Data->update($inputs)) {
                        Session::flash('message', 'Inactivate Successful!');
                        Session::flash('m-class', 'alert-success');
                        return response()->json();
                    } else {
                        Session::flash('message', 'Failed Inactive This!');
                        Session::flash('m-class', 'alert-danger');
                        return response()->json();
                    }
                }
            } else {
                Session::flash('message', 'Data Not Found!');
                Session::flash('m-class', 'alert-danger');
                return response()->json();
            }
        } else {
            Session::flash('message', 'You Have Not Permission To view this page!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.index');
        }
    }

    public function clientAddServer(Request $request)
    {

        if (setting('using_mikrotik')) {
            if ($request->method('post')) {
                $client_Data = Client::find($request->id);

                //CHECK CREDIT LIMIT
                if (Auth::user()->resellerId != '') {
                    $reseller = Reseller::find(Auth::user()->resellerId);
                    $new_client_credit = ResellerPlan::where('resellerId', Auth::user()->resellerId)->where('plan_id', $client_Data->plan_id)->value('sell_price');
                    if ($reseller->balance < $new_client_credit) {
                        Session::flash('message', 'You do not have enough balance to activate client. Contact with upstream to recharge!');
                        Session::flash('m-class', 'alert-danger');
                        return response()->json();
                    }
                }

                $inputs['status'] = 'Off';
                $inputs['server_status'] = 1;
                $inputs['server_active_date'] = date('Y-m-d');
                if (!empty($client_Data)) {
                    $mikrotik = new Mikrotik();
                    if ($mikrotik->addClientToMikrotik($client_Data, $client_Data->plan)) {
                        if ($client_Data->update($inputs)) {
                            Session::flash('message', 'Add to Server Successful!');
                            Session::flash('m-class', 'alert-success');
                            return response()->json();
                        } else {
                            Session::flash('message', 'Failed to Update Client Data!');
                            Session::flash('m-class', 'alert-danger');
                            return response()->json();
                        }
                    } else {
                        Session::flash('message', 'Something went wrong! Please check mikrotik connection!');
                        Session::flash('m-class', 'alert-warning');
                        return response()->json();
                    }
                } else {
                    Session::flash('message', 'Data Not Found!');
                    Session::flash('m-class', 'alert-danger');
                    return response()->json();
                }
            } else {
                Session::flash('message', 'You Have Not Permission To view this page!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('client.index');
            }
        } else {
            Session::flash('message', 'You are not using mikrotik!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function clientDelServer(Request $request)
    {
        if (setting('using_mikrotik')) {
            if ($request->method('post')) {
                $client_Data = Client::find($request->id);
                $inputs['status'] = 'Off';
                $inputs['server_status'] = 2;
                $inputs['server_inactive_date'] = date('Y-m-d');
                if (!empty($client_Data)) {

                    $mikrotik = new Mikrotik();
                    if ($mikrotik->removeClientFromMikrotik($client_Data, $client_Data->plan)) {
                        if ($client_Data->update($inputs)) {
                            Session::flash('message', 'Remove From Server Successful!');
                            Session::flash('m-class', 'alert-success');
                            return response()->json();
                        } else {
                            Session::flash('message', 'Failed to Update Client Data!');
                            Session::flash('m-class', 'alert-danger');
                            return response()->json();
                        }
                    } else {
                        Session::flash('message', 'Something went wrong! Please check mikrotik connection!');
                        Session::flash('m-class', 'alert-warning');
                        return response()->json();
                    }
                } else {
                    Session::flash('message', 'Data Not Found!');
                    Session::flash('m-class', 'alert-danger');
                    return response()->json();
                }
            } else {
                Session::flash('message', 'You Have Not Permission To view this page!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('client.index');
            }
        } else {
            Session::flash('message', 'You are not using mikrotik!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function clientBulkActive(Request $request)
    {
        if ($request->method('post')) {
            $_success = [];
            $_errors = [];
            DB::beginTransaction();
            try {
                foreach ($request->clientID as $c_id) {
                    $client_Data = Client::where('server_status', 1)->where('status', 'Off')->find($c_id);

                    if (!empty($client_Data)) {
                        //CHECK CREDIT LIMIT/BALANCE
                        if (Auth::user()->resellerId != '') {
                            $reseller = Reseller::find(Auth::user()->resellerId);
                            $new_client_credit = ResellerPlan::where('resellerId', Auth::user()->resellerId)->where('plan_id', $client_Data->plan_id)->value('sell_price');
                            if ($reseller->balance < $new_client_credit) {
                                throw new \Exception('You do not have enough balance to activate client. Contact with upstream to recharge!');
                            }
                        }

                        $update_client['status'] = 'On';
                        if (date('Y-m-d H:i:s') > $client_Data->expiration) {
                            $update_client['expiration'] = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                        }

                        //CREATE INVOICE IF THIS MONTH INVOICE NOT EXIST
                        $invoice = new Invoice();
                        $inv_due = $invoice->createCurrentMonthInvoice($client_Data);
                        if ($inv_due) {
                            $update_client['due'] = $client_Data->due + $inv_due;
                        }

                        if (setting('using_mikrotik')) {
                            $mikrotik = new Mikrotik();
                            if ($mikrotik->activateClientInMikrotik($client_Data)) {
                                if (!empty($update_client)) {
                                    $client_Data->update($update_client);
                                    $_success[] = $client_Data->username;
                                }
                            } else {
                                $_errors[] = $client_Data->id;
                            }
                        } else {
                            if (!empty($update_client)) {
                                $client_Data->update($update_client);
                                $_success[] = $client_Data->username;
                            }
                        }
                    }
                }

                if (!empty($_success)) {
                    DB::commit();
                    Session::flash('message', implode(", ", $_success) . ' Activated Successfully!');
                    Session::flash('m-class', 'alert-success');
                    return redirect()->back();
                } else {
                    throw new \Exception('Something went wrong!');
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                foreach ($_errors as $error_client_id) {
                    Invoice::where([
                        ['client_id', '=', $error_client_id],
                        ['bill_month', '=', date('m')],
                        ['bill_year', '=', date('Y')]
                    ])->first()->forceDelete();
                }
                Session::flash('message', $e->getMessage() ?? 'Connection lost or session out');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'You do not have permission to view this page!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function clientBulkInactive(Request $request)
    {
        if ($request->method('post')) {
            $_success = [];
            $inputs['status'] = 'Off';
            foreach ($request->clientID as $c_id) {
                $client_Data = Client::find($c_id);

                if (!empty($client_Data) && $client_Data->status == 'On') {

                    if (setting('using_mikrotik')) {
                        $mikrotik = new Mikrotik();
                        if ($mikrotik->deactivateClientInMikrotik($client_Data)) {
                            $client_Data->update($inputs);
                            $_success[] = $client_Data->username;
                        }
                    } else {
                        $client_Data->update($inputs);
                        $_success[] = $client_Data->username;
                    }
                }
            }

            if (!empty($_success)) {
                Session::flash('message', implode(", ", $_success) . ' Deactivated Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('m-class', 'alert-warning');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'You Have Not Permission To view this page!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function clientBulkAddServer(Request $request)
    {
        if ($request->method('post')) {

            $inputs['status'] = 'Off';
            $inputs['server_status'] = 1;
            $inputs['server_active_date'] = date('Y-m-d');
            $_success = [];

            foreach ($request->clientID as $c_id) {
                $client_Data = Client::find($c_id);

                //CHECK CREDIT LIMIT
                if (Auth::user()->resellerId != '') {
                    $reseller = Reseller::find(Auth::user()->resellerId);
                    $new_client_credit = ResellerPlan::where('resellerId', Auth::user()->resellerId)->where('plan_id', $client_Data->plan_id)->value('sell_price');
                    if ($reseller->balance < $new_client_credit) {
                        Session::flash('message', 'You do not have enough balance to activate client. Contact with upstream to recharge!');
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                }

                if (!empty($client_Data)) {

                    if (setting('using_mikrotik')) {
                        $mikrotik = new Mikrotik();
                        if ($mikrotik->addClientToMikrotik($client_Data, $client_Data->plan)) {
                            $client_Data->update($inputs);
                            $_success[] = $client_Data->username;
                        }
                    } else {
                        $client_Data->update($inputs);
                        $_success[] = $client_Data->username;
                    }
                }
            }

            if (!empty($_success)) {
                Session::flash('message', implode(", ", $_success) . ' Added to Server Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('m-class', 'alert-warning');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'You Have Not Permission To view this page!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function clientBulkDelServer(Request $request)
    {
        if ($request->method('post')) {
            $inputs['status'] = 'Off';
            $inputs['server_status'] = 2;
            $inputs['server_inactive_date'] = date('Y-m-d');

            $_success = [];
            foreach ($request->clientID as $c_id) {
                $client_Data = Client::find($c_id);
                if (setting('using_mikrotik')) {
                    if (!empty($client_Data) && $client_Data->server_status == '1') {
                        $mikrotik = new Mikrotik();
                        if ($mikrotik->removeClientFromMikrotik($client_Data, $client_Data->plan)) {
                            $client_Data->update($inputs);
                            $_success[] = $client_Data->username;
                        }
                    }
                } else {
                    $client_Data->update($inputs);
                    $_success[] = $client_Data->username;
                }
            }

            if (!empty($_success)) {
                Session::flash('message', implode(", ", $_success) . ' Deleted from server successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('m-class', 'alert-warning');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'You Have Not Permission To view this page!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function clientListOfBtrc()
    {
        $clientData = Client::where('server_status', 1)
            ->where('status', 'On')->orderBy('server_active_date', 'ASC')
            ->get();

        $resellers = Reseller::get();
        $servers = Server::where('status', 1)->get();
//        $users = [];
//        foreach ($servers as $server) {
//            $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));
//            if ($con->isConnected()) {
//                $active_users = new Active($con);
//                $x = $active_users->getAll();
//                if (is_array($x)) {
//                    $users = array_merge($users, $x);
//                }
//            }
//        }

        return view("admin.pages.client.list_btrc", [
            'clientData' => $clientData,
//            'users' => $users,
            'resellers' => $resellers,
            'page_title' => 'Client List For BTRC',
            'route_url' => 'pppoe.client.connected',
        ]);
    }

//    public function pppoeClientConnected()
//    {
//
//        $resellers = Reseller::get();
//        $servers = Server::where('status', 1)->get();
//        $users = [];
//        foreach ($servers as $server) {
//            $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));
//
//            if ($con->isConnected()) {
//                $active_users = new Active($con);
//
//                $x = $active_users->getAll();
//                // dd($x);
//                if (is_array($x)) {
//                    $users = array_merge($users, $x);
//                }
//            }
//        }
//
//        return view("admin.pages.client.pppoe_connected", [
//            'clientData' => $users,
//            'page_title' => 'Local Connected PPPoE Client',
//            'resellers' => $resellers,
//            'route_url' => 'pppoe.client.connected',
//        ]);
//    }
//

    public function pppoeClientConnected()
    {
        $resellers = Reseller::all();

        return view("admin.pages.client.pppoe_connected", [
            'clientData' => [], // Empty initially, AJAX will populate
            'page_title' => request('resellerId') != '' ? 'Reseller Connected PPPoE Client' : 'Local Connected PPPoE Client',
            'resellers' => $resellers,
            'route_url' => 'pppoe.client.connected',
        ]);
    }



    public function pppoeClientConnectedData(Request $request)
    {
        $users = [];
        $connected = false;
        $errorMessage = 'No active server found or connection failed.';

        $usingMikrotik = \DB::table('configs')->where('config_title', 'using_mikrotik')->value('value');
     //   Log::info("pppoeClientConnectedData called", ['using_mikrotik' => $usingMikrotik]);

        if ($usingMikrotik == 1) {
            $servers = Server::where('status', 1)->get();
      //      Log::info("Fetching all active servers", ['servers' => $servers->pluck('server_ip')]);

            foreach ($servers as $server) {
                try {
                    $con = Roar::connect(
                        $server->server_ip,
                        $server->server_port,
                        $server->username,
                        encrypt_decrypt('decrypt', $server->password)
                    );

                    if ($con->isConnected()) {
                        $connected = true;
                        $active_users = new Active($con);
                        $serverUsers = $active_users->getAll();

                        if (is_array($serverUsers)) {
                            $users = array_merge($users, $serverUsers);
                        }
                    }
                } catch (\Exception $e) {
//                    Log::error('Exception connecting to MikroTik server', [
//                        'server_ip' => $server->server_ip,
//                        'error'     => $e->getMessage()
//                    ]);
                    continue;
                }
            }
        }

        if (!$connected) {
            return response()->json([
                'status'     => 'error',
                'message'    => $errorMessage,
                'clientData' => []
            ]);
        }

        // Convert active users into lookup by username
        $activeLookup = [];
        foreach ($users as $u) {
            if (isset($u['name'])) {
                $activeLookup[$u['name']] = $u;
            }
        }

        // get all clients (filtered by reseller if needed)
        $clientsQuery = Client::with('reseller:resellerId,resellerName');
       // Log::info($clientsQuery);

        if (Auth::check() && Auth::user()->resellerId) {
            $clientsQuery->where('resellerId', Auth::user()->resellerId);
        } elseif ($request->resellerId == null || $request->resellerId == '') {
            $clientsQuery->whereNull('resellerId');
        } elseif ($request->resellerId != 'all') {
            $clientsQuery->where('resellerId', $request->resellerId);
        }
        elseif ($request->resellerId == 'all') {
            $clientsQuery->whereNotNull('resellerId');
        }


            $clients = $clientsQuery->get();
        $matchedClients = [];

        foreach ($clients as $dbClient) {
            $username     = $dbClient->username;
            $resellerName = optional($dbClient->reseller)->resellerName ?? 'Local Client';

            if (isset($activeLookup[$username])) {
                // Connected
                $mikrotikUser = $activeLookup[$username];
                $matchedClients[] = [
                    'id'           => $dbClient->id,
                    'client_name'  => $dbClient->client_name,
                    'name'         => $dbClient->username,
                    'reseller_name'=> $resellerName,
                    'caller-id'    => $mikrotikUser['caller-id'] ?? '',
                    'address'      => $mikrotikUser['address'] ?? '',
                    'uptime'       => $mikrotikUser['uptime'] ?? ''
                ];
            } else {
                // Disconnected
                if ($dbClient->server_status != 2) {
                    $matchedClients[] = [
                        'id'           => $dbClient->id,
                        'client_name'  => $dbClient->client_name,
                        'name'         => $dbClient->username,
                        'reseller_name'=> $resellerName,
                        'caller-id'    => '',
                        'address'      => '',
                        'uptime'       => 'router_off'
                    ];
                }
            }
        }

        usort($matchedClients, fn($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json([
            'status'     => 'success',
            'clientData' => $matchedClients,
            'total'      => count($matchedClients)
        ]);
    }





    public function pppoeClientConnectedDel(Request $request)
    {
        $client_Data = Client::find($request->id);
        $con = Roar::connect($client_Data->plan->server->server_ip, $client_Data->plan->server->server_port, $client_Data->plan->server->username, encrypt_decrypt('decrypt', $client_Data->plan->server->password));
        if ($con->isConnected()) {
            $active_users = new Active($con);
            $active_id = $active_users->getId($client_Data->username);
            $active_users->delete($active_id);
        } else {
            Session::flash('message', 'Not Connected!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.index');
        }


        return response()->json();
    }

    public function pppoeClientConnectedFresh()
    {
        $server = Server::find(1);
        $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));
        if ($con->isConnected()) {
            $active_users = new Active($con);
            $users = $active_users->getAll();
        } else {
            echo 'not connect';
            exit;
        }

        return $users;
    }

    public function hotspotClientConnected()
    {
        $resellers = Reseller::get();
        $servers = Server::where('status', 1)->get();
        $users = [];
        foreach ($servers as $server) {
            $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));
            if ($con->isConnected()) {
                $active_users = new HotspotActive($con);
                $x = $active_users->getAll();
                if (is_array($x)) {
                    $users = array_merge($users, $x);
                }
            }
        }
        return view("admin.pages.client.pppoe_connected", [
            'clientData' => $users,
            'page_title' => 'Local Connected Hotspot Client',
            'resellers' => $resellers,
            'route_url' => 'hotspot.client.connected',
        ]);
    }

    public function IPClientConnected()
    {
        $resellers = Reseller::get();
        $servers = Server::where('status', 1)->get();
        $users = [];
        foreach ($servers as $server) {
            $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));
            if ($con->isConnected()) {
                $active_users = new FirewallAddressList($con);
                $x = $active_users->getAll();
                if (is_array($x)) {
                    $users = array_merge($users, $x);
                }
            }
        }
        return view("admin.pages.client.pppoe_connected", [
            'clientData' => $users,
            'page_title' => 'Local Connected Hotspot Client',
            'resellers' => $resellers,
            'route_url' => 'ip.client.connected',
        ]);
    }

    public function clientAddFromExcel(Request $request)
    {
        $this->validate($request, [
            'import_file' => 'required'
        ]);

        $path = $request->file('import_file')->getRealPath();
        $data = Excel::load($path)->get();
        $client_data = array();
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                if (
                    $value->client_name == "" or
                    $value->username == "" or
                    $value->email == "" or
                    $value->password == "" or
                    $value->phone == "" or
                    $value->address == "" or
                    $value->thana == "" or
                    $value->district == "" or
                    $value->plan_name == "" or
                    $value->server_active_date == "" or
                    $value->exp_date == "" or
                    $value->distribution == "" or
                    $value->type_of_connection == "" or
                    $value->type_of_connectivity == ""
                ) {
                    Session::flash('message', "Few Data Empty Please Check Your File" . $value->client_name);
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                if ($value->type_of_connection != 'Wired' && $value->type_of_connection != 'Wireless') {
                    Session::flash('message', "type_of_connection Value Must Wired Or Wireless");
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                if ($value->type_of_connectivity != 'Shared' && $value->type_of_connectivity != 'Dedicated') {
                    Session::flash('message', "type_of_connectivity Value Must Shared Or Dedicated");
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                ($value->branch != '') ? $branch = Branch::where('branchName', $value->branch)->first()->branchId
                    : $branch = null;
                ($value->reseller != '') ? $reseller = Reseller::where('resellerName', $value->reseller)->first()->resellerId
                    : $reseller = null;
                ($value->due != '') ? $due = $value->due : $due = 0;
                ($value->balance != '') ? $balance = $value->balance : $balance = 0;
                ($value->discount != '') ? $discount = $value->discount : $discount = 0;
                $plan = Plan::where('plan_name', $value->plan_name)->first();

                $area = Distribution::where('distribution', $value->distribution)->first();
                if (empty($plan)) {
                    Session::flash('message', "Plan Name Not Found in Database");
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                if (empty($area)) {
                    Session::flash('message', "Distribution Area Not Found in Database");
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                if ($plan->type == 'IP') {
                    if ($value->client_ip == "") {
                        Session::flash('message', "Must need client_ip data when Client Type IP");
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                } else {
                    if ($value->server_password == "") {
                        Session::flash('message', "Must need server_password data when Client Type PPPoE Or Hotspot");
                        Session::flash('m-class', 'alert-danger');
                        return redirect()->back();
                    }
                }
                $client_username = Client::where('username', $value->username)->first();
                $client_email = Client::where('email', $value->email)->first();
                $originalDate = $value->server_active_date;
                $newDate = date("Y-m-d", strtotime($originalDate));
                $expDate = date("Y-m-d", strtotime($value->exp_date));
                if (!empty($client_username)) {
                    Session::flash('message', "UserName '" . $value->username . "' Already Exit");
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }
                if (!empty($client_email)) {
                    Session::flash('message', "Email '" . $value->email . "' Already Exit");
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->back();
                }

                $lenght = strlen((string)$value->phone);
                if ($lenght > 11) {
                    $mobileNumber = substr_replace($value->phone, "0", 0, 3) . "";
                } else if ($lenght == 11) {
                    $mobileNumber = substr_replace($value->phone, "0", 0, 1) . "";
                } else {
                    $mobileNumber = substr_replace($value->phone, "0", 0, 0) . "";
                }
                $client_data[] = [
                    'client_name' => $value->client_name,
                    'username' => $value->username,
                    'email' => $value->email,
                    'password' => bcrypt($value->password),
                    'server_password' => $value->server_password,
                    'phone' => $mobileNumber,
                    'house_no' => $value->house_no,
                    'road_no' => $value->road_no,
                    'address' => $value->address,
                    'thana' => $value->thana,
                    'district' => $value->district,
                    'active' => 1,
                    'plan_id' => $plan->id,
                    'client_ip' => $value->client_ip,
                    'server_active_date' => $newDate,
                    'expiration' => $expDate,
                    'server_status' => 1,
                    'status' => 'On',
                    'distribution_id' => $area->id,
                    'type_of_connection' => $value->type_of_connection,
                    'type_of_connectivity' => $value->type_of_connectivity,
                    'due' => $due,
                    'balance' => $balance,
                    'discount' => $discount,
                    'branchId' => $branch,
                    'resellerId' => $branch,
                ];
            }
        } else {
            Session::flash('message', 'Empty Excel Data');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }


        if (count($client_data) > 0) {
            return $this->insertExelData($client_data);
        } else {
            Session::flash('message', 'Empty Excel Data!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function insertExelData($data = array())
    {
        if (count($data) > 0) {
            foreach ($data as $reselt) {
                $plan = Plan::find($reselt['plan_id']);
                $server = Server::find($plan->server_id);
                $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));
                if ($con->isConnected()) {
                    if ($plan['type'] == 'PPPOE') {
                        $clientData = [
                            'name' => $reselt['username'],
                            'service' => 'pppoe',
                            'profile' => $plan->plan_name,
                            'password' => $reselt['server_password']
                        ];

                        $addToServer = new Secret($con);
                        $addToServer->add($clientData);
                        $new_id = $addToServer->getId($reselt['username']);
                    } elseif ($plan['type'] == 'Hotspot') {
                        $clientData = [
                            'name' => $reselt['username'],
                            'profile' => $plan->plan_name,
                            'password' => $reselt['server_password']
                        ];
                        $addToServer = new HotspotUsers($con);
                        $addToServer->add($clientData);
                        $new_id = $addToServer->getId($reselt['username']);
                    } else {
                        $b = Bandwidth::find($plan['bandwidth_id']);
                        ($b->rate_down_unit == 'Kbps') ? $unitdown = 'K' : $unitdown = 'M';
                        ($b->rate_up_unit == 'Kbps') ? $unitup = 'K' : $unitup = 'M';
                        $rate = $b->rate_up . $unitup . "/" . $b->rate_down . $unitdown;
                        $clientData = [
                            'name' => $reselt['client_name'] . " (" . $reselt['username'] . ")",
                            'target' => $reselt['client_ip'],
                            'max-limit' => $rate
                        ];
                        $addToServer = new Simple($con);
                        $addToServer->add($clientData);
                        $new_id = $addToServer->getId($reselt['client_name'] . " (" . $reselt['username'] . ")");
                        $firewall_address = new FirewallAddressList($con);
                        $fire_ip_data = [
                            'address' => $reselt['client_ip'],
                            'list' => 'Redirect IP'
                        ];
                        $firewall_address->add($fire_ip_data);
                    }

                    if (!empty($new_id)) {
                        Client::create($reselt);
                    }
                }
            }
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('client.index');
        } else {
            return false;
        }
    }

    public function serverProfileUpdate(Request $request, $id)
    {
        $client_Data = Client::find($id);
        $old_plan = Plan::with('server', 'bandwidth')->find($client_Data->plan_id);
        $new_plan = Plan::with('server', 'bandwidth')->find($request->plan_id);

        $update_client['plan_id'] = $request->plan_id;

        // Disabled this feature (Only reseller can chenge plane once before pay the bill)
        // if (Auth::user()->resellerId != null)
        //     $update_client['plan_changed_at'] = date('Y-m-d');

        DB::beginTransaction();
        try {
            //CHECK IF PACKAGE IS CHANGED
            if ($client_Data->plan_id == $request->plan_id) {
                throw new \Exception('You select the existing plan!');
            }

            //CHECKING IF PACKAGE IS CHANGED THIS MONTH BEFORE
            if (
                $client_Data->plan_changed_at != ''
                && Carbon::createFromDate($client_Data->plan_changed_at)->isCurrentMonth()
            ) {
                throw new \Exception('You can not change plan more than once a month!');
            }

            //UPDATING CLIENT INVOICE AND RESELLER-ADMIN CALCULATION
            $this_month_invoice = Invoice::where('bill_month', date('m'))
                ->where('bill_year', date('Y'))
                ->where('client_id', $id)->first();

            //CHECKING IF INVOICE EXIST FOR CURRENT MONTH?
            if (!empty($this_month_invoice)) {
                $update_invoice = false;
                //DETERMINING OLD AND NEW PRICE
                if ($client_Data->resellerId != null) {
                    $old_reseller_plan = ResellerPlan::where([
                        ["plan_id", $client_Data->plan_id],
                        ["resellerId", $client_Data->resellerId]
                    ])->first();
                    $new_reseller_plan = ResellerPlan::where([
                        ["plan_id", $request->plan_id],
                        ["resellerId", $client_Data->resellerId]
                    ])->first();

                    $old_buy_price = $old_reseller_plan->sell_price;
                    $new_buy_price = $new_reseller_plan->sell_price;
                    $old_plan_price = $old_reseller_plan->reseller_sell_price;
                    $new_plan_price = $new_reseller_plan->reseller_sell_price;
                    $_old_price = $old_buy_price;
                    $_new_price = $new_buy_price;
                    if (($old_plan_price != $new_plan_price) || ($old_buy_price != $new_buy_price)) {
                        $update_invoice = true;
                    }
                } else {
                    $old_buy_price = 0;
                    $new_buy_price = 0;
                    $old_plan_price = $old_plan->plan_price;
                    $new_plan_price = $new_plan->plan_price;
                    $_old_price = $old_plan_price;
                    $_new_price = $new_plan_price;
                    if ($old_plan_price != $new_plan_price) {
                        $update_invoice = true;
                    }
                }

                if ($update_invoice) {
                    //CHECK IF INVOICE HAS PAYMENT OR NOT
                    if ($this_month_invoice->paid_amount > 0) {
                        $update_client['plan_changed_at'] = date('Y-m-d');

                        //CHECK IF PLAN UPGRADED/DOWNGRADED
                        if ($_new_price < $_old_price) { //DOWNGRADE
                            throw new \Exception('You can not downgrade plan when payment is completed!');
                        } else {

                            //UPGRADE
                            // ========== BUY PRICE CALCULATION ===========
                            $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
                            if ($invoice_system == 'fixed') {

                                $now = Carbon::now();
                                $daysInMonth = $now->daysInMonth;
                                $days_used = $now->diffInDays(Carbon::now()->firstOfMonth());
                                if (Carbon::createFromDate($client_Data->created_at)->isCurrentMonth())
                                    $days_used = $now->diffInDays(Carbon::createFromDate($client_Data->created_at));
                                $days_remaining = $now->diffInDays(Carbon::now()->lastOfMonth());
                                //Adjusting 1 days
                                if ($days_used > 0) $days_remaining++;

                                $used_buy_price = ($old_buy_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                $used_plan_price = ($old_plan_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                $remaining_buy_price = ($new_buy_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                                $remaining_plan_price = ($new_plan_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                            } else {
                                $expire_date = Carbon::createFromDate($client_Data->expiration);
                                $previous_expire_date = Carbon::createFromDate($client_Data->expiration)->subMonth($old_plan->duration);
                                $now = Carbon::now();

                                $days_used = $previous_expire_date->diffInDays($now);
                                $days_remaining = $expire_date->diffInDays($now);
                                //Adjusting 1 days
                                if ($days_used > 0) $days_remaining++;
                                $daysInMonth = $expire_date->diffInDays($previous_expire_date);

                                $used_buy_price = ($old_buy_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                $used_plan_price = ($old_plan_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                $remaining_buy_price = ($new_buy_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                                $remaining_plan_price = ($new_plan_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                            }
                            //UPDATING CLIENT PROFILE DUE
                            $update_client['due'] = ($client_Data->due - $old_plan_price) + $used_plan_price + $remaining_plan_price;


                            //UPDATING RESELLER BALANCE
                            if ($client_Data->resellerId != null) {
                                $reseller = Reseller::find($client_Data->resellerId);
                                $current_reseller_balance = ($reseller->balance + $this_month_invoice->buy_price) - ($used_buy_price + $remaining_buy_price);
                                if ($current_reseller_balance < 0) {
                                    throw new \Exception('Insufficient balance to upgrade package. Please recharge!');
                                }
                                $reseller->update(['balance' => $current_reseller_balance]);
                            }

                            $buy_price = $used_buy_price + $remaining_buy_price;
                            $plan_price = $used_plan_price + $remaining_plan_price;
                            $discount = $this_month_invoice->discount;
                            $charge = $this_month_invoice->service_charge;
                            $sub_total = $plan_price + $charge - $discount;

                            $note = "Previous: {$old_plan->plan_name}/{$days_used} days/BP-{$used_buy_price}/PP-{$used_plan_price} \n";
                            $note .= "Current: {$new_plan->plan_name}/{$days_remaining} days/BP-{$remaining_buy_price}/PP-{$remaining_plan_price}";

                            //UPDATING THIS MONTH INVOICE
                            $update_invoice = [
                                'plan_id' => $request->plan_id,
                                'bandwidth' => $new_plan->bandwidth->bandwidth_name,
                                'note' => $note,
                                'buy_price' => $buy_price,
                                'plan_price' => $plan_price,
                                'total' => $plan_price + $this_month_invoice->service_charge,
                                'discount' => $discount,
                                'all_total' => $sub_total,
                                'sub_total' => $sub_total,
                                'due' => $sub_total - $this_month_invoice->paid_amount,
                                'duration' => $new_plan->duration,
                                'duration_unit' => $new_plan->duration_unit
                            ];

                            $this_month_invoice->update($update_invoice);
                        }
                    } //

                    else { //IF INVOICE NOT PAID

                        $p_price = $new_plan->plan_price;

                        if ($client_Data->resellerId != null) {
                            $reseller_plan = ResellerPlan::where([
                                ["plan_id", $request->plan_id],
                                ["resellerId", $client_Data->resellerId]
                            ])->first();
                            $p_price = $reseller_plan->reseller_sell_price;
                        }

                        $discount = $this_month_invoice->discount;
                        $charge = $this_month_invoice->service_charge;
                        $sub_total = $p_price + $charge - $discount;
                        $pre_due = $client_Data->due - $this_month_invoice->sub_total;
                        $due = $pre_due + $sub_total;

                        $update_client['due'] = $due;

                        if ($client_Data->resellerId != null) {
                            $reseller = Reseller::find($client_Data->resellerId);
                            $vatRate = $reseller->vat_rate;

                            $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
                            if ($invoice_system == 'fixed') {
                                $now = Carbon::now();
                                $daysInMonth = $now->daysInMonth;
                                $days_used = $now->diffInDays(Carbon::now()->firstOfMonth());
                                if (Carbon::createFromDate($client_Data->created_at)->isCurrentMonth())
                                    $days_used = $now->diffInDays(Carbon::createFromDate($client_Data->created_at));
                                $days_remaining = $now->diffInDays(Carbon::now()->lastOfMonth());
                                //Adjusting 1 days
                                if ($days_used > 0) $days_remaining++;

                                $used_buy_price = ($old_buy_price / ($daysInMonth * $old_plan->duration)) * $days_used;
                                $remaining_buy_price = ($new_buy_price / ($daysInMonth * $new_plan->duration)) * $days_remaining;
                                $buy_price = $used_buy_price + $remaining_buy_price;
                            } else {
                                $buy_price = $reseller_plan->sell_price;
                            }

                            if ($buy_price <= 0) {
                                throw new \Exception('Buy Price can not be zero(0)');
                            }
                        } else {
                            $vatData = Config::where('config_title', 'vatRate')->first();
                            $vatRate = $vatData->value;
                            $buy_price = 0;
                        }

                        $price = ceil((($sub_total) * 100) / (100 + $vatRate));
                        $vat = $sub_total - $price;
                        $plan_price = $p_price - $vat;

                        $update_invoice = [
                            'plan_id' => $request->plan_id,
                            'bandwidth' => $new_plan->bandwidth->bandwidth_name,
                            'buy_price' => $buy_price,
                            'plan_price' => $plan_price,
                            'service_charge' => $charge,
                            'total' => $plan_price + $this_month_invoice->service_charge,
                            'discount' => $discount,
                            'all_total' => $sub_total - $vat,
                            'vat' => $vat,
                            'sub_total' => $sub_total,
                            'due' => $sub_total,
                            'duration' => $new_plan->duration,
                            'duration_unit' => $new_plan->duration_unit
                        ];

                        $this_month_invoice->update($update_invoice);
                    }
                }
            }


            if ($client_Data->update($update_client)) {

                //UPDATE PACKAGE IN MIKROTIK
                if (setting('using_mikrotik')) {

                    $is_plan_changed = $this->planChangeToMikrotik($client_Data, $old_plan, $new_plan);
                    if (!$is_plan_changed) {
                        throw new \Exception('Please check if mikrotik connection, plan, pool and username is alright');
                    }
                }

                DB::commit();
                Session::flash('message', 'Plan Update Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('client.view', $id);
            } else {
                throw new \Exception('Plan Updating Failed!');
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Session::flash('message', $e->getMessage());
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function payDue($id)
    {
        $client = Client::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->findOrFail($id);

        if (Auth::user()->resellerId != '' && $client->due <= 0) {
            Session::flash('message', 'No due amount for this client!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

        //RETRIEVE ACCOUNT LIST
        if (Auth::user()->branchId != '') {
            $accounts = Account::where('branchId', Auth::user()->branchId)->orderBY('id', 'ASC')->get();
        } else {
            $accounts = Account::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();
        }
        //
        //            if($client->due > 0){
        //
        //                //DETERMINE EXPIRE DATE OF MONTH
        //                $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
        //                if (Auth::user()->resellerId != '') {
        //                    $reseller = Reseller::find(Auth::user()->resellerId);
        //                    if($invoice_system == 'fixed') {
        //                        $c_exp_date = $reseller->c_exp_date;
        //                    } else {
        //                        $isExpired = strtotime(now())-strtotime($client->expiration);
        //                        if($isExpired >= 0){
        //                            $c_exp_date = date('d');
        //                        }else{
        //                            $c_exp_date = date('d',strtotime($client->expiration));
        //                        }
        //                    }
        //                }
        //                else {
        //                    $c_exp_value = Config::where('config_title', 'exp_date')->first();
        //                    if($invoice_system == 'fixed') {
        //                        $c_exp_date = $c_exp_value->value;
        //                    } else {
        //                        $isExpired = strtotime(now())-strtotime($client->expiration);
        //                        if($isExpired >= 0){
        //                            $c_exp_date = date('d');
        //                        }else{
        //                            $c_exp_date = date('d',strtotime($client->expiration));
        //                        }
        //                    }
        //                }
        //
        //
        //                $singleInvoice = Invoice::where('client_id', $client->id)->where('due', '>', 0)->orderBy('bill_year', 'DESC')->orderBy('bill_month', 'DESC')->first();
        //
        //                $current_month = date('Y-m');
        //                $invoice_month_value = date_create($singleInvoice->bill_year . '-' . $singleInvoice->bill_month);
        //                $invoice_month = date_format($invoice_month_value, 'Y-m');
        //                $exp_time = setting('exp_time');
        //                if ($current_month >= $invoice_month) {
        //
        //                    if ($singleInvoice->duration_unit == '2') {
        //                        $date_exp = date("Y-m-d", mktime(date($exp_time), date("00"), date("00"), date("m"), date("d") + $singleInvoice->duration, date("Y")));
        //                    } else {
        //                        $date_exp = date("Y-m-d", mktime(date($exp_time), date("00"), date("00"), date("m") + $singleInvoice->duration, date($c_exp_date), date("Y")));
        //                    }
        //
        //                }
        //                else {
        //
        //                    if ($singleInvoice->duration_unit == '2') {
        //                        $date_exp = date("Y-m-d", mktime(date($exp_time), date("00"), date("00"), date($singleInvoice->bill_month), date("d") + $singleInvoice->duration, date($singleInvoice->bill_year)));
        //                    } else {
        //                        $date_exp = date("Y-m-d", mktime(date($exp_time), date("00"), date("00"), date($singleInvoice->bill_month) + $singleInvoice->duration, date($c_exp_date), date($singleInvoice->bill_year)));
        //                    }
        //                }
        //
        //            } else {
        //                $date_exp = date('Y-m-d', strtotime($client->expiration));
        //            }


        return view("admin.pages.client.pay_due", compact('client', 'accounts'));
    }

    public function payDueStore(Request $request, $id)
    {
        $client = Client::with([
            'plan' => function ($query) {
                $query->get();
            },
            'plan.server' => function ($query) {
                $query->get();
            }
        ])->find($id);

        $this->validate($request, [
            'due' => 'numeric',
            'paid_amount' => 'numeric|min:1',
            'paid_to' => 'required'
        ]);

        if (($request->paid_amount) == 0) {
            return redirect()->back()->withErrors([
                'paid_amount' => 'Payment amount must be greater than zero',
            ]);
        }

        $paid = $request->paid_amount;
        if ($client->due >= $request->paid_amount) {
            $ad_payment = 0.00;
            $due = $client->due - $request->paid_amount;
        } else {
            $ad_payment = $request->paid_amount - $client->due;
            $due = 0.00;
        }

        $new_balance = $client->balance + $ad_payment;

        $update_client = [
            'due' => $due,
            'balance' => $new_balance
        ];

        //GET LATEST DUE INVOICE
        $invoice = Invoice::where('client_id', $id)->orderBy('bill_year', 'DESC')->orderBy('bill_month', 'DESC')->where('due', '>', 0)->first();
        if (empty($invoice)) {
            $plan_price = 0.00;
            $service_charge = 0.00;
            $pre_due = $client->due;
            $vat = 0.00;
            $otc_charge = 0.00;
            $total = $pre_due;
            $discount = 0.00;

            $bandwidth = null;
            $bill_month = null;
            $bill_year = null;
            $invoice_id = null;
        } else {
            if ($invoice->paid_amount == 0) {
                $plan_price = $invoice->plan_price;
                $service_charge = $invoice->service_charge;
                $otc_charge = $invoice->otc_charge;
                $pre_due = $client->due - $invoice->due;
                $vat = $invoice->vat;
                $total = $invoice->total + $pre_due;
                $discount = $invoice->discount;
            } else {
                $plan_price = 0.00;
                $service_charge = 0.00;
                $otc_charge = 0.00;
                $pre_due = $client->due;
                $vat = 0.00;
                $total = $pre_due;
                $discount = 0.00;
            }

            $bandwidth = $invoice->bandwidth;
            $bill_month = $invoice->bill_month;
            $bill_year = $invoice->bill_year;
            $invoice_id = $invoice->id;
        }

        $sub_total = ($total + $ad_payment) - $discount;

        $payment_inputs = [
            'client_id' => $id,
            'bandwidth' => $bandwidth,
            'bill_month' => $bill_month,
            'bill_year' => $bill_year,
            'plan_price' => $plan_price,
            'advance_payment' => $ad_payment,
            'service_charge' => $service_charge,
            'otc_charge' => $otc_charge,
            'pre_due' => $pre_due,
            'total' => $total + $ad_payment,
            'discount' => $discount,
            'all_total' => $sub_total,
            'vat' => $vat,
            'sub_total' => $sub_total + $vat,
            'paid_amount' => $paid,
            'new_paid' => $request->paid_amount,
            'paid_from_advance' => $request->pay_from_advance,
            'pre_balance' => $client->balance,
            'due' => $due,
            'user_id' => Auth::user()->id,
            'payment_date' => $request->payment_date,
            'branchId' => $client->branchId,
            'resellerId' => $client->resellerId
        ];

        $ac = Account::find($request->paid_to);
        $account_balance = $ac->account_balance + $request->paid_amount;
        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $update_client['status'] = 'On';

        if (Auth::user()->resellerId != '') {
            $reseller = Reseller::find(Auth::user()->resellerId);
            $vatRate = $reseller->vat_rate;
        } else {
            $vatData = Config::where('config_title', 'vatRate')->first();
            $vatRate = $vatData->value;
        }

        if ($vat > 0) {
            if ($request->paid_amount > $service_charge) {
                $p_price_paid = $request->paid_amount - $service_charge;
                $p_price_paid_without_vat = ceil(($p_price_paid * 100) / (100 + $vatRate));
                $vat_paid = $p_price_paid - $p_price_paid_without_vat;
            } else {
                $vat_paid = 0.00;
            }
        } else {
            $vat_paid = 0.00;
        }

        $tr_inputs = [
            'invoice_id' => $invoice_id,
            'account_id' => $request->paid_to,
            'tr_type' => 'Bill Payment',
            'tr_category' => 'Income',
            'tr_amount' => $request->paid_amount,
            'tr_vat' => $vat_paid,
            'payer' => $client->client_name,
            'cr' => $request->paid_amount,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => $request->payment_date
        ];

        if ($client->start_transaction == "") {
            $update_client['start_transaction'] = date("Y-m-d");
        }

        $print_receipt = Config::where('config_title', 'print_receipt_after_payment')->first()->value;


        DB::beginTransaction();
        try {


            //IF NO DUE INVOICE FOUND
            if (empty($invoice)) {

                $tr_id = Transaction::create($tr_inputs)->id;
                if (!empty($tr_id)) {
                    $payment_inputs['tr_id'] = $tr_id;
                    if ($payment_id = ClientPayment::create($payment_inputs)->id) {
                        if ($client->update($update_client)) {
                            $ac->update($ac_inputs);

                            //========PAYMENT CONFIRMATION SMS=======
                            $deelkoSMS = new DeelkoSMS();
                            $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);
                            if ($print_receipt == 'Yes') {
                                DB::commit();
                                return redirect()->route('receipt.print', $payment_id)->with(['redirect' => true]);
                            } else {
                                DB::commit();
                                Session::flash('message', 'Client\'s Due Paid Successful');
                                Session::flash('m-class', 'alert-success');
                                return redirect()->route('client.index');
                            }
                        } else {
                            throw new \Exception('Client Data Update Failed!');
                        }
                    } else {
                        throw new \Exception('Client Payment Create Failed!');
                    }
                } else {
                    throw new \Exception('Transaction Create Failed!');
                }
            } //IF DUE INVOICE EXIST
            else {
                //GET ALL DUE INVOICES
                $dueInvoices = Invoice::where('client_id', $id)->where('due', '>', 0)
                    ->orderBy('bill_year', 'ASC')->orderBy('bill_month', 'ASC')->get();

                $_updated_invoices = [];
                //LOOPING OVER ALL DUE INVOICES
                foreach ($dueInvoices as $singleInvoice) {
                    //COUNTING DUE INVOICE
                    $invoiceCount = Invoice::where('client_id', $id)->where('due', '!=', 0)->count();

                    if ($paid > $singleInvoice['due']) {
                        $paid_amount = $singleInvoice['due'];
                    } else {
                        $paid_amount = $paid;
                    }

                    $inputs = [
                        'paid_amount' => $singleInvoice['paid_amount'] + $paid_amount,
                        'due' => $singleInvoice['due'] - $paid_amount
                    ];

                    if ($request->exp_date != '') {
                        $exp_time = explode(':', setting('exp_time'));
                        $date_exp = date($request->exp_date . " " . "$exp_time[0]:$exp_time[1]:00");
                        $update_client['expiration'] = $date_exp;
                    } else {
                        $current_month = date('Y-m');
                        $invoice_month_value = date_create($singleInvoice->bill_year . '-' . $singleInvoice->bill_month);
                        $invoice_month = date_format($invoice_month_value, 'Y-m');

                        if ($current_month == $invoice_month) {

                            //DETERMINE EXPIRE DATE OF MONTH
                            $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
                            if (Auth::user()->resellerId != '') {
                                $reseller = Reseller::find(Auth::user()->resellerId);
                                if ($invoice_system == 'fixed') {
                                    $c_exp_date = $reseller->c_exp_date;
                                } else {
                                    $isExpired = strtotime(now()) - strtotime($client->expiration);
                                    if ($isExpired >= 0) {
                                        $c_exp_date = date('d');
                                    } else {
                                        $c_exp_date = date('d', strtotime($client->expiration));
                                    }
                                }
                            } else {
                                $c_exp_value = Config::where('config_title', 'exp_date')->first();
                                if ($invoice_system == 'fixed') {
                                    $c_exp_date = $c_exp_value->value;
                                } else {
                                    $isExpired = strtotime(now()) - strtotime($client->expiration);
                                    if ($isExpired >= 0) {
                                        $c_exp_date = date('d');
                                    } else {
                                        $c_exp_date = date('d', strtotime($client->expiration));
                                    }
                                }
                            }

                            $exp_time = explode(':', setting('exp_time'));
                            $date_exp = date("Y-m-d H:i:s", mktime(date($exp_time[0]), date($exp_time[1]), date("00"), date("m") + $singleInvoice->duration, date($c_exp_date), date("Y")));

                            $update_client['expiration'] = $date_exp;
                        }
                    }

                    $inv = Invoice::find($singleInvoice['id']);

                    //STORING DATA FOR INVOICE ROLLBACK
                    if ($invoiceCount > 1) {
                        $_updated_invoices[$inv->id] = [
                            'paid_amount' => $inv->paid_amount,
                            'due' => $inv->due
                        ];
                    }

                    if ($client->resellerId != null) {
                        $reseller_data = [];
                        if ($inv->paid_amount == 0) {
                            if ($inv->buy_price > $reseller->balance) {
                                throw new \Exception('Reseller balance is low to take payment. Contact with upstream!');
                            }
                            $reseller_data = [
                                'balance' => $reseller->balance - $inv->buy_price
                            ];
                        }
                    }

                    $paid = $paid - $singleInvoice['due'];


                    //IF PAID AMOUNT DEDUCTION BECAME ZERO OR DUE INVOICE LOOPING ENDED
                    if ($paid <= 0 || !($invoiceCount > 1)) {
                        //IF CLIENT IS INACTIVE

                        if ($client->status == 'Off') {

                            if ($client->server_status == 2) {
                                $update_client['server_status'] = 1;
                                $update_client['server_active_date'] = date('Y-m-d');
                            }

                            if (setting('using_mikrotik')) {

                                //IF CLIENT NOT EXIST IN MIKROTIK
                                if ($client->server_status == 2) {
                                    $mikrotik = new Mikrotik();
                                    $added = $mikrotik->addClientToMikrotik($client, $client->plan);
                                    if (!$added) {
                                        throw new \Exception('Mikrotik Error');
                                    }
                                } //IF CLIENT EXIST BUT NOT ACTIVE IN MIKROTIK
                                else {
                                    $mikrotik = new Mikrotik();
                                    $activated = $mikrotik->activateClientInMikrotik($client);
                                    if (!$activated) {
                                        throw new \Exception('Mikrotik Error');
                                    }
                                }
                            }
                        }

                        //FINALLY UPDATE RELATED DATAS
                        $tr_id = Transaction::create($tr_inputs)->id;
                        if (!empty($tr_id)) {
                            $payment_inputs['tr_id'] = $tr_id;
                            if ($payment_id = ClientPayment::create($payment_inputs)->id) {
                                $inv->update($inputs);
                                if ($client->update($update_client)) {
                                    $ac->update($ac_inputs);
                                    if ($client->resellerId != null) {
                                        $reseller->update($reseller_data);
                                    }

                                    //========PAYMENT CONFIRMATION SMS=======
                                    $deelkoSMS = new DeelkoSMS();
                                    $deelkoSMS->sendSMS($client, 'sms_payment', $request->paid_amount);

                                    if ($print_receipt == 'Yes') {
                                        DB::commit();
                                        return redirect()->route('receipt.print', $payment_id)->with(['redirect' => true]);
                                    } else {
                                        DB::commit();
                                        Session::flash('message', 'Client\'s Due Paid Successful');
                                        Session::flash('m-class', 'alert-success');
                                        return redirect()->route('client.due');
                                    }
                                } else {
                                    throw new \Exception('Client Data Update Failed!');
                                }
                            } else {
                                throw new \Exception('Client Payment Create Failed!');
                            }
                        } else {
                            throw new \Exception('Transaction Create Failed!');
                        }
                    }


                    $inv->update($inputs);
                }
                //ENDOF LOOPING OVER ALL DUE INVOICES

            }
        } catch (\Throwable $e) {
            DB::rollBack();
            if (!empty($_updated_invoices)) {
                foreach ($_updated_invoices as $key => $u_invoice) {
                    $_inv = Invoice::find($key);
                    $_inv->update($u_invoice);
                }
            }
            Session::flash('message', $e->getMessage());
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('client.index');
        }
    }


    public function increaseExpDate(Request $request, $id)
    {
        $this->validate($request, [
            'exp_date' => 'required|date_format:Y-m-d',
        ]);

        $client = Client::where('branchId', Auth::user()->branchId)->findOrFail($id);

        if ($client->server_status != 1) {
            return redirect()->back()->withErrors([
                'exp_date' => 'This Client have not account in ISP Server'
            ]);
        }

        if (!($request->exp_date > date('Y-m-d'))) {
            return redirect()->back()->withErrors([
                'exp_date' => 'New Expire Date Must Grater Then Previous Exp Date'
            ]);
        }

        if (Auth::user()->resellerId != '') {
            $reseller = Reseller::find(Auth::user()->resellerId);
            if ($reseller->exp_date > date('Y-m-d')) {
                Session::flash('message', 'You Can not Increase Client Expire Date!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }

        $date = date($request->exp_date . " " . setting('exp_time') . ":00");
        $update_client['expiration'] = $date;
        if ($client->status == 'Off') {
            $update_client['status'] = 'On';

            if (setting('using_mikrotik')) {

                $mikrotik = new Mikrotik();
                $activated = $mikrotik->activateClientInMikrotik($client);

                if (!$activated) {
                    Session::flash('message', 'Something went wrong with ISP Server!');
                    Session::flash('m-class', 'alert-warning');
                    return redirect()->back();
                }
            }
        }

        if ($client->update($update_client)) {
            Session::flash('message', 'Expire Date Increase Successful');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('client.view', $id);
        } else {
            Session::flash('message', 'Expire Date Increase Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function transferClient(Request $request)
    {
        $client = Client::where('branchId', Auth::user()->branchId)->find($request->id);

        if (empty($client)) {
            Session::flash('message', 'Client Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        } else {
            //CHECK IF CLIENT HAS EXISTING DUE
            if ($client->due > 0) {
                return redirect()->back()->withErrors([
                    'transfer_client' => 'You have to clear due before transfer client!'
                ]);
            }
            //CHECK IF REQUIRED PLAN IS ASSIGNED OR NOT
            if ($request->resellerId != '') {
                $plan_not_exist = ResellerPlan::where([
                    ["plan_id", $client->plan_id],
                    ["resellerId", $request->resellerId],
                    ["reseller_sell_price", '>', 0]
                ])->doesntExist();
                if ($plan_not_exist) {
                    return redirect()->back()->withErrors([
                        'transfer_client' => "Please assign {$client->plan->plan_name} plan to the desired reseller or update reseller selling price!"
                    ]);
                }
            }

            $update_client['resellerId'] = $request->resellerId ?? null;;
            if ($client->update($update_client)) {
                Session::flash('message', 'Client Transferred Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Client Transfer Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }


    public function destroy($id)
    {
        $client = Client::where('resellerId', Auth::user()->resellerId)
            ->where('server_status', 2)->where('status', 'Off')->findOrFail($id);

        if (!empty($client)) {
            if ($client->delete()) {
                Session::flash('message', 'Client Deleted Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->back();
            } else {
                Session::flash('message', 'Client Deletion Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'You do not have access to delete this client!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    private function planChangeToMikrotik($client, $old_plan, $new_plan)
    {
        if ($new_plan->server->id != $old_plan->server->id) {
            $oldServerConnect = Roar::connect($old_plan->server->server_ip, $old_plan->server->server_port, $old_plan->server->username, encrypt_decrypt('decrypt', $old_plan->server->password));
            $newServerConncet = Roar::connect($new_plan->server->server_ip, $new_plan->server->server_port, $new_plan->server->username, encrypt_decrypt('decrypt', $new_plan->server->password));

            if (!$oldServerConnect->isConnected() || !$newServerConncet->isConnected()) {
                return false;
            }
        }
        //        else {
        //            if ($new_plan->type != $old_plan->type) {
        $mikrotik = new Mikrotik();
        //Delete Data From Old Server
        $deleted = $mikrotik->removeClientFromMikrotik($client, $old_plan);
        //Add Data To New Server
        $activate = $client->status == 'On' ? true : false;
        $added = $mikrotik->addClientToMikrotik($client, $new_plan, $activate);

        if ($deleted && $added) {
            return true;
        }

        //            }
        //            else {
        //                $mikrotik = new Mikrotik();
        //
        //                //Update data to Server
        //                $updated = $mikrotik->addClientToMikrotik($client, $old_plan);
        //                if ($updated) {
        //                    return true;
        //                }
        //            }

        //        }

        return false;
    }

    public function clientStatusForNewAndOld(Request $request)
    {

        $startDate = Carbon::now()->subDays(30)->startOfDay(); // Start of the day, 30 days ago
        $endDate = Carbon::now()->endOfDay(); // End of today
        // Fetch all active clients for last 30 days
        $clientData = Client::whereBetween('created_at', [$startDate, $endDate])
            ->where('server_status', 1)
            ->where('status', 'On')
            ->get();

        if ($request->from_date != '') {
            $clientData = $clientData->whereBetween('created_at', $this->range);
        }
        if ($request->clientType != '' && $request->clientType == "newClients") {
            $startDate = Carbon::now()->subDays(30)->startOfDay(); // or any other date you choose
            $endDate = Carbon::now()->endOfDay(); // end of today
            $clientData = Client::whereBetween('created_at', [$startDate, $endDate])->get();
        }
        if ($request->clientType != '' && $request->clientType == "oldClients") {
            $startDate = Carbon::now()->subDays(30)->startOfDay(); // Start of the day, 30 days ago
            $endDate = Carbon::now()->endOfDay(); // End of today

            // Fetch clients with the additional specified conditions
            $clientData = Client::whereBetween('created_at', [$startDate, $endDate])
                ->where('server_status', 2)
                ->where('status', 'Off')
                ->whereNotNull('server_inactive_date')
                ->get();
        }
        return view("admin.pages.client_status_report_old_new", [
            'clientData' => $clientData,
            'page_title' => 'Client Status Report',
            'route_url' => 'client.status',
        ]);
    }

    public function bulkClientsExpiration(Request $request)
    {
        $resellers = Reseller::get();
        $clientData = Client::with(['plan', 'reseller'])
            ->where('server_status', 1)
            ->whereNull('resellerId');

        if ($request->from_date != '') {
            $clientData = $clientData->whereBetween('created_at', $this->range);
        }
        if ($request->resellerId != '') {
            $clientData = Client::with(['plan', 'reseller'])
                ->where('server_status', 1)
                ->whereNotNull('resellerId')->where('resellerId', $request->resellerId);;
        }
        $clientData = $clientData->orderBy(DB::raw('LENGTH(username) , username'))->get();

        return view("admin.pages.bulk_expiration_clients", [
            'resellers' => $resellers,
            'clientData' => $clientData,
            'page_title' => 'Bulk Expire Date',
            'route_url' => 'expire.bulk.clients',
        ]);
    }

    public function bulkClientsExpirationUpdate(Request $request)
    {
        $client_ids = json_decode($request->input('client_ids'), true); // Decode JSON string to array

        if (empty($client_ids) || !$request->filled('bulkExpiration')) {
            return response()->json(['error' => true, 'message' => 'Invalid input.']);
        }

        $expire_date = $request->input('bulkExpiration');
        $expire_time = DB::table('configs')->where('config_title', 'exp_time')->value('value') ?? "00:00";

        try {
            $expire_date_time = Carbon::createFromFormat('Y-m-d H:i', $expire_date . ' ' . $expire_time);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Invalid date or time format.']);
        }

        if ($expire_date_time->lt(Carbon::now())) {
            return response()->json(['error' => true, 'message' => 'Date cannot be less than today!']);
        }

        DB::beginTransaction();
        try {
            foreach ($client_ids as $client_id) {
                $client = Client::find($client_id);

                if ($client) {
                    $client->expiration = $expire_date_time;
                    if ($client->status == "Off") {

                        $using_mikrotik = DB::table('configs')
                            ->where('config_title', 'using_mikrotik')
                            ->value('value');

                        $server_id = $client->plan->server->id;
                        $server_status = Server::find($server_id)->value('status');

                        if ($using_mikrotik == 1 && $server_status == 1) {
                            $mikrotik = new Mikrotik();
                            $activated = $mikrotik->activateClientInMikrotik($client);
                            if ($activated) {
                                $client->status = 'On';
                            } else {
                                return response()->json(['error' => true, 'message' => 'Failed to activate client in Mikrotik.']);
                            }
                        }
                        $client->status = 'On';
                    }
                    $client->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'error' => false, 'message' => 'Expiration dates updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => true, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}
