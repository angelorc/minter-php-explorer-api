<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMinterNodesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('minter_nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host', 0);
            $table->integer('port')->default(8841);
            $table->decimal('ping', 7,3)->default(0);
            $table->string('version')->nullable();
            $table->boolean('is_secure')->default(false);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_local')->default(false);
            $table->boolean('is_excluded')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('minter_nodes');
    }
}
