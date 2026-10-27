<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gender::insert([
            ['gender_name' => 'Male'],
            ['gender_name' => 'Female'],
            ['gender_name' => 'Other'],
        ]);
    }
}
