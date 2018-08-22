<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValidatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('validators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->default('');
            $table->decimal('accumulated_reward', 50, 0);
            $table->bigInteger('absent_times');
            $table->string('candidate_address');
            $table->decimal('total_stake', 50, 0);
            $table->string('pub_key')->unique();
            $table->bigInteger('commission');
            $table->smallInteger('status');
            $table->bigInteger('created_at_block');
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
        Schema::dropIfExists('validators');
    }
}
