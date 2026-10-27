<?php

namespace Database\Seeders;
use App\Models\Employee;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::insert([
            ['employee_name' => 'Sukanta Hui', 'mobile' => '9830371685', 'email' => 'sukantahui@gmail.com', 'department_id' => 1, 'designation_id' => 1],
            ['employee_name' => 'Tanusree Hui', 'mobile' => '9051724200', 'email' => 'tanusreehui@gmail.com', 'department_id' => 3, 'designation_id' => 5],
            ['employee_name' => 'Chandan Das', 'mobile' => '9836987171', 'email' => 'chandan.dasy2k10@gmail.com', 'department_id' => 1, 'designation_id' => 1],
            ['employee_name' => 'Ritaja Ghosh', 'mobile' => '7003310220', 'email' => 'ritajaghosh@gmail.com', 'department_id' => 4, 'designation_id' => 4]
        ]);
    }
}
