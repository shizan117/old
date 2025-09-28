<?php

namespace App\Http\Controllers\Admin;

use App\Branch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ProductCategory,
    App\Product,
    App\Purchase,
    App\PurchaseItem,
    App\StockItem,
    App\UsedItem,
    App\SoldItem,
    App\RefundItem,
    App\LostItem,
    App\Account,
    App\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function productCatAdd()
    {
        return view('admin.pages.product_category_add');
    }

    public function productCatStore(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:30', Rule::unique('product_categories')->where(function ($query) use ($request) {
                return $query->where('resellerId', $request->resellerId);
            })]
        ]);
        $inputs = $request->all();
        if (ProductCategory::create($inputs)) {
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('product.index');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function productCatEdit($id)
    {
        $product_cat = ProductCategory::where('resellerId', Auth::user()->resellerId)->find($id);
        if ($product_cat != '') {
            return view('admin.pages.product_category_edit', compact('product_cat'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('product.index');
        }
    }

    public function productCatUpdate(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required', 'max:30', Rule::unique('product_categories')->whereNot('id', $id)->where(function ($query) use ($request) {
                return $query->where('resellerId', $request->resellerId);
            })]
        ]);
        $cat = ProductCategory::find($id);
        $inputs = $request->all();
        if ($cat->update($inputs)) {
            Session::flash('message', 'Data Update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('product.index');
        } else {
            Session::flash('message', 'Data Update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function products()
    {
        $role_id = Auth::user()->roleId;
        $products = Product::where('branchId', Auth::user()->branchId)->where('resellerId', Auth::user()->resellerId)->get();
        $categories = ProductCategory::where('resellerId', Auth::user()->resellerId)->get();
        $admin = false;

        return view('admin.pages.products', compact('role_id', 'products', 'categories', 'admin'));
    }

    public function productsBranch()
    {
        $role_id = Auth::user()->roleId;
        $products = Product::whereNotNull('branchId')->where('resellerId', Auth::user()->resellerId)->get();
        $categories = ProductCategory::where('resellerId', Auth::user()->resellerId)->get();
        $admin = true;

        return view('admin.pages.products', compact('role_id', 'products', 'categories', 'admin'));
    }

    public function productAdd()
    {
        $role_id = Auth::user()->roleId;
        $categories = ProductCategory::where('resellerId', Auth::user()->resellerId)->get();
        $branches = Branch::all();

        return view('admin.pages.product_add', compact('role_id', 'categories', 'branches'));
    }

    public function productStore(Request $request)
    {

        $this->validate($request, [
            'category' => 'required',
            'name' => ['required', 'max:30', Rule::unique('products')->where(function ($query) use ($request) {
                return $query->where('branchId', $request->branch)->where('resellerId', $request->resellerId);
            })],
            'single_unit_serial' => 'required',
            'unit' => 'required'
        ]);


        $inputs = [
            'name' => $request->name,
            'unit' => $request->unit,
            'cat_id' => $request->category,
            'serial_type' => $request->single_unit_serial,
            'branchId' => $request->branch,
            'resellerId' => $request->resellerId
        ];

        if (Product::create($inputs)) {
            Session::flash('message', 'Data Save Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('product.index');
        } else {
            Session::flash('message', 'Data Save Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function productEdit($id)
    {
        $product = Product::where('resellerId', Auth::user()->resellerId)->find($id);
        if ($product != '') {
            $role_id = Auth::user()->roleId;
            $categories = ProductCategory::where('resellerId', Auth::user()->resellerId)->get();
            $branches = Branch::all();
            return view('admin.pages.product_edit', compact('product', 'role_id', 'categories', 'branches'));
        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('product.index');
        }
    }

    public function productUpdate(Request $request, $id)
    {
        $this->validate($request, [
            'category' => 'required',
            'name' => ['required', 'max:30', Rule::unique('products')->whereNot('id', $id)->where(function ($query) use ($request) {
                return $query->where('branchId', $request->branch)->where('resellerId', $request->resellerId);
            })],
            'single_unit_serial' => 'required',
            'unit' => 'required'
        ]);
        $inputs = [
            'name' => $request->name,
            'unit' => $request->unit,
            'cat_id' => $request->category,
            'serial_type' => $request->single_unit_serial,
            'branchId' => $request->branch,
            'resellerId' => $request->resellerId
        ];
        $product = Product::find($id);

        if ($product->update($inputs)) {
            Session::flash('message', 'Product update Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('product.index');
        } else {
            Session::flash('message', 'Product update Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function purchases()
    {
        $role_id = Auth::user()->roleId;
        $page_title = 'Purchase List';
        $purchases = Purchase::where('branchId', Auth::user()->branchId)
            ->where('resellerId', Auth::user()->resellerId)
            ->orderBY('id', 'DESC')->get();
        $admin = false;
        return view('admin.pages.purchase_list', compact('purchases', 'page_title', 'admin', 'role_id'));
    }

    public function purchasesBranch()
    {
        $role_id = Auth::user()->roleId;
        $page_title = 'Branch Purchase List';
        $purchases = Purchase::whereNotNull('branchId')->where('resellerId', Auth::user()->resellerId)->orderBY('id', 'DESC')->get();
        $admin = true;
        return view('admin.pages.purchase_list', compact('purchases', 'page_title', 'admin', 'role_id'));
    }

    public function purchaseAdd()
    {
        $accounts = Account::where('branchId', Auth::user()->branchId)->where('resellerId', Auth::user()->resellerId)->get();
        $cats = ProductCategory::where('resellerId', Auth::user()->resellerId)->get();
        return view('admin.pages.purchase_add', compact('cats', 'accounts'));
    }

    public function purchaseSelectProduct(Request $request)
    {
        $products = Product::where('cat_id', $request->cat)
            ->where('branchId', Auth::user()->branchId)
            ->where('resellerId', Auth::user()->resellerId)
            ->get();
        return response()->json($products);
    }

    public function purchaseProductSl(Request $request)
    {
        $product = Product::find($request->id);
        return response()->json($product);
    }

    public function purchaseStore(Request $request)
    {
        $final_total_amt = $request->final_total;
        $total_item = $request->total_item;
        $date = $request->date;
        $account = Account::find($request->account);
        if ($account->account_balance < $final_total_amt) {
            return redirect()->back()->withErrors([
                'account' => 'Insufficient Balance In This Account'
            ]);
        }

        $tr_inputs = [
            'account_id' => $account->id,
            'tr_type' => 'Product Purchase',
            'tr_category' => 'Expanse',
            'tr_amount' => $final_total_amt,
            'dr' => $final_total_amt,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId
        ];

        $ac_inputs['account_balance'] = $account->account_balance - $final_total_amt;

        $account->update($ac_inputs);

        $tr_id = Transaction::create($tr_inputs)->id;

        $pr_inputs = [
            'price' => $final_total_amt,
            'purchase_date' => $date,
            'tr_id' => $tr_id,
            'user_id' => Auth::user()->id,
            'branchId' => Auth::user()->branchId,
            'resellerId' => Auth::user()->resellerId
        ];

        $pr_id = Purchase::create($pr_inputs)->id;

        for ($i = 0; $i < $total_item; $i++) {
            $qty = $request->order_item_quantity[$i];
            $purchase_items[] = [
                'product_id' => $request->item_name[$i],
                'qty' => $qty,
                'price' => $request->order_item_price[$i],
                'total_price' => $request->order_item_final_amount[$i],
                'purchase_id' => $pr_id,
            ];
            if ($request->item_sl[$i] == 1) {
                for ($j = 0; $j < $qty; $j++) {
                    $stock_items[] = [
                        'product_id' => $request->item_name[$i],
                        'qty' => 1,
                        'price' => $request->order_item_price[$i],
                        'total_price' => $request->order_item_price[$i],
                        'serial' => $this->randomString(),
                        'purchase_id' => $pr_id,
                    ];
                }
            } else {
                $stock_items[] = [
                    'product_id' => $request->item_name[$i],
                    'qty' => $request->order_item_quantity[$i],
                    'price' => $request->order_item_price[$i],
                    'total_price' => $request->order_item_final_amount[$i],
                    'serial' => $request->item_sku[$i],
                    'purchase_id' => $pr_id,
                ];
            }
            $product = Product::find($request->item_name[$i]);
            $product_inputs['stock'] = $product->stock + $qty;
            $product->update($product_inputs);
        }
        if (PurchaseItem::insert($purchase_items) && StockItem::insert($stock_items)) {
            Session::flash('message', 'Items Added Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('purchases');
        } else {
            Session::flash('message', 'Items Add Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function purchaseEdit($id)
    {
        $prs = Purchase::find($id);
        if ($prs != '') {
            if (Auth::user()->id != $prs->user_id) {
                Session::flash('message', 'Only have permission this person who add this entry!');
                Session::flash('m-class', 'alert-danger');
                return redirect()->route('purchases');
            } else {
                $check_items = StockItem::whereNotNull('updated_at')->where('purchase_id', $id)->first();
                if (!empty($check_items)) {
                    Session::flash('message', 'Product Already Used From This Purchase!');
                    Session::flash('m-class', 'alert-danger');
                    return redirect()->route('purchases');
                } else {
                    $pr_items = PurchaseItem::where('purchase_id', $id)->get();
                    $accounts = Account::where('branchId', Auth::user()->branchId)->where('resellerId', Auth::user()->resellerId)->get();
                    $cats = ProductCategory::where('resellerId', Auth::user()->resellerId)->get();
                    return view('admin.pages.purchase_edit',
                        compact('prs', 'pr_items', 'accounts', 'cats'));
                }

            }

        } else {
            Session::flash('message', 'Data Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('purchases');
        }
    }

    public function purchaseUpdate(Request $request, $id)
    {
        $pr = Purchase::find($id);
        $final_total_amt = $request->final_total;
        $total_item = $request->total_item;
        $date = $request->date;
        $account = Account::find($request->account);
        if ($request->account != $pr->transaction->account_id) {
            $old_ac = Account::find($pr->tr_id);
            $old_ac_update['account_balance'] = $old_ac->account_balance + $pr->amount;
            $old_ac->update($old_ac_update);
            $ac_balance = $account->account_balance;
        } else {
            $ac_balance = $account->account_balance + $pr->amount;
        }


        if ($ac_balance < $final_total_amt) {
            return redirect()->back()->withErrors([
                'account' => 'Insufficient Balance In This Account'
            ]);
        }

        $tr_inputs = [
            'account_id' => $account->id,
            'tr_amount' => $final_total_amt,
            'dr' => $final_total_amt
        ];

        $ac_inputs['account_balance'] = $ac_balance - $final_total_amt;

        $account->update($ac_inputs);
        $tr = Transaction::find($pr->tr_id);

        $tr->update($tr_inputs);

        $pr_inputs = [
            'price' => $final_total_amt,
            'purchase_date' => $date,
        ];

        $pr->update($pr_inputs);

        $pr_info = PurchaseItem::where('purchase_id', $id)->get();
        foreach ($pr_info as $info) {
            $pro_info = Product::find($info->product_id);
            $pro_inputs['stock'] = $pro_info->stock - $info->qty;
            $pro_info->update($pro_inputs);
            $info->delete();
        }

        StockItem::where('purchase_id', $id)->delete();


        for ($i = 0; $i < $total_item; $i++) {
            //echo $request->order_item_quantity[$i].'<br>';
            $qty = $request->order_item_quantity[$i];
            $purchase_items[] = [
                'product_id' => $request->item_name[$i],
                'qty' => $qty,
                'price' => $request->order_item_price[$i],
                'total_price' => $request->order_item_final_amount[$i],
                'purchase_id' => $id,
            ];
            if ($request->item_sl[$i] == 1) {
                for ($j = 0; $j < $qty; $j++) {
                    $stock_items[] = [
                        'product_id' => $request->item_name[$i],
                        'qty' => 1,
                        'price' => $request->order_item_price[$i],
                        'total_price' => $request->order_item_price[$i],
                        'serial' => $this->randomString(),
                        'purchase_id' => $id,
                    ];
                }
            } else {
                $stock_items[] = [
                    'product_id' => $request->item_name[$i],
                    'qty' => $request->order_item_quantity[$i],
                    'price' => $request->order_item_price[$i],
                    'total_price' => $request->order_item_final_amount[$i],
                    'serial' => $request->item_sku[$i],
                    'purchase_id' => $id,
                ];
            }
            $product = Product::find($request->item_name[$i]);
            $product_inputs['stock'] = $product->stock + $qty;
            $product->update($product_inputs);
        }
        if (PurchaseItem::insert($purchase_items) && StockItem::insert($stock_items)) {
            Session::flash('message', 'Items Added Successful!');
            Session::flash('m-class', 'alert-success');
            return redirect()->route('purchases');
        } else {
            Session::flash('message', 'Items Add Failed!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->back();
        }
    }


    public function purchaseShow($id)
    {
        $role_id = Auth::user()->roleId;
        if ($role_id == 3) {
            $purchases = PurchaseItem::with(['purchase'])->whereHas("purchase", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->where('purchase_id', $id)->get();
        } else {
            $purchases = PurchaseItem::with(['purchase'])->whereHas("purchase", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId);
            })->where('purchase_id', $id)->get();
        }

        if ($purchases != '') {
            $pr = Purchase::find($id);
            return view('admin.pages.purchase_view', compact('pr', 'role_id', 'purchases'));

        } else {
            Session::flash('message', 'Purchase Not Found!');
            Session::flash('m-class', 'alert-danger');
            return redirect()->route('receipt.index');
        }
    }

    public function inventoryMaintain()
    {
        $role_id = Auth::user()->roleId;
        if ($role_id == 3) {
            $stocks = StockItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->get();

            $ugases = UsedItem::with(['product'])->whereHas("product", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->get();
            $accounts = Account::where("branchId", Auth::user()->branchId)->get();
        } else {
            $stocks = StockItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
            })->get();

            $ugases = UsedItem::with(['product'])->whereHas("product", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
            })->get();
            $accounts = Account::where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId)->get();
        }

        return view('admin.pages.inventory_maintain', compact('stocks', 'ugases', 'accounts'));
    }

    public function inventorySelectStockProduct(Request $request)
    {
        $product = StockItem::with('product')->where('serial', $request->serial)->first();
        return response()->json($product);
    }

    public function inventorySelectUsedProduct(Request $request)
    {
        $product = UsedItem::with('product')->where('serial', $request->serial)->first();
        return response()->json($product);
    }

    public function inventoryStockMaintain(Request $request)
    {
        $this->validate($request, [
            'stock_serial' => 'required',
            'stock_name' => 'required',
            'stock_qty' => 'required|numeric',
            'stock_maintain_to' => 'required',
            'stock_amount' => 'required_if:stock_maintain_to,==,Sell|nullable',
            'stock_account' => 'required_if:stock_maintain_to,==,Sell|required_if:stock_maintain_to,==,Refund|nullable',
            'stock_comment' => 'required_if:stock_maintain_to,==,Use|required_if:stock_maintain_to,==,Refund|required_if:stock_maintain_to,==,Loss|max:191'
        ]);

        $stock_item = StockItem::where('serial', $request->stock_serial)->first();
        if ($stock_item->qty < $request->stock_qty) {
            return redirect()->back()->withErrors([
                'stock_qty' => $stock_item->qty . ' Qty In Stock'
            ]);
        }
        $item_price = $stock_item->total_price / $stock_item->qty;
        $item_total_price = $item_price * $request->stock_qty;
        $stock_inputs = [
            'qty' => $stock_item->qty - $request->stock_qty,
            'price' => $stock_item->price - $item_price,
            'total_price' => $stock_item->total_price - $item_total_price,
        ];
        $stock_item->update($stock_inputs);
        $product = Product::find($stock_item->product_id);
        $pr_inputs['stock'] = $product->stock - $request->stock_qty;
        if ($request->stock_maintain_to == 'Sell') {
            $account = Account::find($request->stock_account);
            $account->update(['account_balance' => $account->account_balance + $request->stock_amount]);
            $tr_inputs = [
                'account_id' => $account->id,
                'tr_type' => 'Product Sell',
                'tr_category' => 'Income',
                'tr_amount' => $request->stock_amount,
                'cr' => $request->stock_amount,
                'user_id' => Auth::user()->id,
                'branchId' => Auth::user()->branchId,
                'resellerId' => Auth::user()->resellerId
            ];
            Transaction::create($tr_inputs);
            $inputs = [
                'product_id' => $stock_item->product_id,
                'qty' => $request->stock_qty,
                'total_sell_price' => $request->stock_amount,
                'serial' => $stock_item->serial,
                'comment' => $request->stock_comment
            ];
            SoldItem::create($inputs);
            $pr_inputs['sold'] = $product->sold + $request->stock_qty;

        } else if ($request->stock_maintain_to == 'Use') {
            $inputs = [
                'product_id' => $stock_item->product_id,
                'qty' => $request->stock_qty,
                'price' => $item_price,
                'total_price' => $item_total_price,
                'serial' => $stock_item->serial,
                'comment' => $request->stock_comment
            ];
            UsedItem::create($inputs);
            $pr_inputs['used'] = $product->used + $request->stock_qty;
        } else if ($request->stock_maintain_to == 'Refund') {
            $account = Account::find($request->stock_account);
            $account->update(['account_balance' => $account->account_balance + $item_total_price]);
            $tr_inputs = [
                'account_id' => $account->id,
                'tr_type' => 'Product Refund',
                'tr_category' => 'Income',
                'tr_amount' => $item_total_price,
                'cr' => $item_total_price,
                'user_id' => Auth::user()->id,
                'branchId' => Auth::user()->branchId,
                'resellerId' => Auth::user()->resellerId
            ];
            Transaction::create($tr_inputs);
            $inputs = [
                'product_id' => $stock_item->product_id,
                'qty' => $request->stock_qty,
                'price' => $item_price,
                'total_price' => $item_total_price,
                'serial' => $stock_item->serial,
                'comment' => $request->stock_comment
            ];
            RefundItem::create($inputs);
            $pr_inputs['refund'] = $product->refund + $request->stock_qty;
        } else {
            $inputs = [
                'product_id' => $stock_item->product_id,
                'qty' => $request->stock_qty,
                'price' => $item_price,
                'total_price' => $item_total_price,
                'serial' => $stock_item->serial,
                'comment' => $request->stock_comment
            ];
            LostItem::create($inputs);
            $pr_inputs['lost'] = $product->lost + $request->stock_qty;
        }
        $product->update($pr_inputs);
        Session::flash('message', 'Product Maintain Successful!');
        Session::flash('m-class', 'alert-success');
        return redirect()->route('inventory.maintain');

    }

    public function inventoryUsedMaintain(Request $request)
    {
        $this->validate($request, [
            'use_serial' => 'required',
            'use_name' => 'required',
            'use_qty' => 'required|numeric',
            'use_maintain_to' => 'required',
            'use_amount' => 'required_if:use_maintain_to,==,Sell|nullable',
            'use_account' => 'required_if:use_maintain_to,==,Sell|required_if:use_maintain_to,==,Refund|nullable',
            'use_comment' => 'required_if:use_maintain_to,!=,Sell|max:191'
        ]);

        $used_item = UsedItem::where('serial', $request->use_serial)->first();
        if ($used_item->qty < $request->use_qty) {
            return redirect()->back()->withErrors([
                'use_qty' => $used_item->qty . ' Qty In Stock'
            ]);
        }
        $item_price = $used_item->total_price / $used_item->qty;
        $item_total_price = $item_price * $request->use_qty;
        if ($request->use_qty == $used_item->qty) {
            $used_item->delete();
        } else {
            $used_inputs = [
                'qty' => $used_item->qty - $request->use_qty,
                'price' => $used_item->price - $item_price,
                'total_price' => $used_item->total_price - $item_total_price,
            ];
            $used_item->update($used_inputs);
        }
        $product = Product::find($used_item->product_id);
        $pr_inputs['used'] = $product->used - $request->stock_qty;

        if ($request->use_maintain_to == 'Sell') {
            $account = Account::find($request->use_account);
            $account->update(['account_balance' => $account->account_balance + $request->use_amount]);
            $tr_inputs = [
                'account_id' => $account->id,
                'tr_type' => 'Product Sell',
                'tr_category' => 'Income',
                'tr_amount' => $request->use_amount,
                'cr' => $request->use_amount,
                'user_id' => Auth::user()->id,
                'branchId' => Auth::user()->branchId,
                'resellerId' => Auth::user()->resellerId
            ];
            Transaction::create($tr_inputs);
            $inputs = [
                'product_id' => $used_item->product_id,
                'qty' => $request->stock_qty,
                'total_sell_price' => $request->use_amount,
                'serial' => $used_item->serial,
                'comment' => $request->use_comment
            ];
            SoldItem::create($inputs);
            $pr_inputs['sold'] = $product->sold + $request->use_qty;
        } else if ($request->use_maintain_to == 'Stock') {
            $stock_item = StockItem::where('serial', $request->use_serial)->first();
            $inputs = [
                'qty' => $stock_item->qty + $request->use_qty,
                'price' => $stock_item->price + $item_price,
                'total_price' => $stock_item->total_price + $item_total_price,
            ];
            $stock_item->update($inputs);
            $pr_inputs['stock'] = $product->stock + $request->use_qty;
        } else if ($request->use_maintain_to == 'Refund') {
            $account = Account::find($request->use_account);
            $account->update(['account_balance' => $account->account_balance + $item_total_price]);
            $tr_inputs = [
                'account_id' => $account->id,
                'tr_type' => 'Product Refund',
                'tr_category' => 'Income',
                'tr_amount' => $item_total_price,
                'cr' => $item_total_price,
                'user_id' => Auth::user()->id,
                'branchId' => Auth::user()->branchId,
                'resellerId' => Auth::user()->resellerId
            ];
            Transaction::create($tr_inputs);
            $inputs = [
                'product_id' => $used_item->product_id,
                'qty' => $request->use_qty,
                'price' => $item_price,
                'total_price' => $item_total_price,
                'serial' => $used_item->serial,
                'comment' => $request->use_comment
            ];
            RefundItem::create($inputs);
            $pr_inputs['refund'] = $product->refund + $request->use_qty;
        } else {
            $inputs = [
                'product_id' => $used_item->product_id,
                'qty' => $request->use_qty,
                'price' => $item_price,
                'total_price' => $item_total_price,
                'serial' => $used_item->serial,
                'comment' => $request->use_comment
            ];
            LostItem::create($inputs);
            $pr_inputs['lost'] = $product->lost + $request->use_qty;
        }
        $product->update($pr_inputs);
        Session::flash('message', 'Product Maintain Successful!');
        Session::flash('m-class', 'alert-success');
        return redirect()->route('inventory.maintain');
    }

    public function stockItem()
    {
        $role_id = Auth::user()->roleId;
        if ($role_id == 3) {
            $items = StockItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        } else {
            $items = StockItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        }
        $brshow = false;
        $page_title = 'Stock Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function stockItemBranch()
    {
        $role_id = Auth::user()->roleId;
        $items = StockItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
            $query->where('resellerId', Auth::user()->resellerId)->whereNotNull("branchId");
        })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        $brshow = true;
        $page_title = 'Branch Stock Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function usedItem()
    {
        $role_id = Auth::user()->roleId;
        if ($role_id == 3) {
            $items = UsedItem::with(['product'])->whereHas("product", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        } else {
            $items = UsedItem::with(['product'])->whereHas("product", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        }
        $brshow = false;
        $page_title = 'Used Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function usedItemBranch()
    {
        $role_id = Auth::user()->roleId;
        $items = UsedItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
            $query->where('resellerId', Auth::user()->resellerId)->whereNotNull("branchId");
        })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        $brshow = true;
        $page_title = 'Branch Used Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function soldItem()
    {
        $role_id = Auth::user()->roleId;
        if ($role_id == 3) {
            $items = SoldItem::with(['product'])->whereHas("product", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        } else {
            $items = SoldItem::with(['product'])->whereHas("product", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        }
        $brshow = false;
        $page_title = 'Sold Item List';
        return view('admin.pages.item_list_sold', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function soldItemBranch()
    {
        $role_id = Auth::user()->roleId;
        $items = SoldItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
            $query->where('resellerId', Auth::user()->resellerId)->whereNotNull("branchId");
        })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        $brshow = true;
        $page_title = 'Branch Sold Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function refundItem()
    {
        $role_id = Auth::user()->roleId;
        if ($role_id == 3) {
            $items = RefundItem::with(['product'])->whereHas("product", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        } else {
            $items = RefundItem::with(['product'])->whereHas("product", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        }
        $brshow = false;
        $page_title = 'Refund Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function refundItemBranch()
    {
        $role_id = Auth::user()->roleId;
        $items = RefundItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
            $query->where('resellerId', Auth::user()->resellerId)->whereNotNull("branchId");
        })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        $brshow = true;
        $page_title = 'Branch Refund Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function lostItem()
    {
        $role_id = Auth::user()->roleId;
        if ($role_id == 3) {
            $items = LostItem::with(['product'])->whereHas("product", function ($query) {
                $query->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        } else {
            $items = LostItem::with(['product'])->whereHas("product", function ($query) {
                $query->where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId);
            })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        }
        $brshow = false;
        $page_title = 'Lost Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function LostItemBranch()
    {
        $role_id = Auth::user()->roleId;
        $items = LostItem::where('qty', '!=', 0)->with(['product'])->whereHas("product", function ($query) {
            $query->where('resellerId', Auth::user()->resellerId)->whereNotNull("branchId");
        })->orderBy('product_id', 'ASC')->orderBy('qty', 'DESC')->get();
        $brshow = true;
        $page_title = 'Branch Lost Item List';
        return view('admin.pages.item_list', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function itemsDetail(){
        $role_id = Auth::user()->roleId;
        $items = Product::where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId)->orderBy('id', 'ASC')->get();
        $brshow = false;
        $page_title = 'Items Detail';
        return view('admin.pages.item_list_details', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function itemsDetailBranch(){
        $role_id = Auth::user()->roleId;
        $items = Product::where('resellerId', Auth::user()->resellerId)->where("branchId", Auth::user()->branchId)->orderBy('id', 'ASC')->get();
        $brshow = true;
        $page_title = 'Items Detail';
        return view('admin.pages.item_list_details', compact('items', 'brshow', 'role_id', 'page_title'));
    }

    public function randomString()
    {
        $random_string = strtoupper(substr(md5(time() . rand(10000, 99999)), 0, 10));
        $is_unique = false;

        while (!$is_unique) {
            $result = StockItem::where('serial', $random_string)->first();
            if ($result == false) {
                $is_unique = true;
            } else {
                $random_string = strtoupper(substr(md5(time() . rand(10000, 99999)), 0, 10));
                $is_unique = false;
            }
        }
        return $random_string;
    }
}