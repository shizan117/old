<?php

namespace App\Http\Controllers\Admin;

use App\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Pool;
use Illuminate\Support\Facades\Session;
use MikrotikAPI\Roar\Roar;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        $servers = Server::orderBY('id', 'ASC')->get();
        if($servers != ''){
            return view("admin.pages.server.index" , compact('servers'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.server.create');
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
            'server_name'   => 'required|unique:servers|max:30',
            'server_ip'     => 'required|unique:servers|ip',
            'server_port'   => 'required|numeric',
            'username'      => 'required',
            'password'      => 'required',
            'status'      => 'required'
        ];

        $this->validate($request, $validate);

        $server = new Server();
        $server->server_name = $request->server_name;
        $server->server_ip = $request->server_ip;
        $server->server_port = $request->server_port;
        $server->username = $request->username;
        $server->password = encrypt_decrypt('encrypt',$request->password);
        $server->status = $request->status;

        if($server->save())
        {
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('server.index');
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
        $server_Data = Server::find($id);
        if($server_Data != ''){
            return view("admin.pages.server.edit" , [
                'serverData' => $server_Data
            ]);
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('server.index');
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
        $server = Server::find($id);
        $validate = [
            'server_name'   => 'required|max:30|unique:servers,server_name,'.$server->id.'',
            'server_ip'     => 'required|ip|unique:servers,server_ip,'.$server->id.'',
            'server_port'   => 'required|numeric',
            'username'      => 'required',
            'password'      => 'nullable',
            'status'      => 'required',
        ];

        $this->validate($request, $validate);

        $server->server_name = $request->server_name;
        $server->server_ip = $request->server_ip;
        $server->server_port = $request->server_port;
        $server->username = $request->username;
        $server->status = $request->status;
        if($request->password != ''){
            $server->password = encrypt_decrypt('encrypt',$request->password);
        }

        if($server->save())
        {
            Session::flash('message', 'Data Updated Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('server.index');
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
    public function destroy_server($id)
    {
        $server_Data = Server::find($id);
        $pool_Data=Pool::where('server_id',$server_Data->id)->get();

        if(!count($pool_Data)) 
        {            
           
            if ($server_Data->delete()){
                Session::flash('message', 'Server Deleted Successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('server.index');
            } else {
                Session::flash('message', 'Server Deleting Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        }

        else{
            Session::flash('message', 'Pool is exist with this server!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
        

    }

    public function checkConnection(Request $request)
    {
        $server = Server::findOrFail($request->id);
        $con = Roar::connect($server->server_ip, $server->server_port, $server->username, encrypt_decrypt('decrypt', $server->password));
        if ($con->isConnected()) {
            Session::flash('message', 'Server Connected Successfully!');
            Session::flash('m-class', 'alert-success');
            return response()->json();
        } else {
            Session::flash('message', 'Server Connection Failed!');
            Session::flash('m-class', 'alert-danger');
            return response()->json();
        }

    }

}
