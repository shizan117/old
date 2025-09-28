<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->references('id')
                ->on('clients')->onDelete('set null');
            $table->string('bandwidth')->nullable();
            $table->unsignedInteger('bill_month')->nullable();
            $table->unsignedInteger('bill_year')->nullable();
            $table->double('plan_price')->default(0)->nullable();
            $table->double('service_charge')->default(0)->nullable();
            $table->double('otc_charge')->default(0)->nullable();
            $table->double('advance_payment')->default(0)->nullable();
            $table->double('pre_due')->default(0)->nullable();
            $table->double('total')->default(0)->nullable();
            $table->double('discount')->default(0)->nullable();
            $table->double('all_total')->default(0)->nullable();
            $table->double('vat')->default(0)->nullable();
            $table->double('sub_total')->default(0)->nullable();
            $table->double('paid_amount')->default(0)->nullable();
            $table->double('new_paid')->default(0)->nullable();
            $table->double('paid_from_advance')->default(0)->nullable();
            $table->double('due')->default(0)->nullable();
            $table->double('pre_balance')->default(0)->nullable();
            $table->foreignId('tr_id')->references('id')
                ->on('transactions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->references('id')
                ->on('users')->onDelete('set null');
            $table->date('payment_date');
            $table->foreignId('branchId')->nullable()->references('branchId')
                ->on('branches')->onDelete('cascade');
            $table->foreignId('resellerId')->nullable()->references('resellerId')
                ->on('resellerId')->onDelete('cascade');
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
        Schema::dropIfExists('client_payments');
    }
}
