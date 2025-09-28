<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resellers', function (Blueprint $table) {
            $table->id('resellerId');
            $table->char('resellerName', 120)->unique();
            $table->text('resellerLocation')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('signature')->nullable();
            $table->string('business_name')->nullable();
            $table->string('prefix')->nullable();
            $table->string('notice')->nullable();
            $table->double('credit_limit')->default(0);
            $table->double('balance')->default(0);
            $table->double('due')->default(0);
            $table->date('exp_date')->nullable();
            $table->integer('c_exp_date')->default(7);
            $table->char('vat_rate')->default(0);
            $table->char('bkash_username')->nullable();
            $table->char('bkash_password')->nullable();
            $table->char('bkash_app_key')->nullable();
            $table->char('bkash_app_secret')->nullable();
            $table->char('bkash_charge')->nullable();
            $table->char('bkash_url')->nullable();
            $table->char('bkash_production_root_url')->nullable();
            $table->char('sms_api_url')->nullable();
            $table->char('sms_api_key')->nullable();
            $table->char('sms_masking_id')->nullable();
            $table->boolean('sms_is_active')->default(false);
            $table->char('sms_remainder')->nullable();
            $table->char('sms_payment')->nullable();
            $table->char('sms_disconnect')->nullable();
            $table->char('sms_invoice')->nullable();
            $table->char('sms_new_client')->nullable();
            $table->char('sms_custom')->nullable();
            $table->char('nagad_merchant_id')->nullable();
            $table->char('nagad_merchant_number')->nullable();
            $table->char('nagad_pg_public_key')->nullable();
            $table->char('nagad_merchant_private_key')->nullable();
            $table->char('nagad_charge')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resellers');
    }
}
