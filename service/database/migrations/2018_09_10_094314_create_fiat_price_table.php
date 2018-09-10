<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFiatPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('fiat_price', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('coin_id');
            $table->integer('currency_id');
            $table->decimal('price', 50, 10);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('coin_id')->references('id')->on('coins');
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('fiat_price');
    }
}
