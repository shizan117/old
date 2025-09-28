<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use LogsActivity,SoftDeletes;

    public function tapActivity(Activity $activity)
    {
        $_subject_info = Invoice::withTrashed()->find($activity->subject_id);
        if(!empty($_subject_info)){
            $activity->properties = $activity->properties->merge([
                'subject_info' => "Invoice of - {$_subject_info->client->client_name} ({$_subject_info->client->username})",
                'ip' => \Request::ip(),
            ]);
        }
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'client.client_name', 'bandwidth', 'bill_month', 'bill_year', 'buy_price', 'plan_price', 'service_charge',
                'charge_for','otc_charge','discount', 'vat', 'sub_total',
                'paid_amount', 'due',  'duration', 'duration_unit','note', 'branch.branchName', 'reseller.resellerName'])
            ->useLogName('invoice')->logOnlyDirty();
    }

    protected $fillable = [
        'client_id', 'bandwidth', 'bill_month', 'bill_year', 'buy_price', 'plan_price', 'service_charge',
        'charge_for', 'total','discount', 'all_total', 'vat', 'sub_total',
        'paid_amount', 'due','otc_charge',  'duration', 'duration_unit','note','branchId', 'resellerId'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branchId', 'branchId');
    }

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'resellerId', 'resellerId');
    }

    public function createCurrentMonthInvoice($client)
    {
        $have_invoice = Invoice::withTrashed()->where([
            ['client_id', '=', $client->id],
            ['bill_month', '=', date('m')],
            ['bill_year', '=', date('Y')]
        ])->first();
        if (empty($have_invoice)) {

            if ($client->resellerId != '') {
                $reseller_plan = ResellerPlan::where([
                    ["plan_id", $client->plan_id],
                    ["resellerId", $client->resellerId]
                ])->first();

                $p_price = $reseller_plan->reseller_sell_price;
                $discount = $client->discount;
                $charge = $client->charge;
                $sub_total = $p_price + $charge - $discount;

                $reseller = Reseller::find($client->resellerId);
                $vatRate = $reseller->vat_rate;

                // ========== BUY PRICE CALCULATION ===========
                $invoice_system = Config::where('config_title', 'invoice_system')->first()->value;
                if ($invoice_system == 'fixed') {
                    $now = Carbon::now();
                    $daysInMonth = $now->daysInMonth;
                    $days_remaining = $now->diffInDays(Carbon::parse(date('Y-m-t 23:59:59')));
                    //Adjusting 1 day
                    $days_remaining++;
                    if ($client->plan->duration_unit == '2') {
                        $buy_price = ($reseller_plan->sell_price / $client->plan->duration) * $days_remaining;
                    } else {
                        $buy_price = ($reseller_plan->sell_price / ($daysInMonth * $client->plan->duration)) * $days_remaining;
                    }
                } else {
                    $buy_price = $reseller_plan->sell_price;
                }

                if ($buy_price <= 0) {
                    return 0;
                }

                $price = ceil((($sub_total) * 100) / (100 + $vatRate));
                $vat = $sub_total - $price;
                $plan_price = $p_price - $vat;

            }
            else {
                $p_price = $client->plan->plan_price;
                $discount = $client->discount;
                $charge = $client->charge;
                $sub_total = $p_price + $charge - $discount;

                $vatData = Config::where('config_title', 'vatRate')->first();
                $vatRate = $vatData->value;
                $buy_price = 0;

                $price = ceil((($sub_total) * 100) / (100 + $vatRate));
                $vat = $sub_total - $price;
                $plan_price = $p_price - $vat;
            }

            $inputs = [
                'client_id' => $client->id,
                'plan_id' => $client->plan->id,
                'bandwidth' => $client->plan->bandwidth->bandwidth_name,
                'bill_month' => date('m'),
                'bill_year' => date('Y'),
                'buy_price' => $buy_price,
                'plan_price' => $plan_price,
                'service_charge' => $charge,
                'otc_charge' => $client->otc_charge,
                'total' => $plan_price+$client->otc_charge+$client->charge,
                'discount' => $discount,
                'all_total' => $sub_total - $vat + $client->otc_charge,
                'vat' => $vat,
                'sub_total' => $sub_total+ $client->otc_charge,
                'paid_amount' => 0,
                'due' => $sub_total +$client->otc_charge,
                'duration' => $client->plan->duration,
                'duration_unit' => $client->plan->duration_unit,
                'branchId' => $client->branchId,
                'resellerId' => $client->resellerId,
            ];

            if(Invoice::create($inputs)) return $sub_total+$client->otc_charge;
            else return 0;
        }
    }
}
