<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResellerPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reseller_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resellerId')
                ->references('resellerId')
                ->on('resellers')
                ->onDelete('cascade');
            $table->integer('bill_month', false)->nullable();
            $table->integer('bill_year', false)->nullable();
            $table->decimal('price')->default(0)->nullable();
            $table->decimal('advance_payment')->default(0)->nullable();
            $table->decimal('pre_due')->default(0)->nullable();
            $table->decimal('total')->default(0)->nullable();
            $table->decimal('vat')->default(0)->nullable();
            $table->decimal('sub_total')->default(0)->nullable();
            $table->decimal('paid_amount')->default(0)->nullable();
            $table->decimal('new_paid')->default(0)->nullable();
            $table->decimal('paid_from_advance')->default(0)->nullable();
            $table->decimal('due')->default(0)->nullable();

            $table->foreignId('tr_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');

            $table->foreignId('user_id')->nullable()
                ->references('id')
                ->on('users')
                ->onDelete('set null');

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
        Schema::dropIfExists('reseller_payments');
    }
}
