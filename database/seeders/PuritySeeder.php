<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PuritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $purities = [
            [
                'karat_name'        => '24 Karat (999 Pure)',
                'karat_code'        => '24K',
                'purity_percentage' => 99.900,
                'hallmark_code'     => '999',
                'description'       => 'Fine Pure Gold Bullion & Coins',
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'karat_name'        => '22 Karat (916 Hallmark)',
                'karat_code'        => '22K',
                'purity_percentage' => 91.667,
                'hallmark_code'     => '916',
                'description'       => 'Standard BIS 916 Hallmarked Gold Jewellery',
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'karat_name'        => '18 Karat (750 Hallmark)',
                'karat_code'        => '18K',
                'purity_percentage' => 75.000,
                'hallmark_code'     => '750',
                'description'       => '18K Diamond-studded & Modern Jewellery',
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'karat_name'        => '14 Karat (585 Hallmark)',
                'karat_code'        => '14K',
                'purity_percentage' => 58.500,
                'hallmark_code'     => '585',
                'description'       => '14K Daily-wear Jewellery',
                'is_active'         => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];

        foreach ($purities as $purity) {
            DB::table('purities')->updateOrInsert(
                ['karat_code' => $purity['karat_code']],
                $purity
            );
        }
    }
}
