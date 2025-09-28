<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpansesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expanses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('cat_id', false, true);
            $table->double('amount');
            $table->date('date');

            $table->foreignId('cat_id')
                ->references('id')
                ->on('expanse_categories')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreignId('tr_id')
                ->references('id')
                ->on('transactions')
                ->onDelete('cascade');

            $table->foreignId('resellerId')->nullable()
                ->references('resellerId')
                ->on('resellers')
                ->onDelete('cascade');
            $table->foreignId('branchId')->nullable()
                ->references('branchId')
                ->on('branches')
                ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expanses');
    }
}
