<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('server_password')->nullable();
            $table->string('phone', 20)->nullable();
            $table->char('house_no',10)->nullable()->default(0);
            $table->char('road_no',10)->nullable()->default(0);
            $table->char('address')->nullable();
            $table->char('thana');
            $table->char('district');
            $table->text('user_image')->nullable();
            $table->char('clientNid')->nullable();
            $table->integer('active');
            $table->dateTime('last_login')->nullable();
            $table->foreignId('plan_id')->references('id')
                ->on('plans')->onDelete('set null');
            $table->ipAddress('client_ip')->nullable();
            $table->date('start_transaction')->nullable();
            $table->dateTime('expiration')->nullable();
            $table->date('server_active_date')->nullable();
            $table->date('server_inactive_date')->nullable();
            $table->integer('server_status');
            $table->string('status')->default('Off');
            $table->foreignId('distribution_id')->references('id')
                ->on('distributions')->onDelete('cascade');
            $table->char('type_of_connection', 50);
            $table->char('type_of_connectivity', 50);
            $table->char('olt_type', 50);
            $table->double('due')->default(0);
            $table->double('balance')->default(0);
            $table->double('discount')->default(0);
            $table->double('charge')->default(0);
            $table->double('otc_charge')->default(0);
            $table->string('client_photo', 512)->nullable();
            $table->json('other_documents')->nullable();

            $table->text('note')->nullable();
            $table->foreignId('resellerId')->nullable()->references('resellerId')
                ->on('resellerId')->onDelete('cascade');
            $table->foreignId('branchId')->nullable()->references('branchId')
                ->on('branches')->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
