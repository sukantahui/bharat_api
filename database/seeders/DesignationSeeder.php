<?php

namespace Database\Seeders;
use App\Models\Designation;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Designation::insert([
            ['designation_name' => 'Administrator'],
            ['designation_name' => 'Developer'],
            ['designation_name' => 'Owner'],
            ['designation_name' => 'Manager'],
            ['designation_name' => 'Teacher'],
            ['designation_name' => 'Manager Sale'],
            ['designation_name' => 'Worker'],
            ['designation_name' => 'Student'],
        ]);
    }
}
