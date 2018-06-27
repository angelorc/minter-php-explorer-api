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
            $table->string('from');
            $table->string('to');
            $table->string('coin');
            $table->string('hash');
            $table->string('payload')->nullable();
            $table->string('service_data')->nullable();
            $table->string('pub_key')->nullable();
            $table->string('address')->nullable();
            $table->string('from_coin_symbol')->nullable();
            $table->string('to_coin_symbol')->nullable();
            $table->string('raw_check')->nullable();
            $table->string('proof')->nullable();
            $table->string('name')->nullable();
            $table->string('symbol')->nullable();
            $table->decimal('fee', 50, 0);
            $table->decimal('value', 50, 18);
            $table->decimal('stake', 50, 0)->nullable();
            $table->decimal('commission', 50, 0)->nullable();
            $table->decimal('initial_amount', 50, 0)->nullable();
            $table->decimal('initial_reserve', 50, 0)->nullable();
            $table->decimal('constant_reserve_ratio', 50, 0)->nullable();
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