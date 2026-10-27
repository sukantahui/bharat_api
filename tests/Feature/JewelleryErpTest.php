<?php

use App\Models\Account;
use App\Models\BusinessProfile;
use App\Models\DailyGoldRate;
use App\Models\JewelleryCategory;
use App\Models\JewelleryItem;
use App\Models\Party;
use App\Models\Purity;
use App\Models\Sale;
use App\Models\User;
use App\Models\UserType;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $userType = UserType::firstOrCreate(
        ['user_type_name' => 'Admin'],
        ['user_type_name' => 'Admin']
    );

    $this->admin = User::firstOrCreate(
        ['email' => 'sukantahui'],
        [
            'unique_id'    => 'SUKANTA01',
            'user_name'    => 'sukantahui',
            'password'     => bcrypt('Password@123'),
            'user_type_id' => $userType->id,
        ]
    );

    Sanctum::actingAs($this->admin);
});

test('can retrieve jewellery master purities and categories', function () {
    $puritiesRes = $this->getJson('/api/purities');
    $puritiesRes->assertStatus(200)
        ->assertJson(['status' => true]);

    $categoriesRes = $this->getJson('/api/jewellery-categories');
    $categoriesRes->assertStatus(200)
        ->assertJson(['status' => true]);
});

test('can set daily gold board rate and fetch today rate', function () {
    $purity = Purity::where('karat_code', '22K')->first() ?? Purity::first();

    $postRes = $this->postJson('/api/daily-gold-rates', [
        'rate_date'             => now()->format('Y-m-d'),
        'metal_type'            => 'gold',
        'purity_id'             => $purity->id,
        'rate_per_gram'         => 7350.00,
        'buyback_rate_per_gram' => 7050.00,
    ]);

    $postRes->assertStatus(201)
        ->assertJson(['status' => true]);

    $todayRes = $this->getJson('/api/daily-gold-rates/today');
    $todayRes->assertStatus(200)
        ->assertJson(['status' => true]);
});

