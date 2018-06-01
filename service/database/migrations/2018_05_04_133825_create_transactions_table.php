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
            $table->integer('validator_id');
            $table->integer('type');
            $table->integer('nonce');
            $table->integer('gas_price');
            $table->string('from');
            $table->string('to');
            $table->string('coin');
            $table->string('hash');
            $table->string('payload');
            $table->string('service_data');
            $table->integer('fee');
            $table->decimal('value', 30, 18);
            $table->timestampsTz();

            $table->foreign('block_id')->references('id')->on('blocks');
            $table->foreign('validator_id')->references('id')->on('validators');
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