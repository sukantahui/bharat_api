<?php

namespace Database\Seeders;

use App\Models\PosTerminal;
use Illuminate\Database\Seeder;

class PosTerminalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Mandatory Main Gold Counter (As required by system specifications)
        PosTerminal::firstOrCreate(
            ['code' => 'POS-GOLD-01'],
            [
                'name'            => 'Main Gold Counter',
                'counter_type'    => 'gold',
                'area'            => 'Bowbazar Flagship Showroom',
                'branch_code'     => 'BOW-01',
                'address'         => '42, Bowbazar Gold Market, Central Avenue, Kolkata 700012',
                'invoice_prefix'  => 'BJ-GOLD',
                'assigned_staff'  => 'Main Counter Cashier',
                'opening_balance' => 10000.00,
                'current_balance' => 10000.00,
                'status'          => 'active',
                'notes'           => 'Primary gold jewellery retail billing and settlement counter with dual-display thermal printer and digital scale connectivity.',
                'last_active_at'  => now(),
            ]
        );

        // 2. VIP Bridal Lounge Counter
        PosTerminal::firstOrCreate(
            ['code' => 'POS-BRIDAL-02'],
            [
                'name'            => 'VIP Bridal Lounge Counter',
                'counter_type'    => 'gold',
                'area'            => 'Bowbazar Flagship Showroom',
                'branch_code'     => 'BOW-01',
                'address'         => '42, Bowbazar Gold Market, Central Avenue, Kolkata 700012',
                'invoice_prefix'  => 'BJ-BRIDAL',
                'assigned_staff'  => 'Bridal Lounge Staff',
                'opening_balance' => 5000.00,
                'current_balance' => 5000.00,
                'status'          => 'active',
                'notes'           => 'Exclusive 1st Floor VIP Bridal Lounge & Diamond jewellery showroom counter.',
                'last_active_at'  => now(),
            ]
        );

        // 3. Fine Silver & Bullion Counter
        PosTerminal::firstOrCreate(
            ['code' => 'POS-SILVER-03'],
            [
                'name'            => 'Silver & Bullion Counter',
                'counter_type'    => 'silver',
                'area'            => 'Gariahat Branch',
                'branch_code'     => 'GAR-02',
                'address'         => '18/1 Rashbehari Avenue, Gariahat, Kolkata 700029',
                'invoice_prefix'  => 'BJ-SILV',
                'assigned_staff'  => 'Gariahat Cashier',
                'opening_balance' => 5000.00,
                'current_balance' => 5000.00,
                'status'          => 'active',
                'notes'           => '999 Fine Silver, silverware, and bullion coin procurement & sales counter.',
                'last_active_at'  => now(),
            ]
        );
    }
}
