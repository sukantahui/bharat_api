<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sets up standard double-entry chart of accounts for jewellery retail & wholesale.
     */
    public function run(): void
    {
        // 1. Account Groups
        $groups = [
            ['id' => 1, 'group_name' => 'Current Assets',     'group_code' => 'ASST_CURR',   'primary_type' => 'asset',     'parent_id' => null, 'is_system' => true],
            ['id' => 2, 'group_name' => 'Cash-in-Hand',       'group_code' => 'ASST_CASH',   'primary_type' => 'asset',     'parent_id' => 1,    'is_system' => true],
            ['id' => 3, 'group_name' => 'Bank Accounts',      'group_code' => 'ASST_BANK',   'primary_type' => 'asset',     'parent_id' => 1,    'is_system' => true],
            ['id' => 4, 'group_name' => 'Sundry Debtors',     'group_code' => 'ASST_DEBT',   'primary_type' => 'asset',     'parent_id' => 1,    'is_system' => true],
            ['id' => 5, 'group_name' => 'Stock-in-Hand',      'group_code' => 'ASST_STK',    'primary_type' => 'asset',     'parent_id' => 1,    'is_system' => true],

            ['id' => 6, 'group_name' => 'Current Liabilities','group_code' => 'LIAB_CURR',   'primary_type' => 'liability', 'parent_id' => null, 'is_system' => true],
            ['id' => 7, 'group_name' => 'Sundry Creditors',   'group_code' => 'LIAB_CRED',   'primary_type' => 'liability', 'parent_id' => 6,    'is_system' => true],
            ['id' => 8, 'group_name' => 'Duties & Taxes',     'group_code' => 'LIAB_TAX',    'primary_type' => 'liability', 'parent_id' => 6,    'is_system' => true],

            ['id' => 9, 'group_name' => 'Direct Incomes',     'group_code' => 'INC_DIR',     'primary_type' => 'income',    'parent_id' => null, 'is_system' => true],
            ['id' => 10,'group_name' => 'Indirect Incomes',   'group_code' => 'INC_INDIR',   'primary_type' => 'income',    'parent_id' => null, 'is_system' => true],

            ['id' => 11,'group_name' => 'Direct Expenses',    'group_code' => 'EXP_DIR',     'primary_type' => 'expense',   'parent_id' => null, 'is_system' => true],
            ['id' => 12,'group_name' => 'Indirect Expenses',  'group_code' => 'EXP_INDIR',   'primary_type' => 'expense',   'parent_id' => null, 'is_system' => true],
        ];

        foreach ($groups as $group) {
            DB::table('account_groups')->updateOrInsert(
                ['id' => $group['id']],
                array_merge($group, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. Default Chart of Accounts
        $accounts = [
            // Cash & Bank
            ['account_name' => 'Cash in Hand (Counter)',   'account_code' => 'CASH_01',      'account_group_id' => 2, 'account_type' => 'cash',           'is_system' => true],
            ['account_name' => 'State Bank of India (Current)', 'account_code' => 'BANK_SBI_01', 'account_group_id' => 3, 'account_type' => 'bank',           'is_system' => true],
            ['account_name' => 'HDFC Bank (Current A/c)',   'account_code' => 'BANK_HDFC_01', 'account_group_id' => 3, 'account_type' => 'bank',           'is_system' => true],

            // Duties & Taxes (GST)
            ['account_name' => 'CGST Output Account (1.5%)','account_code' => 'TAX_CGST_OUT', 'account_group_id' => 8, 'account_type' => 'tax_payable',   'is_system' => true],
            ['account_name' => 'SGST Output Account (1.5%)','account_code' => 'TAX_SGST_OUT', 'account_group_id' => 8, 'account_type' => 'tax_payable',   'is_system' => true],
            ['account_name' => 'IGST Output Account (3.0%)','account_code' => 'TAX_IGST_OUT', 'account_group_id' => 8, 'account_type' => 'tax_payable',   'is_system' => true],
            ['account_name' => 'CGST Input Credit Account', 'account_code' => 'TAX_CGST_IN',  'account_group_id' => 1, 'account_type' => 'tax_receivable','is_system' => true],
            ['account_name' => 'SGST Input Credit Account', 'account_code' => 'TAX_SGST_IN',  'account_group_id' => 1, 'account_type' => 'tax_receivable','is_system' => true],
            ['account_name' => 'IGST Input Credit Account', 'account_code' => 'TAX_IGST_IN',  'account_group_id' => 1, 'account_type' => 'tax_receivable','is_system' => true],

            // Sales & Purchase Heads
            ['account_name' => 'Gold Jewellery Sales',     'account_code' => 'SALES_GOLD',   'account_group_id' => 9, 'account_type' => 'direct_income',   'is_system' => true],
            ['account_name' => 'Making Charges Income',    'account_code' => 'INC_MAKING',   'account_group_id' => 9, 'account_type' => 'direct_income',   'is_system' => true],
            ['account_name' => 'Old Gold Buyback / Scrap', 'account_code' => 'PUR_OLD_GOLD', 'account_group_id' => 11,'account_type' => 'direct_expense',  'is_system' => true],
            ['account_name' => 'Gold Jewellery Purchases', 'account_code' => 'PUR_GOLD',     'account_group_id' => 11,'account_type' => 'direct_expense',  'is_system' => true],

            // Operating Expenses
            ['account_name' => 'Shop Rent Expense',        'account_code' => 'EXP_RENT',     'account_group_id' => 12,'account_type' => 'indirect_expense','is_system' => false],
            ['account_name' => 'Electricity & Power',      'account_code' => 'EXP_ELEC',     'account_group_id' => 12,'account_type' => 'indirect_expense','is_system' => false],
            ['account_name' => 'Staff Salaries & Wages',   'account_code' => 'EXP_SALARY',   'account_group_id' => 12,'account_type' => 'indirect_expense','is_system' => false],
            ['account_name' => 'Tea & Refreshments',       'account_code' => 'EXP_TEA',      'account_group_id' => 12,'account_type' => 'indirect_expense','is_system' => false],
            ['account_name' => 'Karigar / Artisan Making Cost','account_code'=>'EXP_KARIGAR', 'account_group_id' => 11,'account_type' => 'direct_expense',  'is_system' => false],
        ];

        foreach ($accounts as $acc) {
            DB::table('accounts')->updateOrInsert(
                ['account_code' => $acc['account_code']],
                array_merge($acc, [
                    'opening_balance'      => 0.00,
                    'opening_balance_type' => 'debit',
                    'current_balance'      => 0.00,
                    'is_active'            => true,
                    'created_at'           => now(),
                    'updated_at'           => now()
                ])
            );
        }
    }
}
