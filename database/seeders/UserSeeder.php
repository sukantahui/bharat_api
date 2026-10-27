<?php

namespace Database\Seeders;
use App\Models\User;
use App\Models\UserType;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Sukanta
        User::updateOrCreate(
            ['email' => 'sukantahui'],
            [
                'password' => Hash::make('Cnat@1977'),
                'user_type_id' => 1,
                'employee_id' => 1
            ]
        );

        // Teacher
        User::updateOrCreate(
            ['email' => 'tanusreehui'],
            [
                'password' => Hash::make('Cnat@1977'),
                'user_type_id' => 5,
                'employee_id' => 2
            ]
        );

        // Admin Chandan
        User::updateOrCreate(
            ['email' => 'chandandas'],
            [
                'password' => Hash::make('Cnat@1977'),
                'user_type_id' => 1,
                'employee_id' => 3
            ]
        );

        // Developer
        User::updateOrCreate(
            ['email' => 'developer@gmail.com'],
            [
                'password' => Hash::make('Cnat@1977'),
                'user_type_id' => 2,
                'employee_id' => 2
            ]
        );

        // Owner
        User::updateOrCreate(
            ['email' => 'owner@gmail.com'],
            [
                'password' => Hash::make('Cnat@1977'),
                'user_type_id' => 3,
                'employee_id' => 1
            ]
        );

        // Manager
        User::updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'password' => Hash::make('Cnat1977'),
                'user_type_id' => 4,
                'employee_id' => 3
            ]
        );

        // Ritaja Manager
        User::updateOrCreate(
            ['email' => 'ritajaghosh@gmail.com'],
            [
                'password' => Hash::make('Ghosh@2000'),
                'user_type_id' => 4,
                'employee_id' => 4
            ]
        );

        // Counter Billing Staff (Non-Admin Staff Role)
        $staffUserType = UserType::firstOrCreate(['user_type_name' => 'Staff']);
        $staffDesig = Designation::firstOrCreate(['designation_name' => 'Sales & Billing Staff']);
        $staffEmp = Employee::firstOrCreate(
            ['employee_name' => 'Counter Billing Staff'],
            [
                'employee_short_name' => 'Staff-01',
                'mobile' => '9830099999',
                'email' => 'staff@bharatjewellery.com',
                'department_id' => 4,
                'designation_id' => $staffDesig->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff'],
            [
                'password' => Hash::make('Cnat@1977'),
                'user_type_id' => $staffUserType->id,
                'employee_id' => $staffEmp->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@gmail.com'],
            [
                'password' => Hash::make('Cnat@1977'),
                'user_type_id' => $staffUserType->id,
                'employee_id' => $staffEmp->id,
            ]
        );
    }
}
