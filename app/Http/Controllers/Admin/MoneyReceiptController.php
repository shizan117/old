<?php

namespace App\Http\Controllers\Admin;

use App\Reseller;
use App\ResellerPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ClientPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use DB;

class MoneyReceiptController extends Controller
{
    private $from_date;
    private $to_date;
    private $range;
    public function __construct()
    {
        //		if(\request()->from_date != ''){
        //			$this->from_date = \request()->from_date??date("Y-m-d");
        //			$this->to_date = \request()->to_date??date("Y-m-d");
        //		}
        ////		$this->range = [$this->from_date.' 00:00:00',$this->to_date.' 23:59:59'];
        //
        //		$this->range = [$this->from_date,$this->to_date];

        // $this->range = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        // if (\request()->from_date != '') {
        //     $this->from_date = \request()->from_date ?? date("Y-m-d");
        //     $this->to_date = \request()->to_date ?? date("Y-m-d");
        //     $this->range = [$this->from_date, $this->to_date];
        // }

        // Default range: current month
        $this->from_date = Carbon::now()->startOfMonth()->toDateString();
        $this->to_date = Carbon::now()->endOfMonth()->toDateString();
        $this->range = [$this->from_date, $this->to_date];

        // Override with user input if provided
        if (\request()->has('from_date') && \request()->has('to_date')) {
            $this->from_date = \request()->input('from_date');
            $this->to_date = \request()->input('to_date');

            // Validate and set the range based on user input
            $this->range = [
                Carbon::createFromFormat('Y-m-d', $this->from_date)->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $this->to_date)->endOfDay()
            ];
        }
    }

    // public function index(Request $request)
    // {
    //     if (Auth::user()->branchId != '') {
    //         $payments = ClientPayment::whereHas("client")
    //             ->where("branchId", Auth::user()->branchId);
    //     } else {
    //         $payments = ClientPayment::whereHas("client")
    //             ->where('resellerId', Auth::user()->resellerId)
    //             ->where("branchId", Auth::user()->branchId);
    //     }

    //     // Filter by date range if provided
    //     $payments = $payments->whereBetween('payment_date', $this->range)->orderBy('payment_date', 'DESC')->get();

    //     // Adding transaction details to each payment
    //     foreach ($payments as $payment) {
    //         // Retrieve the invoice ID
    //         $invoiceId = DB::table('invoices')
    //             ->where('bill_month', $payment->bill_month)
    //             ->where('bill_year', $payment->bill_year)
    //             ->value('id');

    //         // Retrieve the transaction related to the invoice ID
    //         $transaction = DB::table('transactions')
    //             ->where('invoice_id', $invoiceId)
    //             ->first();
    //     }

    //     // Determine the page title based on date filter
    //     $page_title = $request->from_date == '' ? 'This Month Receipt List' : 'Custom Search';

    //     return view('admin.pages.receipt_list', compact('payments', 'page_title'));
    // }

    public function index(Request $request)
    {
        $page_title = ($request->from_date == '' && $request->to_date == '') ? 'This Month Receipt List' : 'Custom Search';
        return view('admin.pages.receipt_list', compact('page_title'));
    }

    // public function allData(Request $request)
    // {
    //     $query = ClientPayment::with(['client', 'transaction.user']) // Include the user relationship
    //         ->where(function ($query) {
    //             if (Auth::user()->branchId != '') {
    //                 $query->where("branchId", Auth::user()->branchId);
    //             } else {
    //                 $query->where('resellerId', Auth::user()->resellerId)
    //                     ->where("branchId", Auth::user()->branchId);
    //             }
    //         });

    //     // Filter by date range
    //     if ($request->from_date && $request->to_date) {
    //         $query->whereBetween('payment_date', [$request->from_date, $request->to_date]);
    //     }

    //     // Get total count before pagination
    //     $totalRecords = $query->count();

    //     // Paginate with correct page number for DataTables
    //     $payments = $query->orderBy('payment_date', 'DESC')
    //         ->paginate($request->length, ['*'], 'page', ($request->start / $request->length) + 1);

    //     // Return JSON response with the expected DataTables structure
    //     return response()->json([
    //         'draw' => intval($request->draw),
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $totalRecords,
    //         'data' => $payments->items(),
    //     ]);
    // }

    
    public function allData(Request $request)
    {
        // Start measuring time
        // $startTime = microtime(true);
    
        // Fetch data with relationships
        // $query = ClientPayment::with(['client', 'transaction.user'])
        //     ->where(function ($query) {
        //         if (Auth::user()->branchId != '') {
        //             $query->where("branchId", Auth::user()->branchId);
        //         } else {
        //             $query->where('resellerId', Auth::user()->resellerId)
        //                 ->where("branchId", Auth::user()->branchId);
        //         }
        //     });

        $query = ClientPayment::with(['client', 'transaction.user'])
    ->join('clients', 'client_payments.client_id', '=', 'clients.id')  // Assuming the foreign key in client_payments is client_id
    ->where(function ($query) {
        $user = Auth::user();
        
        if ($user->branchId) {
            $query->where(function ($query) use ($user) {
                $query->where('clients.branchId', $user->branchId)
                    ->where(function ($query) {
                        $query->whereNull('clients.resellerId')
                            ->orWhere('clients.resellerId', Auth::user()->resellerId);
                    });
            });
        } else {
            $query->where('clients.resellerId', $user->resellerId)
                ->whereNull('clients.branchId');  // Ensure no branchId filter if it is not applicable
        }
    })
    ->select('client_payments.*');  // Select only the fields from ClientPayment
    
        // Filter by date range
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('payment_date', [$request->from_date, $request->to_date]);
        }
    
        // Fetch all records without pagination
        $payments = $query->orderBy('payment_date', 'DESC')->get();
    
        // Stop measuring time
        // $endTime = microtime(true);
        // $executionTime = $endTime - $startTime;
    
        // Log the execution time for debugging purposes
        // \Log::info('Execution time for loading all data: ' . $executionTime . ' seconds.');
    
        // Return JSON response with all data
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $payments->count(),
            'recordsFiltered' => $payments->count(),
            'data' => $payments,
        ]);
    }
        
    


    public function branchesReceiptList()
    {
        $payments = ClientPayment::with(['client', 'user'])->whereHas("client")
            ->whereNotNull("branchId")->orderBy('payment_date', 'DESC')->get();
        $page_title = 'Branches Receipt List';

        return view('admin.pages.receipt_list', compact('payments', 'page_title'));
    }

    public function resellerReceiptList(Request $request)
    {
        $auth_user = Auth::user();
        $resellerId = (Auth::user()->resellerId != '') ? $auth_user->resellerId : $request->resellerId;
        $resellers = Reseller::get();

        if (Auth::user()->resellerId == '') {
            $page_title = 'Reseller Receipt List';
            $payments = ResellerPayment::query();
            if ($request->resellerId != '') {
                $payments = $payments->where('resellerId', $request->resellerId);
            }
        } else {
            $payments = ResellerPayment::where('resellerId', Auth::user()->resellerId);
            $page_title = 'Seller Receipt List';
        }

        $payments = $payments->whereBetween('created_at', $this->range)->orderBy('created_at', 'DESC')->get();

        return view('admin.pages.reseller_receipt_list', compact('payments', 'page_title', 'resellers'));
    }

    public function show($id)
    {
        if (Auth::user()->branchId != '') {
            $payment = ClientPayment::with(['client'])->where("branchId", Auth::user()->branchId)->find($id);
        } else {
            $payment = ClientPayment::with(['client'])->where('resellerId', Auth::user()->resellerId)->find($id);
        }

        if ($payment != '') {
            $client = \App\Client::with([
                'plan' => function ($query) {
                    $query->get();
                }
            ])->find($payment->client->id);

            return view('admin.pages.receipt_view', compact('client', 'payment'));
        } else {
            Session::flash('message', 'Receipt Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('receipt.index');
        }
    }

    public function print($id)
    {
        if (Auth::user()->branchId != '') {
            $payment = ClientPayment::with(['client'])->where("branchId", Auth::user()->branchId)->find($id);
        } else {
            $payment = ClientPayment::with(['client'])->where('resellerId', Auth::user()->resellerId)->find($id);
        }

        if ($payment != '') {
            $client = \App\Client::with([
                'plan' => function ($query) {
                    $query->get();
                }
            ])->find($payment->client->id);

            return view('admin.pages.receipt_print', compact('client', 'payment'));
        } else {
            Session::flash('message', 'Receipt Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('receipt.index');
        }
    }

    public function bulkReceiptPrint(Request $request)
    {
        if (Auth::user()->branchId != '') {
            $receipts = ClientPayment::with(['client'])->where("branchId", Auth::user()->branchId)->whereIn('id', $request->receiptId)->get();
        } else {
            $receipts = ClientPayment::with(['client'])->where('resellerId', Auth::user()->resellerId)->whereIn('id', $request->receiptId)->get();
        }

        if (!empty($receipts)) {
            return view('admin.pages.receipt_print_bulk', compact('receipts'));
        } else {
            Session::flash('message', 'No receipt was selected!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function resellerShow($id)
    {
        $payment = ResellerPayment::find($id);

        if ($payment != '') {
            return view('admin.pages.reseller_receipt_view', compact('payment'));
        } else {
            Session::flash('message', 'Receipt Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('receipt.seller');
        }
    }

    public function resellerReceiptPrint(Request $request)
    {
        $auth_user = Auth::user();
        $resellerId = (Auth::user()->resellerId != '') ? $auth_user->resellerId : $request->resellerId;

        if (Auth::user()->resellerId == '') {
            $payments = ResellerPayment::query();
            if ($request->resellerId != '') {
                $payments = $payments->where('resellerId', $request->resellerId);
            }
        } else {
            $payments = ResellerPayment::where('resellerId', Auth::user()->resellerId);
        }

        $payments = $payments->whereBetween('created_at', $this->range)->orderBy('created_at')->get();
        $reseller = Reseller::find($request->resellerId);
        $date_range = $this->range;

        return view('admin.pages.reseller_receipt_print', compact('payments', 'reseller', 'date_range'));
    }
}
