<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for ($i=1; $i < 20; $i++) { 
            DB::table('book')->insert([
                'subjek' => $faker->word,
                'kuantitas' => $faker->randomDigitNot(0),
            ]);
        }
    }
}
