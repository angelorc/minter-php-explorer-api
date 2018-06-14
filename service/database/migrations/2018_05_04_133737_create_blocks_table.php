<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('height')->unique();
            $table->timestampTz('timestamp');
            $table->integer('tx_count');
            $table->integer('size');
            $table->integer('block_time');
            $table->string('hash');
            $table->decimal('block_reward', 30, 0);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
}
