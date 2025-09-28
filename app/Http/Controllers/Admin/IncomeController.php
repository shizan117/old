<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Transaction, App\IncomeCategory, App\Income, App\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class IncomeController extends Controller
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
            $incomes = Income::where("branchId", Auth::user()->branchId);
        } else {
            $incomes = Income::where('resellerId', Auth::user()->resellerId)
                ->where("branchId", Auth::user()->branchId)
                ->orderBY('date', 'DESC');
        }

        $incomes =  $incomes->whereBetween('date',$this->range);
        if($request->from_date != ''){
            $page_title = 'Income List - '.$this->from_date.' to '.$this->to_date;
        }else{
            $page_title = 'Income List - Last 30 Days';
        }
        $incomes = $incomes->get();
        return view('admin.pages.income.index', compact('page_title', 'incomes'));
    }

    public function branchIncomeList()
    {
        $page_title = 'Branch Income List';
        $incomes = Income::where('resellerId', Auth::user()->resellerId)->whereNotNull('resellerId')->orderBY('date', 'DESC')->get();
        return view('admin.pages.income.index', compact('page_title', 'incomes'));
    }

    public function create()
    {
        $cats = IncomeCategory::where('resellerId', Auth::user()->resellerId)->get();
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        return view('admin.pages.income.create', compact('cats', 'accounts'));
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'income_name' => 'required',
            'income_category' => 'required',
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'income_date' => 'required|date',
            'note' => 'nullable',
        ]);

        $ac = Account::find($request->account);
        $account_balance = $ac->account_balance + $request->amount;

        $ac_inputs = ['account_balance' => $account_balance];

        $tr_type = IncomeCategory::find($request->income_category)->name;

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_type' => $tr_type,
            'tr_category' => 'Income',
            'tr_amount' => $request->amount,
            'cr' => $request->amount,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => $request->income_date,
        ];

        $inputs = [
            'name' => $request->income_name,
            'cat_id' => $request->income_category,
            'amount' => $request->amount,
            'note' => $request->note,
            'date' => $request->income_date,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
        ];
        $tr_id = Transaction::create($tr_inputs)->id;

        if (!empty($tr_id)) {
            $inputs['tr_id'] = $tr_id;
            if (Income::create($inputs)) {
                $ac->update($ac_inputs);
                Session::flash('message', 'Income added successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('income.list');
            } else {
                Session::flash('message', 'Data Saving Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'Data Saving Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function edit($id)
    {
        $income = Income::where('resellerId', Auth::user()->resellerId)->find($id);
        $cats = IncomeCategory::where('resellerId', Auth::user()->resellerId)->get();
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->where('branchId', Auth::user()->branchId)->get();
        if ($income != '') {
            if (Auth::user()->id != $income->user_id) {
                Session::flash('message', 'Only have permission this person who add this entry!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('income.list');
            } else {
                return view('admin.pages.income.edit', compact('income', 'cats', 'accounts'));
            }
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('income.list');
        }
    }

    public function update(Request $request, $id)
    {
        $income = Income::find($id);

        $this->validate($request, [
            'income_name' => 'required|max:30',
            'income_category' => 'required',
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'income_date' => 'required|date',
            'note' => 'nullable',
        ]);

        $tr = Transaction::find($income->tr_id);
        $ac = Account::find($request->account);
        if ($request->account != $income->transaction->account->id) {
            $old_ac = Account::find($income->transaction->account_id);
            $old_ac_update['account_balance'] = $old_ac->account_balance - $income->amount;
            $old_ac->update($old_ac_update);
            $ac_balance = $ac->account_balance;
        } else {
            $ac_balance = $ac->account_balance - $income->amount;
        }

        $account_balance = $ac_balance + $request->amount;

        $ac_inputs = [
            'account_balance' => $account_balance
        ];

        $tr_type = IncomeCategory::find($request->income_category)->name;

        $tr_inputs = [
            'account_id' => $request->account,
            'tr_type' => $tr_type,
            'tr_category' => 'Income',
            'tr_amount' => $request->amount,
            'cr' => $request->amount,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
            'trans_date' => $request->income_date,
        ];

        $inputs = [
            'name' => $request->income_name,
            'cat_id' => $request->income_category,
            'amount' => $request->amount,
            'note' => $request->note,
            'date' => $request->income_date,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId,
        ];


        if ($tr->update($tr_inputs)) {
            if ($income->update($inputs)) {
                $ac->update($ac_inputs);
                Session::flash('message', 'Income updated successfully!');
                Session::flash('m-class', 'alert-success');
                return redirect()->route('income.list');
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


    public function incomeCatList()
    {
        $cats = IncomeCategory::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();
        return view('admin.pages.income.income_cat_list', compact('cats'));
    }

    public function incomeCatAdd()
    {
        return view('admin.pages.income.income_cat_add');
    }

    public function incomeCatStore(Request $request)
    {
        $this->validate($request, [
            'income_category_name' => 'required|max:30'
        ]);

        $inputs = [
            'name' => $request->income_category_name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if (IncomeCategory::create($inputs)) {
            Session::flash('message', 'Income Category Add Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('income.cat.list');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function incomeCatEdit($id)
    {
        $cat = IncomeCategory::where('resellerId', Auth::user()->resellerId)->find($id);
        if ($cat != '') {
            return view('admin.pages.income.income_cat_edit', compact('cat'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('income.cat.list');
        }
    }

    public function incomeCatUpdate(Request $request, $id)
    {

        $cat = IncomeCategory::find($id);
        $this->validate($request, [
            'income_category_name' => 'required|max:30'
        ]);

        $inputs = [
            'name' => $request->income_category_name,
            'resellerId' => Auth::user()->resellerId,
        ];

        if ($cat->update($inputs)) {
            Session::flash('message', 'Income Category Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('income.cat.list');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

}
