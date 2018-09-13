<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('block_height');
            $table->string('role');
            $table->decimal('amount', 300, 0);
            $table->string('address');
            $table->string('validator_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
}
