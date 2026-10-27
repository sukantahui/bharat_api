# 🛠️ Next Steps Implementation Instructions — Gold Jewellery API

> **Purpose:** Detailed step-by-step roadmap to implement Models, Seeders, Services, Controllers, and Tests following the newly created migrations.

---

## 📋 Phase-by-Phase Execution Roadmap

```
┌─────────────────────────┐     ┌─────────────────────────┐     ┌─────────────────────────┐
│        Phase 1          │     │        Phase 2          │     │        Phase 3          │
│ Eloquent Models & Casts ├────►│ Master Database Seeders ├────►│ Form Requests & Rules   │
└─────────────────────────┘     └─────────────────────────┘     └─────────────────────────┘
             │                                                               │
             ▼                                                               ▼
┌─────────────────────────┐     ┌─────────────────────────┐     ┌─────────────────────────┐
│        Phase 6          │     │        Phase 5          │     │        Phase 4          │
│ Automated Pest Tests    │◄────┤ Controllers & Routes    │◄────┤ Domain Business Services│
└─────────────────────────┘     └─────────────────────────┘     └─────────────────────────┘
             │
             ▼
┌─────────────────────────┐
│        Phase 7          │
│ Insomnia Collection API │
└─────────────────────────┘
```

---

## 📌 Phase 1: Eloquent Models & Casts (`app/Models/`)

Create the 18 Eloquent Models with explicit attribute casting, mass-assignment `$fillable`, and relationships.

### Models to Create:
1. `BusinessProfile.php`
   * **Casts:** `'is_gst_registered' => 'boolean'`, `'financial_year_start' => 'date'`
   * **Relations:** `belongsTo(State::class)`, `belongsTo(District::class)`
2. `JewelleryCategory.php`
   * **Casts:** `'is_active' => 'boolean'`
   * **Relations:** `hasMany(JewelleryItem::class)`
3. `Purity.php`
   * **Casts:** `'purity_percentage' => 'decimal:3'`, `'is_active' => 'boolean'`
   * **Relations:** `hasMany(JewelleryItem::class)`, `hasMany(DailyGoldRate::class)`
4. `DailyGoldRate.php`
   * **Casts:** `'rate_date' => 'date'`, `'selling_rate_per_gram' => 'decimal:2'`, `'buying_rate_per_gram' => 'decimal:2'`
   * **Relations:** `belongsTo(Purity::class)`, `belongsTo(User::class, 'created_by')`
5. `Party.php`
   * **Casts:** `'opening_balance' => 'decimal:2'`, `'current_balance' => 'decimal:2'`, `'metal_opening_balance_grams' => 'decimal:3'`, `'metal_current_balance_grams' => 'decimal:3'`, `'is_active' => 'boolean'`
   * **Relations:** `belongsTo(State::class)`, `hasMany(Sale::class)`, `hasMany(Purchase::class)`, `hasMany(Payment::class)`
6. `JewelleryItem.php`
   * **Casts:** `'gross_weight' => 'decimal:3'`, `'stone_weight' => 'decimal:3'`, `'net_gold_weight' => 'decimal:3'`, `'fine_gold_weight' => 'decimal:3'`, `'purchase_cost' => 'decimal:2'`
   * **Boot Event:** Automatically recalculate `net_gold_weight = gross_weight - stone_weight` on save.
   * **Relations:** `belongsTo(JewelleryCategory::class, 'category_id')`, `belongsTo(Purity::class, 'purity_id')`
7. `StockEntry.php` & `StockEntryItem.php`
   * **StockEntry Relations:** `hasMany(StockEntryItem::class)`, `belongsTo(Party::class, 'artisan_id')`, `belongsTo(User::class, 'created_by')`
   * **StockEntryItem Relations:** `belongsTo(StockEntry::class)`, `belongsTo(JewelleryItem::class)`
8. `Purchase.php` & `PurchaseItem.php`
   * **Purchase Relations:** `belongsTo(Party::class)`, `hasMany(PurchaseItem::class)`, `belongsTo(User::class, 'created_by')`
   * **PurchaseItem Relations:** `belongsTo(Purchase::class)`, `belongsTo(JewelleryItem::class)`, `belongsTo(Purity::class)`, `belongsTo(JewelleryCategory::class)`
