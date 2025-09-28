<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->char('qty');
            $table->double('price');
            $table->double('total_price');
            $table->char('serial');

            $table->foreignId('purchase_id')
                ->references('id')
                ->on('purchases')
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
        Schema::dropIfExists('stock_items');
    }
}
