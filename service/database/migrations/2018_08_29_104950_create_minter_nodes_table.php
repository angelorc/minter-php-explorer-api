<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMinterNodesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('minter_nodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host', 0);
            $table->integer('port')->default(8841);
            $table->decimal('ping', 7,3)->default(0);
            $table->boolean('is_secure')->default(false);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_excluded')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('minter_nodes');
    }
}
