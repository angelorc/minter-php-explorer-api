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
            $table->decimal('timestamp', 20, 10);
            $table->integer('tx_count');
            $table->integer('size');
            $table->decimal('block_time', 12, 9);
            $table->string('hash');
            $table->decimal('block_reward', 50, 0);
            $table->timestampsTz();
            $table->softDeletesTz();
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
