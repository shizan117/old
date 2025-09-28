<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transaction, App\ExpanseCategory, App\Expanse, App\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ExpenseController extends Controller
{
//     private $from_date;
//     private $to_date;
//     private $range;
//     public function __construct() {
//         if(\request()->from_date != ''){
//             $this->from_date = \request()->from_date;
//             $this->to_date = \request()->to_date??date("Y-m-d");
//         } else {
//             $this->from_date = date("Y-m-d",strtotime('-30 days'));
//             $this->to_date = date("Y-m-d");
//         }

// 		$this->range = [$this->from_date.' 00:00:00',$this->to_date.' 23:59:59'];
//         $this->range = [$this->from_date.' 00:00:00',$this->to_date];
//     }

private $from_date;
	private $to_date;
	private $range;
	public function __construct() {
		$this->range = [Carbon::now()->startOfMonth().' 00:00:00',Carbon::now()->endOfMonth().' 23:59:59'];
		if(\request()->from_date != ''){
			$this->from_date = \request()->from_date??date("Y-m-d");
			$this->to_date = \request()->to_date??date("Y-m-d");
			$this->range = [$this->from_date.' 00:00:00',$this->to_date.' 23:59:59'];
		}
	}
    public function index(Request $request)
    {
        if (Auth::user()->branchId != '') {
            $expanses = Expanse::where("branchId", Auth::user()->branchId);
        } else {
            $expanses = Expanse::where('resellerId', Auth::user()->resellerId)
                ->where("branchId", Auth::user()->branchId)
                ->orderBY('date', 'DESC');
        }

        $expanses =  $expanses->whereBetween('date',$this->range);

        if($request->from_date != ''){
            $page_title = 'Expense List - '.$this->from_date.' to '.$this->to_date;
        }else{
            $page_title = 'Expense List - Last 30 Days';
        }
        $expanses = $expanses->get();
        return view('admin.pages.expense.index', compact('page_title', 'expanses'));
    }

    public function branchExpanseList()
    {
        $page_title = 'Branch Expanse List';
        $expanses = Expanse::where('resellerId', Auth::user()->resellerId)->whereNotNull('resellerId')->orderBY('date', 'DESC')->get();
        return view('admin.pages.expense.index', compact('page_title', 'expanses'));
    }

    public function create()
    {
        $cats = ExpanseCategory::where('resellerId', Auth::user()->resellerId)->get();
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        return view('admin.pages.expense.create', compact('cats', 'accounts'));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'expanse_name' => 'required',
            'expanse_category' => 'required',
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'note' => 'nullable',
            'expense_date' => 'required|date',
        ]);

        $ac = Account::find($request->account);

        if ($ac->account_balance < $request->amount) {
            return back()->withErrors([
                'account' => 'Insufficient Balance In This Account',
                'amount' => 'Insufficient Balance',
            ]);
        }

        $account_balance = $ac->account_balance - $request->amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $tr_type = ExpanseCategory::find($request->expanse_category)->name;

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_type' => $tr_type,
            'tr_category' => 'Expanse',
            'tr_amount' => $request->amount,
            'dr' => $request->amount,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => $request->expense_date,
        ];

        $inputs = [
            'name' => $request->expanse_name,
            'cat_id' => $request->expanse_category,
            'amount' => $request->amount,
            'note' => $request->note,
            'date' => $request->expense_date,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
        ];
        $tr_id = Transaction::create($tr_inputs)->id;

        if (!empty($tr_id)) {
            $inputs['tr_id'] = $tr_id;
            if (Expanse::create($inputs)) {
                $ac->update($ac_inputs);
                Session::flash('message', 'Expanse Add Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('expanse.list');
            } else {
                Session::flash('message', 'Data Save Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }

        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $expanse = Expanse::where('resellerId', Auth::user()->resellerId)->find($id);
        $cats = ExpanseCategory::where('resellerId', Auth::user()->resellerId)->get();
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        if ($expanse != '') {
            if (Auth::user()->id != $expanse->user_id) {
                Session::flash('message', 'Only have permission this person who add this entry!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('expanse.list');
            } else {
                return view('admin.pages.expense.edit', compact('expanse', 'cats', 'accounts'));
            }

        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('expanse.list');
        }
    }

    public function update(Request $request, $id)
    {
        $expanse = Expanse::find($id);

        $this->validate($request, [
            'expanse_name' => 'required|max:30',
            'expanse_category' => 'required',
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'note' => 'nullable',
            'expense_date' => 'required|date',
        ]);


        $tr = Transaction::find($expanse->tr_id);
        $ac = Account::find($request->account);
        if ($request->account != $expanse->transaction->account->id) {
            $old_ac = Account::find($expanse->transaction->account_id);
            $old_ac_update['account_balance'] = $old_ac->account_balance + $expanse->amount;
            $old_ac->update($old_ac_update);
            $ac_balance = $ac->account_balance;
        } else {
            $ac_balance = $ac->account_balance + $expanse->amount;
        }

        if ($ac_balance < $request->amount) {
            return back()->withErrors([
                'account' => 'Insufficient Balance In This Account',
                'amount' => 'Insufficient Balance',
            ]);
        }

        $account_balance = $ac_balance - $request->amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $tr_type = ExpanseCategory::find($request->expanse_category)->name;

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_type' => $tr_type,
            'tr_category' => 'Expanse',
            'tr_amount' => $request->amount,
            'dr' => $request->amount,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => $request->expense_date,
        ];

        $inputs = [
            'name' => $request->expanse_name,
            'cat_id' => $request->expanse_category,
            'amount' => $request->amount,
            'note' => $request->note,
            'date' => $request->expense_date,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
        ];


        if ($tr->update($tr_inputs)) {
            if ($expanse->update($inputs)) {
                $ac->update($ac_inputs);
                Session::flash('message', 'Expanse Update Successful!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('expanse.list');
            } else {
                Session::flash('message', 'Data Update Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }

        } else {
            Session::flash('message', 'Data Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }


    public function expanseCatList()
    {
        $cats = ExpanseCategory::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();
        return view('admin.pages.expense.expanse_cat_list', compact('cats'));
    }

    public function expanseCatAdd()
    {
        return view('admin.pages.expense.expanse_cat_add');
    }

    public function expanseCatStore(Request $request)
    {
        $this->validate($request, [
            'expanse_category_name' => 'required|max:30'
        ]);

        $inputs = [
            'name' => $request->expanse_category_name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if (ExpanseCategory::create($inputs)) {
            Session::flash('message', 'Expanse Category Add Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('expanse.cat.list');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function expanseCatEdit($id)
    {
        $cat = ExpanseCategory::where('resellerId', Auth::user()->resellerId)->find($id);
        if ($cat != '') {
            return view('admin.pages.expense.expanse_cat_edit', compact('cat'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('expanse.cat.list');
        }
    }

    public function expanseCatUpdate(Request $request, $id)
    {

        $cat = ExpanseCategory::find($id);
        $this->validate($request, [
            'expanse_category_name' => 'required|max:30'
        ]);

        $inputs = [
            'name' => $request->expanse_category_name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if ($cat->update($inputs)) {
            Session::flash('message', 'Expanse Category Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('expanse.cat.list');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

}
