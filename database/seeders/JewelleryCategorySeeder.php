<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JewelleryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Finger Ring',          'category_code' => 'RNG', 'default_hsn_code' => '7113', 'description' => 'Gold rings for men & women'],
            ['category_name' => 'Necklace & Choker',    'category_code' => 'NCK', 'default_hsn_code' => '7113', 'description' => 'Traditional and bridal necklaces'],
            ['category_name' => 'Bangle & Kada',        'category_code' => 'BNG', 'default_hsn_code' => '7113', 'description' => 'Solid & hollow gold bangles'],
            ['category_name' => 'Gold Chain',           'category_code' => 'CHN', 'default_hsn_code' => '7113', 'description' => 'Machine-made and handmade chains'],
            ['category_name' => 'Earrings & Jhumka',    'category_code' => 'EAR', 'default_hsn_code' => '7113', 'description' => 'Studs, drops, and jhumkas'],
            ['category_name' => 'Pendant & Locket',     'category_code' => 'PDT', 'default_hsn_code' => '7113', 'description' => 'Religious and decorative pendants'],
            ['category_name' => 'Mangalsutra & Tanmaniya','category_code'=> 'MNG', 'default_hsn_code' => '7113', 'description' => 'Bridal mangalsutras and beads'],
            ['category_name' => 'Gold Coin',            'category_code' => 'CON', 'default_hsn_code' => '7108', 'description' => '24K & 22K minted coins'],
            ['category_name' => 'Gold Bullion Bar',     'category_code' => 'BAR', 'default_hsn_code' => '7108', 'description' => 'Fine bullion bars and biscuits'],
            ['category_name' => 'Bracelet',             'category_code' => 'BRC', 'default_hsn_code' => '7113', 'description' => 'Fancy wrist bracelets'],
        ];

        foreach ($categories as $cat) {
            DB::table('jewellery_categories')->updateOrInsert(
                ['category_code' => $cat['category_code']],
                array_merge($cat, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
