<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Admin',
            'Developer',
            'Owner',
            'Manager',
            'Teacher',
            'Manager Sale',
            'Worker',
            'Student',
            'Staff',
        ];

        foreach ($types as $type) {
            DB::table('user_types')->updateOrInsert(
                ['user_type_name' => $type],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
