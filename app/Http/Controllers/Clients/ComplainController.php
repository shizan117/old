<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Complain;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ComplainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
	    $complainData = Complain::where('client_id', Auth::user()->id)->orderBy('complain_date', 'DESC')->get();

	    return view('pages.complain_list',compact('complainData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
	    return view('pages.complain_create');
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
            'title'   => 'required',
            'description'   => 'nullable',
        ]);

        $complain = new Complain();
        $complain->client_id = Auth::user()->id;
        $complain->title = $request->title;
        $complain->description = $request->description;
        $complain->complain_date = date('Y-m-d H:i:s');
        $save = $complain->save();

        if($save)
        {
            Session::flash('message', 'Complain Submitted Successfully! Our Complain Department will review this complain soon!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('client.complain.index');
        }else{
            Session::flash('message', 'Submission Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }

    }
    public function storeWeb(Request $request)
    {
        $complain = new Complain();
        $complain->client_id = Client::where('username',$request->clientId)->value('id');
        $complain->title = $request->complainTitle;
        $complain->description = $request->complainDesc;
        $complain->complain_date = date('Y-m-d H:i:s');
        $complain->save();
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
        $complain = Complain::findOrFail($id);
	    if($complain->client_id == Auth::user()->id)
	    {
		    return view('pages.complain_edit', compact('complain'));
	    }else{
		    Session::flash('message', 'You are not the right user to edit this complain!');
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
    public function update(Request $request,$id)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);
        $complainData = Complain::findOrFail($id);
	    $complainData->title = $request->title;
	    $complainData->description = $request->description;
		$save = $complainData->save();

        if($save)
        {
            Session::flash('message', 'Data Updated Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('client.complain.index');
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
	    $complain = Complain::findOrFail($id);
	    if($complain->client_id == Auth::user()->id)
	    {
		    $delete =$complain->delete();
	    }else{
		    Session::flash('message', 'You are not the right user to delete this complain');
		    Session::flash('m-class', 'alert-danger');
		    return redirect()->back();
	    }

	    if($delete)
	    {
		    Session::flash('message', 'Data Delete Successful!');
		    Session::flash('m-class', 'alert-success');
		    return redirect()->route('client.complain.index');
	    }else{
		    Session::flash('message', 'Data Delete Failed!');
		    Session::flash('m-class', 'alert-danger');
		    return redirect()->back();
	    }
    }

}
