<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->double('pay_amount');
            $table->date('date');
            $table->foreign('loan_payer_id')
                ->references('id')
                ->on('loan_payers')
                ->onDelete('set null');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('tr_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');

            $table->foreignId('resellerId')->nullable()
                ->references('resellerId')
                ->on('resellers')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
