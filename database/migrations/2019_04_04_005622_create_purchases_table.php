<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->double('price');
            $table->date('purchase_date');
            $table->foreignId('tr_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreignId('branchId')->nullable()
                ->references('branchId')
                ->on('branches')
                ->onDelete('set null');

            $table->foreignId('resellerId')->nullable()
                ->references('resellerId')
                ->on('resellers')
                ->onDelete('cascade');
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
        Schema::dropIfExists('purchases');
    }
}
