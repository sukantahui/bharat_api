<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('business_profiles')->updateOrInsert(
            ['id' => 1],
            [
                'company_name'         => 'Bharat Gold & Jewellers Pvt. Ltd.',
                'trade_name'           => 'Bharat Jewellers',
                'legal_name'           => 'Bharat Gold & Diamond Retail LLP',
                'tagline'              => 'Purity & Trust Since 1977',
                'is_gst_registered'    => true,
                'gst_scheme'           => 'regular',
                'gstin'                => '19AAACB1234F1Z5',
                'pan_number'           => 'AAACB1234F',
                'hallmark_license_no'  => 'HM-WB-2026-9168',
                'phone'                => '+91 98300 12345',
                'email'                => 'contact@bharatjewellers.com',
                'address_line1'        => '42, Bowbazar Gold Market',
                'address_line2'        => 'Central Avenue',
                'city'                 => 'Kolkata',
                'state_code'           => 19,
                'pincode'              => '700012',
                'currency_symbol'      => '₹',
                'invoice_prefix'       => 'INV-',
                'purchase_prefix'      => 'PUR-',
                'stock_entry_prefix'   => 'STK-',
                'voucher_prefix'       => 'VCH-',
                'financial_year_start' => '2026-04-01',
                'terms_and_conditions' => '1. Gold rate is subject to daily market fluctuation. 2. 100% Hallmark guaranteed with HUID certificate. 3. Goods once sold can be exchanged within 7 days against original invoice.',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]
        );
    }
}