9. `Sale.php`, `SaleItem.php`, & `SaleOldGoldExchange.php`
   * **Sale Relations:** `belongsTo(Party::class)`, `hasMany(SaleItem::class)`, `hasMany(SaleOldGoldExchange::class)`, `belongsTo(State::class, 'place_of_supply_state_id')`
   * **SaleItem Relations:** `belongsTo(Sale::class)`, `belongsTo(JewelleryItem::class)`, `belongsTo(JewelleryCategory::class)`, `belongsTo(Purity::class)`
   * **SaleOldGoldExchange Relations:** `belongsTo(Sale::class)`
10. `AccountGroup.php` & `Account.php`
    * **AccountGroup Relations:** `hasMany(Account::class)`, `belongsTo(AccountGroup::class, 'parent_id')`, `hasMany(AccountGroup::class, 'parent_id')`
    * **Account Relations:** `belongsTo(AccountGroup::class)`, `hasMany(FinancialVoucherEntry::class)`, `hasMany(Payment::class)`
11. `FinancialVoucher.php` & `FinancialVoucherEntry.php`
    * **FinancialVoucher Relations:** `hasMany(FinancialVoucherEntry::class)`, `belongsTo(User::class, 'created_by')`
    * **FinancialVoucherEntry Relations:** `belongsTo(FinancialVoucher::class)`, `belongsTo(Account::class)`
12. `Payment.php`
    * **Casts:** `'payment_date' => 'date'`, `'amount' => 'decimal:2'`
    * **Relations:** `belongsTo(Account::class)`, `belongsTo(Party::class)`, `belongsTo(User::class, 'created_by')`
13. `ExpenseIncome.php`
    * **Casts:** `'entry_date' => 'date'`, `'amount' => 'decimal:2'`, `'is_gst_claimed' => 'boolean'`, `'tax_amount' => 'decimal:2'`
    * **Relations:** `belongsTo(Account::class, 'account_id')`, `belongsTo(Account::class, 'payment_account_id')`, `belongsTo(User::class, 'created_by')`
14. `SalesReturn.php` & `SalesReturnItem.php` (Credit Notes)
    * **SalesReturn Casts:** `'credit_note_date' => 'date'`, `'original_invoice_date' => 'date'`, `'total_credit_amount' => 'decimal:2'`, `'is_gst_applicable' => 'boolean'`
    * **SalesReturn Relations:** `belongsTo(Sale::class)`, `belongsTo(Party::class)`, `hasMany(SalesReturnItem::class)`, `belongsTo(Payment::class, 'refund_payment_id')`
    * **SalesReturnItem Relations:** `belongsTo(SalesReturn::class)`, `belongsTo(JewelleryItem::class)`, `belongsTo(SaleItem::class)`
15. `PurchaseReturn.php` & `PurchaseReturnItem.php` (Debit Notes)
    * **PurchaseReturn Casts:** `'debit_note_date' => 'date'`, `'total_debit_amount' => 'decimal:2'`, `'is_gst_applicable' => 'boolean'`
    * **PurchaseReturn Relations:** `belongsTo(Purchase::class)`, `belongsTo(Party::class)`, `hasMany(PurchaseReturnItem::class)`
    * **PurchaseReturnItem Relations:** `belongsTo(PurchaseReturn::class)`, `belongsTo(JewelleryItem::class)`, `belongsTo(PurchaseItem::class)`

---

## 📌 Phase 2: Database Seeders (`database/seeders/`)

Create foundational seeders to initialize the jewellery business ready out-of-the-box.

### 1. `PuritySeeder.php`
```php
DB::table('purities')->insert([
    ['karat_name' => '24 Karat (999)', 'karat_code' => '24K', 'purity_percentage' => 99.900, 'hallmark_code' => '999'],
    ['karat_name' => '22 Karat (916 Hallmark)', 'karat_code' => '22K', 'purity_percentage' => 91.667, 'hallmark_code' => '916'],
    ['karat_name' => '18 Karat (750 Hallmark)', 'karat_code' => '18K', 'purity_percentage' => 75.000, 'hallmark_code' => '750'],
    ['karat_name' => '14 Karat (585 Hallmark)', 'karat_code' => '14K', 'purity_percentage' => 58.500, 'hallmark_code' => '585'],
]);
```

