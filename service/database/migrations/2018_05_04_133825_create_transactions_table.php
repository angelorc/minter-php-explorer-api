<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('block_id');
            $table->integer('type');
            $table->integer('nonce');
            $table->integer('gas_price');
            $table->string('gas_coin')->nullable();
            $table->string('from');
            $table->string('hash');
            $table->boolean('status');
            $table->decimal('fee', 50, 0);
            $table->decimal('value', 50, 18)->nullable();
            $table->string('to')->nullable();
            $table->string('coin')->nullable();
            $table->string('payload')->nullable();
            $table->string('service_data')->nullable();
            $table->string('pub_key')->nullable();
            $table->string('address')->nullable();
            $table->string('coin_to_sell')->nullable();
            $table->string('coin_to_buy')->nullable();
            $table->string('raw_check')->nullable();
            $table->string('proof')->nullable();
            $table->string('name')->nullable();
            $table->string('symbol')->nullable();
            $table->decimal('stake', 50, 0)->nullable();
            $table->decimal('commission', 50, 0)->nullable();
            $table->decimal('initial_amount', 50, 0)->nullable();
            $table->decimal('initial_reserve', 50, 0)->nullable();
            $table->decimal('constant_reserve_ratio', 50, 0)->nullable();
            $table->decimal('gas_wanted', 50, 0)->nullable();
            $table->decimal('gas_used', 50, 0)->nullable();
            $table->string('log')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->foreign('block_id')->references('id')->on('blocks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
}