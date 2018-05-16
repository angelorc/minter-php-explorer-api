<?php

use App\Models\Validator;
use Illuminate\Database\Seeder;

class ValidatorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            Validator::create([
                'name' => 'TestValidator',
                'address' => md5(random_bytes(10)),
                'public_key' => md5(random_bytes(10)),
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
