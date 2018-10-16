<?php

use App\Models\Coin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('symbol')->unique();
            $table->string('name');
            $table->integer('crr');
            $table->decimal('volume',300, 0);
            $table->decimal('reserve_balance',300, 0);
            $table->string('creator');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Coin::create([
            'symbol' => env('MINTER_BASE_COIN', 'BIP'),
            'name' => 'Minter Coin',
            'crr' => 0,
            'volume' => 0,
            'reserve_balance' => 0,
            'creator' => '',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
}
