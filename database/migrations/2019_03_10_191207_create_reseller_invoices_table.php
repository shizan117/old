<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResellerInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reseller_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resellerId')
                ->references('resellerId')
                ->on('resellers')
                ->onDelete('set null');
            $table->unsignedInteger('bill_month');
            $table->unsignedInteger('bill_year');
            $table->double('buy_price')->default(0)->nullable();
            $table->double('total')->default(0)->nullable();
            $table->double('vat')->default(0)->nullable();
            $table->double('discount')->default(0)->nullable();
            $table->double('sub_total')->default(0)->nullable();
            $table->double('paid_amount')->default(0)->nullable();
            $table->double('due')->default(0)->nullable();
            $table->integer('client_invoice')->nullable();
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
        Schema::dropIfExists('reseller_invoices');
    }
}
