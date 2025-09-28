<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20);
            $table->text('user_image')->nullable();
            $table->integer('active', false);
            $table->integer('branchId', false)->nullable();
            $table->integer('resellerId', false)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('resellerId')
                ->references('resellerId')
                ->on('resellerId')
                ->onDelete('cascade');
            $table->foreign('branchId')
                ->references('branchId')
                ->on('branches')
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
        Schema::dropIfExists('users');
    }
}
