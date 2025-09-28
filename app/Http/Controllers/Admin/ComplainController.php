<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Complain;
use App\User;
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
	private $from_date;
	private $to_date;
	private $range;
	public function __construct() {
		if(\request()->from_date != ''){
			$this->from_date = \request()->from_date??date("Y-m-d");
			$this->to_date = \request()->to_date??date("Y-m-d");
		}
		$this->range = [$this->from_date.' 00:00:00',$this->to_date.' 23:59:59'];
	}

	public function index()
    {
//	    $role_id = Auth::user()->roleId;
//	    $complainData = Complain::where('is_solved','0')->orderBy('complain_date','desc')->get();
//	    return view('admin.pages.complain.index',compact('complainData','role_id'));
    }
    public function pending(Request $request)
    {
        $auth_user = Auth::user();

	    if ($auth_user->branchId != '') {
            $complains = Complain::where('is_solved','0')->
		    whereHas("client", function ($query) {
			    $query->where("branchId", Auth::user()->branchId);
		    });
	    }else {
            $complains = Complain::where('is_solved','0')->
		    whereHas("client", function ($query) {
			    $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
		    });
	    }

        if(!$auth_user->hasRole(['Super-Admin','Reseller'])){
            $complains =  $complains->where('assign_to',$auth_user->id);
        }

        if($request->from_date != ''){
            $complains =  $complains->whereBetween('complain_date',$this->range);
        }
        $complains = $complains->orderBy('complain_date','desc')->get();
	    return view('admin.pages.complain.pending',compact('complains'));
    }
    public function solved(Request $request)
    {
	    $auth_user = Auth::user();

        if ($auth_user->branchId != '') {
		    $complains = Complain::where('is_solved','1')->
		    whereHas("client", function ($query) {
			    $query->where("branchId", Auth::user()->branchId);
		    });
	    }else {
		    $complains = Complain::where('is_solved','1')->
		    whereHas("client", function ($query) {
			    $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
		    });
	    }

        if(!$auth_user->hasRole(['Super-Admin','Reseller'])){
            $complains =  $complains->where('assign_to',$auth_user->id);
        }

        if($request->from_date != ''){
            $complains =  $complains->whereBetween('complain_date',$this->range);
        }

        $complains = $complains->orderBy('complain_date','desc')->get();
	    return view('admin.pages.complain.solved',compact('complains'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::where('resellerId', Auth::user()->resellerId)->get();
	    $clients = Client::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->orderBY('id', 'ASC')->get();

	    return view('admin.pages.complain.create', compact('clients','users'));
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
            'client_id'   => 'required|numeric',
            'title'   => 'required',
            'description'   => 'nullable',
            'action_taken'   => 'nullable',
        ]);

        $complain = new Complain();
        $complain->client_id = $request->client_id;
        $complain->title = $request->title;
        $complain->description = $request->description;
        $complain->action_taken = $request->action_taken;
        $complain->complain_date = date('Y-m-d H:i:s');
        $complain->assign_to = $request->assign_to;
        $save = $complain->save();

        if($save)
        {
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('complain.pending');
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
	    $clients = Client::where('resellerId', Auth::user()->resellerId)
            ->where('branchId', Auth::user()->branchId)
            ->orderBY('id', 'ASC')->get();
        $users = User::where('resellerId', Auth::user()->resellerId)->get();

        $complain = Complain::findOrFail($id);
	    return view('admin.pages.complain.edit', compact('clients','complain','users'));
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
            'client_id' => 'required|numeric'
        ]);
        $complain = Complain::findOrFail($id);
        $complain->client_id = $request->client_id;
        $complain->title = $request->title;
        $complain->description = $request->description;
        $complain->action_taken = $request->action_taken;
        $complain->assign_to = $request->assign_to;
		$save = $complain->save();

        if($save)
        {
            Session::flash('message', 'Data Updated Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('complain.pending');
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
	    $delete = Complain::findOrFail($id)->delete();

	    if($delete)
	    {
		    Session::flash('message', 'Data Delete Successful!');
		    Session::flash('m-class', 'alert-success');
		    return redirect()->route('complain.pending');
	    }else{
		    Session::flash('message', 'Data Delete Failed!');
		    Session::flash('m-class', 'alert-danger');
		    return redirect()->back();
	    }
    }

	public function solveComplain(Request $request, $id)
	{
		$complainData = Complain::findOrFail($id);
		$complainData->is_solved = 1;
		$complainData->solved_date = date('Y-m-d H:i:s');
		$save = $complainData->save();

		if($save)
		{
			Session::flash('message', 'Complain mark as solved!');
			Session::flash('m-class', 'alert-success');
			return redirect()->back();
		}else{
			Session::flash('message', 'Data Updated Failed!');
			Session::flash('m-class', 'alert-danger');
			return redirect()->back();
		}
	}

	public function unsolveComplain(Request $request, $id)
	{
		$complainData = Complain::findOrFail($id);
		$complainData->is_solved = 0;
		$complainData->solved_date = null;
		$save = $complainData->save();

		if($save)
		{
			Session::flash('message', 'Complain mark as unsolved!');
			Session::flash('m-class', 'alert-success');
			return redirect()->back();
		}else{
			Session::flash('message', 'Data Updated Failed!');
			Session::flash('m-class', 'alert-danger');
			return redirect()->back();
		}
	}
}
