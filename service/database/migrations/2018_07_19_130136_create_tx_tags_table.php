<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTxTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tx_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('transaction_id');
            $table->string('key');
            $table->string('value');

            $table->foreign('transaction_id')->references('id')->on('transactions');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_tags');
    }
}
