<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->string('account_type');
            $table->string('account_number')->nullable();
            $table->double('account_balance')->default(0);
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
        Schema::dropIfExists('accounts');
    }
}
