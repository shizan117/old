<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invests', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount');
            $table->date('date');
            $table->foreignId('investor_id')->references('id')
                ->on('investors')->onDelete('set null');

            $table->foreignId('user_id')->references('id')
                ->on('users')->onDelete('set null');

            $table->foreignId('tr_id')->references('id')
                ->on('transactions')->onDelete('cascade');

            $table->foreignId('resellerId')->nullable()->references('resellerId')
                ->on('resellers')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invests');
    }
}
