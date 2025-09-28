<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBandwidthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bandwidths', function (Blueprint $table) {
            $table->id();
            $table->string('bandwidth_name')->unique();
            $table->string('rate_down');
            $table->string('rate_down_unit');
            $table->string('rate_up');
            $table->string('rate_up_unit');
            $table->decimal('bandwidth_allocation_mb');
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
        Schema::dropIfExists('bandwidths');
    }
}
