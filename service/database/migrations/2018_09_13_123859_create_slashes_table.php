<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('slashes', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('block_height');
            $table->string('coin');
            $table->decimal('amount', 300, 0);
            $table->string('address');
            $table->string('validator_pk');
            $table->index('address');
            $table->index('validator_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('slashes');
    }
}