test('can create party, production stock inward, and POS sale invoice', function () {
    // 1. Create Party
    $partyRes = $this->postJson('/api/parties', [
        'party_type' => 'customer',
        'party_name' => 'Pest Test Customer',
        'phone'      => '9800112233',
        'state_id'   => 1,
        'state_code' => 19,
        'city'       => 'Kolkata',
    ]);
    $partyRes->assertStatus(201);
    $partyId = $partyRes->json('data.id');

    // 2. Production Stock Inward
    $purity = Purity::where('karat_code', '22K')->first() ?? Purity::first();
    $cat    = JewelleryCategory::where('category_code', 'RNG')->first() ?? JewelleryCategory::first();
    $tagCode = 'TAG-TEST-' . rand(1000, 9999);

    $stockRes = $this->postJson('/api/stock-entries', [
        'entry_date' => now()->format('Y-m-d'),
        'entry_type' => 'production_inward',
        'notes'      => 'Workshop batch inward test',
        'items'      => [
            [
                'category_id'        => $cat->id,
                'purity_id'          => $purity->id,
                'item_name'          => 'Gold Ring Test',
                'tag_code'           => $tagCode,
                'pieces'             => 1,
                'gross_weight'       => 5.000,
                'stone_weight'       => 0.000,
                'making_charge_type' => 'per_gram',
                'making_charge_rate' => 600.00,
            ]
        ]
    ]);
    $stockRes->assertStatus(201);

    // Verify item created in stock
    $item = JewelleryItem::where('tag_code', $tagCode)->first();
    expect($item)->not->toBeNull();
    expect($item->status)->toBe('in_stock');

    // 3. Scan item by tag
    $tagLookup = $this->getJson("/api/jewellery-items/tag/{$tagCode}");
    $tagLookup->assertStatus(200)
        ->assertJsonPath('data.tagCode', $tagCode);

    // 4. Create POS Sale with Old Gold exchange
    $cashAccount = Account::where('account_type', 'cash')->first();

    $saleRes = $this->postJson('/api/sales', [
        'invoice_date'        => now()->format('Y-m-d'),
        'invoice_type'        => 'retail_b2c',
        'party_id'            => $partyId,
        'customer_name'       => 'Pest Test Customer',
        'customer_mobile'     => '9800112233',
        'is_gst_applicable'   => true,
        'payment_status'      => 'paid',
        'payment_mode'        => 'cash',
        'deposit_account_id'  => $cashAccount?->id,
        'paid_amount'         => 35000.00,
        'items' => [
            [
                'jewellery_item_id' => $item->id,
                'category_id'       => $cat->id,
                'purity_id'         => $purity->id,
                'item_name'         => $item->item_name,
                'tag_code'          => $item->tag_code,
                'gross_weight'      => 5.000,
                'stone_weight'      => 0.000,
                'rate_per_gram'     => 7300.00,
                'making_charge_type'=> 'per_gram',
                'making_charge_rate'=> 600.00,
                'gst_rate'          => 3.00,
            ]
        ],
        'old_gold_exchanges' => [
            [
                'purity_id'        => $purity->id,
                'item_description' => 'Customer old melted scrap',
                'gross_weight'     => 1.000,
                'dust_weight'      => 0.000,
                'rate_per_gram'    => 7000.00,
            ]
        ]
    ]);

    $saleRes->assertStatus(201);
    $saleId = $saleRes->json('data.id');

    // Verify item marked as sold
    $item->refresh();
    expect($item->status)->toBe('sold');

    // 5. Test Cash Drawer Summary
    $drawerRes = $this->getJson('/api/accounts/drawer/cash-summary');
    $drawerRes->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'asOfDate',
                'totalCashBalance',
                'totalBankBalance',
                'totalLiquidFunds',
                'todayCashInflow',
            ]
        ]);

    // 6. Test GST Reports
    $gstr1Res = $this->getJson('/api/gst/gstr1-summary');
    $gstr1Res->assertStatus(200)
        ->assertJson(['status' => true]);

    $gstr3bRes = $this->getJson('/api/gst/gstr3b-summary');
    $gstr3bRes->assertStatus(200)
        ->assertJson(['status' => true]);
});

test('can issue sales return credit note and restock item', function () {
    $sale = Sale::with('items')->latest()->first();
    expect($sale)->not->toBeNull();

    $saleItem = $sale->items->first();
    expect($saleItem)->not->toBeNull();

    $cashAcc = Account::where('account_type', 'cash')->first();

    $returnRes = $this->postJson('/api/sales-returns', [
        'credit_note_date'   => now()->format('Y-m-d'),
        'sale_id'            => $sale->id,
        'refund_mode'        => 'cash_refund',
        'refund_account_id'  => $cashAcc?->id,
        'return_reason'      => 'size_issue',
        'notes'              => 'Customer ring return test',
        'items' => [
            [
                'sale_item_id'             => $saleItem->id,
                'jewellery_item_id'        => $saleItem->jewellery_item_id,
                'category_id'              => $saleItem->category_id,
                'purity_id'                => $saleItem->purity_id,
                'item_name'                => $saleItem->item_name,
                'tag_code'                 => $saleItem->tag_code,
                'gross_weight'             => (float) $saleItem->gross_weight,
                'stone_weight'             => 0.000,
                'rate_per_gram'            => (float) ($saleItem->gold_rate_per_gram ?? 7300.00),
                'making_charges_refunded'  => 1000.00,
                'gst_rate'                 => 3.00,
                'stock_action'             => 'restocked_as_original',
            ]
        ]
    ]);

    $returnRes->assertStatus(201)
        ->assertJson(['status' => true]);

    // Check item is restocked
    if ($saleItem->jewellery_item_id) {
        $item = JewelleryItem::find($saleItem->jewellery_item_id);
        expect($item->status)->toBe('in_stock');
    }
});