### 2. `JewelleryCategorySeeder.php`
Populate standard jewellery categories:
* `RNG` — Rings (HSN 7113)
* `NCK` — Necklaces (HSN 7113)
* `BNG` — Bangles & Kadas (HSN 7113)
* `CHN` — Chains (HSN 7113)
* `EAR` — Earrings & Tops (HSN 7113)
* `PDT` — Pendants (HSN 7113)
* `MNG` — Mangalsutra (HSN 7113)
* `CON` — Gold Coins (HSN 7108)
* `BAR` — Gold Bullion Bars (HSN 7108)

### 3. `ChartOfAccountsSeeder.php`
Create default double-entry chart of accounts:
* **Account Groups**:
  * `Current Assets` (`ASST_CURR`) $\rightarrow$ Subgroups: `Cash-in-Hand` (`ASST_CASH`), `Bank Accounts` (`ASST_BANK`), `Sundry Debtors` (`ASST_DEBTORS`), `Stock in Hand` (`ASST_STOCK`)
  * `Current Liabilities` (`LIAB_CURR`) $\rightarrow$ Subgroups: `Sundry Creditors` (`LIAB_CREDITORS`), `Duties & Taxes` (`LIAB_TAX`)
  * `Direct Incomes` (`INC_DIR`) $\rightarrow$ Gold Sales, Making Charges Income, Old Gold Recovery
  * `Direct Expenses` (`EXP_DIR`) $\rightarrow$ Gold Purchases, Karigar Wages
  * `Indirect Expenses` (`EXP_INDIR`) $\rightarrow$ Shop Rent, Electricity, Staff Salaries, Tea/Refreshments, Repairs
* **Default Accounts**:
  * `Cash Counter 1 (Shop Drawer)` (code: `CASH_01`, type: `cash`)
  * `SBI Current Account` (code: `BANK_SBI_01`, type: `bank`)
  * `HDFC Bank Account` (code: `BANK_HDFC_01`, type: `bank`)
  * `CGST Output Account (1.5%)` (code: `TAX_CGST_OUT`, type: `tax_payable`)
  * `SGST Output Account (1.5%)` (code: `TAX_SGST_OUT`, type: `tax_payable`)
  * `IGST Output Account (3.0%)` (code: `TAX_IGST_OUT`, type: `tax_payable`)
  * `CGST Input Account` (code: `TAX_CGST_IN`, type: `tax_receivable`)
  * `SGST Input Account` (code: `TAX_SGST_IN`, type: `tax_receivable`)
  * `IGST Input Account` (code: `TAX_IGST_IN`, type: `tax_receivable`)

---

## 📌 Phase 3: Form Requests & Validation (`app/Http/Requests/`)

Create dedicated validation classes extending `BaseRequest`:
1. `StoreDailyGoldRateRequest`: Validates date, purity_id exists, rates > 0.
2. `StorePartyRequest`: Validates phone, valid 15-char GSTIN format (if provided), valid 10-char PAN format (if provided).
3. `StoreStockEntryRequest`: Validates item weights: `gross_weight >= stone_weight`, positive valuation.
4. `StorePurchaseRequest`: Validates items array, positive rates and weights.
5. `StoreSaleRequest`:
   * Validates customer details.
   * If `payment_mode === 'cash'` and `amount >= 200000`, enforce **PAN is required** (Indian statutory regulation).
   * Validates jewellery items are `in_stock`.
6. `StoreExpenseIncomeRequest`: Validates account_id, payment_account_id exists, amount > 0.
7. `StorePaymentRequest`: Validates amount > 0, account_id exists.

---

## 📌 Phase 4: Domain Service Layer (`app/Services/`)

Keep controllers slim by moving business domain math into decoupled services:

### 1. `GoldPricingService`
* Calculates `gold_amount`, `making_charges` (by per gram, %, or fixed), `stone_charges`, and subtotal.

### 2. `TaxCalculationService`
* Compares `business_profiles.state_code` with `parties.state_code` or `sales.place_of_supply_state_code`.
* If `business_profiles.is_gst_registered == false` $\rightarrow$ Tax is 0.00, tax_type is `non_gst`.
* If `seller_state == buyer_state` $\rightarrow$ 1.5% CGST + 1.5% SGST.
* If `seller_state != buyer_state` $\rightarrow$ 3.0% IGST.

