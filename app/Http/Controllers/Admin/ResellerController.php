<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\BkashCheckoutSetting;
use App\Invoice;
use App\Plan;
use App\Reseller;
use App\ResellerInvoice;
use App\ResellerPayment;
use App\ResellerPlan;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\SMS\DeelkoSMS;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class ResellerController extends Controller
{
    private $from_date;
    private $to_date;
    private $range;
    public function __construct()
    {
        $this->range = [Carbon::now()->startOfMonth() . ' 00:00:00', Carbon::now()->endOfMonth() . ' 23:59:59'];
        if (\request()->from_date != '') {
            $this->from_date = \request()->from_date ?? date("Y-m-d");
            $this->to_date = \request()->to_date ?? date("Y-m-d");
            $this->range = [$this->from_date . ' 00:00:00', $this->to_date . ' 23:59:59'];
        }
    }

    public function index()
    {
        $data = Reseller::orderBY('resellerId', 'ASC')->get();

        return view("admin.pages.reseller.index", [
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.pages.reseller.create');
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
            'resellerName'        => 'required|unique:resellers|max:40',
            'resellerLocation'    => 'required',
            'c_exp_date'    => 'numeric'
        ];

        $this->validate($request, $validate);

        $inputs     = $request->all();

        if (Reseller::create($inputs)) {
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('reseller.index');
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
        $reseller = Reseller::find($id);
        if (empty($reseller)) {
            Session::flash('message', 'Reseller Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('reseller.index');
        } else {
            $receipts = ResellerPayment::where('resellerId', $reseller->resellerId)->orderBy('created_at', 'DESC')->get();
            $paydetails = ResellerPayment::where('resellerId', $reseller->resellerId)->where('recharge_amount', '>', 0)
                ->select(DB::raw('DATE(created_at) as date'))
                ->groupBy('date')->get();

            $lsPay = ResellerPayment::where('resellerId', $reseller->resellerId)->where('recharge_amount', '>', 0)->select('created_at', 'recharge_amount')->orderBy('created_at', 'DESC')->first();

            $planData = ResellerPlan::with(['plan'])->where('resellerId', $reseller->resellerId)->get();

            return view(
                'admin.pages.reseller.show',
                compact(
                    'reseller',
                    'receipts',
                    'paydetails',
                    'lsPay',
                    'planData'
                )
            );
        }
    }

    public function deactive_reseller(Request $request, $id)
    {
        $data = Reseller::find($id);
        if ($data) {
            $data->is_payment = $data->is_payment == 0 ? 1 : 0;
            $data->save();
        }
        return redirect()->back();
    }

    public function toggleExtraCharge($id)
    {
        $reseller = Reseller::findOrFail($id);

        // Toggle extra_charge value: if 0 then 1, if 1 then 0
        $reseller->extra_charge = $reseller->extra_charge == 1 ? 0 : 1;

        $reseller->save();

        return redirect()->back()->with('success', 'Reseller Extra Charge taken Permission status updated.');
    }

    public function togglePlan_Price($id)
    {
        $reseller = Reseller::findOrFail($id);

        // Toggle plan_price value: if 0 then 1, if 1 then 0
        $reseller->plan_price = $reseller->plan_price == 1 ? 0 : 1;

        $reseller->save();

        return redirect()->back()->with('success', 'Reseller PLan Price Permission status updated.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $reseller = Reseller::find($id);
        if ($reseller != '') {
            return view("admin.pages.reseller.edit", [
                'reseller' => $reseller
            ]);
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('reseller.index');
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
        $data = Reseller::find($id);
        $validate = [
            'resellerName'        => 'required|max:30|unique:resellers,resellerName,' . $data->resellerId . ',resellerId',
            'resellerLocation'    => 'required',
            'c_exp_date'    => 'numeric'
        ];

        $this->validate($request, $validate);

        $inputs = $request->all();

        if ($data->update($inputs)) {
            Session::flash('message', 'Reseller Data Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('reseller.index');
        } else {
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
        //
    }

    public function payment($id)
    {
        $reseller = Reseller::findOrFail($id);
        if (empty($reseller)) {
            Session::flash('message', 'Reseller Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('reseller.index');
        }
        $accounts = Account::where('resellerId', Auth::user()->resellerId)->orderBY('id', 'ASC')->get();

        return view('admin.pages.reseller.reseller_payment', compact('reseller', 'accounts'));
    }

    public function paymentStore(Request $request, $id)
    {
        $this->validate($request, [
            'recharge_amount' => 'numeric|min:1',
            'account_id' => 'required'
        ]);

        $reseller = Reseller::findOrFail($id);
        $pre_balance = $reseller->balance;
        $new_balance = $reseller->balance + $request->recharge_amount;

        $payment_inputs = [
            'resellerId' => $id,
            'recharge_amount' => $request->recharge_amount,
            'pre_balance' => $pre_balance,
            'user_id' => Auth::user()->id
        ];

        $ac = Account::findOrFail($request->account_id);
        $account_balance = $ac->account_balance + $request->recharge_amount;

        $tr_inputs = [
            'account_id' => $request->account_id,
            'tr_type' => 'Reseller Recharge Balance',
            'tr_category' => 'Income',
            'tr_amount' => $request->recharge_amount,
            'payer' => $reseller->resellerName,
            'cr' => $request->recharge_amount,
            'user_id' => Auth::user()->id,
            'trans_date' => date('Y-m-d')
        ];

        $tr_id = Transaction::create($tr_inputs)->id;
        if (!empty($tr_id)) {
            $payment_inputs['tr_id'] = $tr_id;
            if (ResellerPayment::create($payment_inputs)) {
                $ac->update(['account_balance' => $account_balance]);
                $reseller->update(['balance' => $new_balance]);
                Session::flash('message', 'Recharge Successful!');
                Session::flash('m-class', 'alert-success');
                // Send Reseller confirmation payment messesage here
                $deelkoSMS = new DeelkoSMS();
                $deelkoSMS->sendSMS($reseller, 'sms_payment_to_reseller', $request->recharge_amount, null, null, true);
                return redirect()->route('reseller.index');
            } else {
                Session::flash('message', 'Recharge Failed!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->back();
            }
        } else {
            Session::flash('message', 'Recharge Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function resellerPay()
    {
        $reseller = Reseller::find(Auth::user()->resellerId);

        $totalBuyPrice = Invoice::whereHas("client", function ($query) {
            $query->where('resellerId', Auth::user()->resellerId)->where('server_status', 1);
        })->where('paid_amount', '=', 0)->sum('buy_price');

        $rechargeable = $totalBuyPrice - $reseller->balance;
        if ($rechargeable < 0) $rechargeable = 0;

        $charge = \App\Config::where('config_title', 'bkash_charge')->first();

        Session::put('payer_id', Auth::user()->resellerId);
        Session::put('payer_type', 'reseller');
        $create_payment_url = route('bkash-create-payment');
        $ex_payment_url = route('bkash-execute-payment');
        $success_url = route('receipt.seller');

        return view('admin.pages.reseller.reseller_pay', compact(
            'reseller',
            'charge',
            'rechargeable',
            'create_payment_url',
            'ex_payment_url',
            'success_url'
        ));
    }


    public function getAllResellerInvoice(Request $request)
{
    // Initialize the query to get all invoices related to resellers
    $invoiceData = Invoice::with('client.distribution')
        ->whereNotNull('resellerId')
        ->whereNull('deleted_at');

    // Apply reseller filter if provided
    if ($request->has('reseller_name') && $request->input('reseller_name') != 'All Invoices') {
        $invoiceData->where('resellerId', $request->input('reseller_name'));
    }

    // Apply date range filter if provided
    if ($request->has('from_date') && $request->has('to_date')) {
        $invoiceData->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
    } else {
        $invoiceData->whereBetween('created_at', $this->range);
    }

    // Order by due date and get the results
    $invoiceData = $invoiceData->orderBy('due', 'DESC')->get();

    $page_title = "Reseller Invoice List";

    return view('admin.pages.invoice.reseller_invoice_for_admin', compact('invoiceData', 'page_title'));
}

}
