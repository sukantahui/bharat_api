<?php

namespace Database\Seeders;
use App\Models\Department;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::insert([
            ['department_name' => 'Administration'],
            ['department_name' => 'Development'],
            ['department_name' => 'Teaching'],
            ['department_name' => 'Office'],
        ]);
    }
}