### 3. `StockMovementService`
* Marks `jewellery_items` as `sold` when sale completes.
* Restores status to `in_stock` if sale is cancelled or refunded.
* Handles inward creation from `StockEntry` (Production inward).

### 4. `AccountingLedgerService`
* Automatically generates balanced `financial_vouchers` and entries for sales, purchases, expenses, and payments.
* Updates `accounts.current_balance` in real time.

---

## 📌 Phase 5: Controllers & Routes (`routes/api.php`)

Group endpoints cleanly under RESTful prefixes:

| Route Prefix | Controller | Key Methods |
| :--- | :--- | :--- |
| `/api/jewellery/profile` | `BusinessProfileController` | `show`, `update` |
| `/api/jewellery/categories` | `JewelleryCategoryController` | `index`, `store`, `update` |
| `/api/jewellery/purities` | `PurityController` | `index`, `store` |
| `/api/jewellery/rates` | `DailyGoldRateController` | `index`, `today`, `store` |
| `/api/jewellery/parties` | `PartyController` | `index`, `show`, `store`, `update`, `ledger` |
| `/api/jewellery/items` | `JewelleryItemController` | `index`, `show`, `store`, `update`, `byTag` |
| `/api/jewellery/stock-entries` | `StockEntryController` | `index`, `show`, `store` (Production Inward) |
| `/api/jewellery/purchases` | `PurchaseController` | `index`, `show`, `store`, `cancel` |
| `/api/jewellery/sales` | `SaleController` | `index`, `show`, `store`, `cancel`, `invoicePdf` |
| `/api/jewellery/sales-returns` | `SalesReturnController` | `index`, `show`, `store` (Credit Notes for GSTR-1 Table 9B) |
| `/api/jewellery/purchase-returns` | `PurchaseReturnController` | `index`, `show`, `store` (Debit Notes for GSTR-2B/3B) |
| `/api/accounting/accounts` | `AccountController` | `index`, `show`, `store`, `cashBalance` |
| `/api/accounting/payments` | `PaymentController` | `index`, `store` (Cash/Bank in/out) |
| `/api/accounting/expenses` | `ExpenseIncomeController` | `index`, `store` (Operating expenses & incomes) |
| `/api/reports/gst` | `GstReportController` | `gstr1`, `gstr3b`, `hsnSummary` |

---

## 📌 Phase 6: Automated Testing Suite (`tests/Feature/Jewellery/`)

Write Pest feature tests to guarantee reliability:
1. `WeightCalculationTest`: Verifies net weight and fine weight equations across 24K, 22K, 18K.
2. `ProductionInwardTest`: Verifies adding jewellery from production creates `jewellery_items` in stock without errors.
3. `SaleInvoiceGstTest`: Verifies 1.5% CGST + 1.5% SGST calculation for intra-state and 3% IGST for inter-state.
4. `NonGstShopSaleTest`: Verifies that if `is_gst_registered == false`, invoice tax is 0% and does not trigger GST liabilities.
5. `OldGoldExchangeTradeInTest`: Verifies deducting customer old gold from sale invoice grand total.
6. `CashRegisterBalanceTest`: Verifies that sales receipts increase cash balance and expenses decrease cash balance immediately.
7. `GeneralLedgerBalancingTest`: Verifies every financial voucher has equal total debits and credits ($\sum\text{Debit} = \sum\text{Credit}$).

---

## 📌 Phase 7: Insomnia API Collection Update

Add the new Jewellery ERP folders to [bharat_api.insomnia_collection.json](file:///e:/wamp64/www/bharat_api/bharat_api.insomnia_collection.json):
* `Jewellery - Master & Rates` (Purities, Categories, Daily Gold Rates)
* `Jewellery - Inventory & Stock Inward` (Items by Tag, Production Inward)
* `Jewellery - Purchases` (Vendor Invoice, Old Gold Buyback)
* `Jewellery - Sales Invoicing` (Retail Invoice, B2B Invoice, Old Gold Exchange)
* `Jewellery - Cash & Bank Book` (Payments, Cash Balance Inquiry)
* `Jewellery - Income & Expenditure` (Shop Expenses, Other Incomes)
* `Jewellery - GST Reports` (GSTR-1 JSON, GSTR-3B Summary)
