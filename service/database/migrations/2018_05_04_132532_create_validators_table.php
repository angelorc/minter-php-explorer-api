<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->decimal('accumulated_reward', 300, 0);
            $table->bigInteger('absent_times');
            $table->string('address');
            $table->decimal('total_stake', 300, 0);
            $table->string('public_key')->unique();
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
