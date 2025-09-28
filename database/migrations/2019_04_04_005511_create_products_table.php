<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->char('name');
            $table->char('unit');
            $table->foreignId('cat_id')
                ->references('id')
                ->on('product_categories')
                ->onDelete('cascade');
            $table->char('stock')->default(0)->nullable();
            $table->char('used')->default(0)->nullable();
            $table->char('sold')->default(0)->nullable();
            $table->char('lost')->default(0)->nullable();
            $table->char('refund')->default(0)->nullable();
            $table->enum('serial_type', [1,2]);

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
        Schema::dropIfExists('products');
    }
}
