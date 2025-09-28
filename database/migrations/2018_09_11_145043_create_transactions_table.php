<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->references('id')->on('accounts')
                ->onDelete('set null');
            $table->string('tr_type');
            $table->string('tr_category');
            $table->double('tr_amount')->default(0);
            $table->double('tr_vat')->default(0)->nullable();
            $table->double('charge')->default(0)->nullable();
            $table->string('payer')->nullable();
            $table->string('payee')->nullable();
            $table->double('dr')->default(0);
            $table->double('cr')->default(0);
            $table->string('pay_id')->nullable();
            $table->string('bkash_trxID')->nullable();
            $table->foreignId('user_id')->nullable()->references('id')
                ->on('users')->onDelete('set null');
            $table->foreignId('resellerId')->nullable()->references('resellerId')
                ->on('resellers')->onDelete('cascade');
            $table->foreignId('branchId')->nullable()->references('branchId')
                ->on('branches')->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->references('id')
                ->on('invoices')->onDelete('set null');
            $table->foreignId('r_invoice_id')->nullable()->references('id')
                ->on('reseller_invoices')->onDelete('set null');
            $table->date('trans_date');
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
        Schema::dropIfExists('transactions');
    }
}
