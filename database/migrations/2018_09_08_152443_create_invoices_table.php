<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->references('id')->on('clients')
                ->onDelete('set null');
            $table->string('bandwidth');
            $table->unsignedInteger('bill_month');
            $table->unsignedInteger('bill_year');
            $table->double('buy_price')->default(0)->nullable();
            $table->double('plan_price')->default(0)->nullable();
            $table->double('service_charge')->default(0)->nullable();
            $table->double('otc_charge')->default(0)->nullable();
            $table->text('charge_for')->nullable();
            $table->double('total')->default(0)->nullable();
            $table->double('discount')->default(0)->nullable();
            $table->double('all_total')->default(0)->nullable();
            $table->double('vat')->default(0)->nullable();
            $table->double('sub_total')->default(0)->nullable();
            $table->double('paid_amount')->default(0)->nullable();
            $table->double('due')->default(0)->nullable();
            $table->integer('duration', false);
            $table->integer('duration_unit', false);
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
        Schema::dropIfExists('invoices');
    }
}
