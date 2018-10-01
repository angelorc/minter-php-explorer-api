<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            $table->decimal('block_time', 20, 9);
            $table->string('hash');
            $table->decimal('block_reward', 50, 0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::unprepared("
          CREATE INDEX blocks_date_trunc_day_index 
            ON blocks (date_trunc('day', created_at at time zone 'UTC'));
        ");
        DB::unprepared("
          CREATE INDEX blocks_date_trunc_hour_index 
            ON blocks (date_trunc('hour', created_at at time zone 'UTC'));
        ");
        DB::unprepared("
          CREATE INDEX blocks_date_trunc_minute_index 
            ON blocks (date_trunc('minute', created_at at time zone 'UTC'));
        ");

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
