<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserTypeSeeder::class,
            DepartmentSeeder::class,
            DesignationSeeder::class,
            EmployeeSeeder::class,
            UserSeeder::class,
            GenderSeeder::class,
            StateDistrictSeeder::class,
            PuritySeeder::class,
            JewelleryCategorySeeder::class,
            ChartOfAccountsSeeder::class,
            BusinessProfileSeeder::class,
            PosTerminalSeeder::class,
        ]);
    }
}
