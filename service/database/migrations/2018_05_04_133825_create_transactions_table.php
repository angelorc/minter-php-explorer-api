<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('from');
            $table->string('to')->nullable();
            $table->string('hash');
            $table->string('pub_key')->nullable();
            $table->decimal('value', 300, 0)->nullable();
            $table->decimal('value_to_sell', 300, 0)->nullable();
            $table->decimal('value_to_buy', 300, 0)->nullable();
            $table->decimal('fee', 300, 0);
            $table->decimal('stake', 300, 0)->nullable();
            $table->decimal('commission', 300, 0)->nullable();
            $table->decimal('initial_amount', 300, 0)->nullable();
            $table->decimal('initial_reserve', 300, 0)->nullable();
            $table->decimal('constant_reserve_ratio', 300, 0)->nullable();
            $table->decimal('gas_wanted', 300, 0)->nullable();
            $table->decimal('gas_used', 300, 0)->nullable();
            $table->integer('gas_price');
            $table->string('gas_coin')->nullable();
            $table->string('coin')->nullable();
            $table->integer('nonce');
            $table->string('payload')->nullable();
            $table->string('service_data')->nullable();
            $table->string('address')->nullable();
            $table->string('coin_to_sell')->nullable();
            $table->string('coin_to_buy')->nullable();
            $table->text('raw_check')->nullable();
            $table->string('proof')->nullable();
            $table->string('name')->nullable();
            $table->string('log')->nullable();
            $table->boolean('status');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->foreign('block_id')->references('height')->on('blocks');
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