test('can record cash receipt payment and operating expense', function () {
    $cashAcc = Account::where('account_type', 'cash')->first();
    expect($cashAcc)->not->toBeNull();

    $openingCash = (float) $cashAcc->current_balance;

    // 1. Record customer money receipt (+10,000)
    $receiptRes = $this->postJson('/api/payments', [
        'payment_date' => now()->format('Y-m-d'),
        'payment_type' => 'receipt',
        'account_id'   => $cashAcc->id,
        'payment_mode' => 'cash',
        'amount'       => 10000.00,
        'notes'        => 'Cash deposit test',
    ]);
    $receiptRes->assertStatus(201);

    $cashAcc->refresh();
    expect((float) $cashAcc->current_balance)->toBe($openingCash + 10000.00);

    // 2. Record daily operating expense (-1,200)
    $expenseHead = Account::where('account_type', 'indirect_expense')->first() ?? $cashAcc;

    $expenseRes = $this->postJson('/api/expense-incomes', [
        'entry_date'         => now()->format('Y-m-d'),
        'entry_type'         => 'expense',
        'account_id'         => $expenseHead->id,
        'payment_account_id' => $cashAcc->id,
        'amount'             => 1200.00,
        'payment_mode'       => 'cash',
        'description'        => 'Showroom tea and cleaning supplies',
    ]);
    $expenseRes->assertStatus(201);

    $cashAcc->refresh();
    expect((float) $cashAcc->current_balance)->toBe($openingCash + 10000.00 - 1200.00);
});

test('can retrieve dashboard summary stats', function () {
    $res = $this->getJson('/api/dashboard');
    $res->assertStatus(200)
        ->assertJson(['status' => true])
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'asOfDate',
                'goldRates',
                'todayStats',
                'cashDrawer',
                'inventorySummary',
                'recentSales',
                'recentStockEntries',
                'directoryCounts',
            ]
        ]);
});

test('can create category, purity, and record and cancel purchase', function () {
    // 1. Create Category
    $catCode = 'TST' . rand(100, 999);
    $catRes = $this->postJson('/api/jewellery-categories', [
        'category_name' => 'Test Diamond Pendant',
        'category_code' => $catCode,
    ]);
    $catRes->assertStatus(201)
        ->assertJson(['status' => true]);

    // 2. Create Purity
    $purCode = '99K' . rand(10, 99);
    $purRes = $this->postJson('/api/purities', [
        'karat_name'        => 'Custom Test Purity',
        'karat_code'        => $purCode,
        'purity_percentage' => 99.50,
    ]);
    $purRes->assertStatus(201)
        ->assertJson(['status' => true]);
    $purityId = $purRes->json('data.id');

    // 3. Create Vendor Party
    $vendorRes = $this->postJson('/api/parties', [
        'party_type' => 'vendor',
        'party_name' => 'Wholesale Gold Bullion Supplier',
        'phone'      => '9988776655',
    ]);
    $vendorRes->assertStatus(201);
    $vendorId = $vendorRes->json('data.id');

    // 4. Record Purchase
    $purInvoiceRes = $this->postJson('/api/purchases', [
        'purchase_date' => now()->format('Y-m-d'),
        'party_id'      => $vendorId,
        'purchase_type' => 'vendor_jewellery',
        'items'         => [
            [
                'purity_id'        => $purityId,
                'item_description' => 'Fine Gold Bar 10g',
                'gross_weight'     => 10.000,
                'rate_per_gram'    => 7200.00,
            ]
        ]
    ]);
    $purInvoiceRes->assertStatus(201);
    $purchaseId = $purInvoiceRes->json('data.id');

    // 5. Cancel Purchase
    $cancelRes = $this->postJson("/api/purchases/{$purchaseId}/cancel", [
        'reason' => 'Vendor dispatched incorrect weight',
    ]);
    $cancelRes->assertStatus(200)
        ->assertJson([
            'status' => true,
            'data'   => [
                'isCancelled' => true,
            ]
        ]);
});


