<?php

namespace Database\Seeders;

use App\Models\Laptops;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaptopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Laptops::factory()->count(7)->create();
    }
}
