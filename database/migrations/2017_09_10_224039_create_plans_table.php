<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name')->unique();
            $table->enum('type', ['PPPOE', 'Hotspot', 'IP']);
            $table->decimal('plan_price')->default(0);
            $table->integer('duration', false, true)->nullable();
            $table->enum('duration_unit', [1, 2]);
            $table->integer('shared_users', false, true)->nullable();
            $table->foreignId('server_id')->references('id')
                ->on('servers')->onDelete('cascade');
            $table->foreignId('bandwidth_id')->references('id')
                ->on('bandwidths')->onDelete('cascade');
            $table->foreignId('pool_id')->references('id')
                ->on('pools')->onDelete('cascade');
            $table->foreignId('branchId')->references('branchId')
                ->on('branches')->onDelete('cascade');
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
        Schema::dropIfExists('plans');
    }
}
