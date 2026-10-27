# 📖 Bharat Jewellery ERP & POS — Complete API Reference Manual

> **Base URL:** `http://localhost/bharat_api/public/api` (or `http://127.0.0.1:8000/api`)  
> **Auth Scheme:** HTTP Bearer Token (`Authorization: Bearer <token>`) via Laravel Sanctum  
> **Content-Type:** `application/json` • **Accept:** `application/json`

---

## 📋 Table of Contents

1. [Authentication & User Self-Service](#1-authentication--user-self-service)
2. [Dashboard & KPI Analytics](#2-dashboard--kpi-analytics)
3. [Jewellery Masters (Purities, Categories, Daily Rates)](#3-jewellery-masters)
4. [Business Profile & Settings](#4-business-profile--settings)
5. [Parties (Customers, Vendors, Karigars)](#5-parties-crm)
6. [Jewellery Items & Barcode Tags (Showroom Inventory)](#6-jewellery-items--tags)
7. [Stock Entries (Artisan Production Inward)](#7-stock-entries-inward)
8. [Procurement & Purchases](#8-procurement--purchases)
9. [Point of Sale (POS) & GST Invoicing](#9-point-of-sale-pos--sales)
10. [Sales Returns & Credit Notes](#10-sales-returns--credit-notes)
11. [Accounts, Cash Drawer & Ledger Statements](#11-accounts--cash-drawer)
12. [Payments & Cash/Bank Book Vouchers](#12-payments--vouchers)
13. [Operating Expenses & Incomes](#13-operating-expenses--incomes)
14. [Statutory GST Reports (GSTR-1, GSTR-3B)](#14-statutory-gst-reports)
15. [Administration (Users, Employees, Departments, Backups)](#15-administration)

---

## 1. Authentication & User Self-Service

### `POST /login`
* **Access:** Public (Rate limit: 6 requests / minute)
* **Purpose:** Authenticate employee credentials and obtain a Sanctum Bearer token.
* **Request Body:**
  ```json
  {
    "email": "sukantahui",
    "password": "Password@123"
  }
  ```
  *(Note: `email` parameter accepts either the user's email address or system username).*
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "login successfully",
    "data": {
      "user": {
        "id": 1,
        "userName": "sukantahui",
        "name": "Sukanta Hui",
        "email": "sukantahui@example.com",
        "role": "Admin",
        "mobile": "9830000000",
        "department": "Management",
        "designation": "Owner",
        "employeeId": 1
      },
      "token": "1|qX8zO87w..."
    }
  }
  ```
* **Errors:**
  * `401 Unauthorized`: `{"status": false, "message": "Invalid credentials"}`
  * `422 Unprocessable Entity`: Validation failure on email/password.
  * `429 Too Many Requests`: Rate limit exceeded.

---

### `GET /me`
* **Access:** Authenticated (Any role)
* **Purpose:** Returns the current logged-in employee profile formatted through `UserResource`.
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "User fetched",
    "data": {
      "id": 1,
      "userName": "sukantahui",
      "name": "Sukanta Hui",
      "email": "sukantahui@example.com",
      "role": "Admin",
      "mobile": "9830000000",
      "department": "Management",
      "designation": "Owner",
      "employeeId": 1
    }
  }
  ```

---

### `POST /logout`
* **Access:** Authenticated
* **Purpose:** Deletes current Sanctum personal access token.
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Logged out successfully",
    "data": { ... }
  }
  ```

---

### `GET|POST /revokeAll`
* **Access:** Authenticated
* **Purpose:** Revokes all active session tokens across devices for current user.

---

## 2. Dashboard & KPI Analytics

### `GET /dashboard` (or `GET /dashboard/stats`)
* **Access:** Authenticated (Any role)
* **Query Parameters:**
  * `date` (optional, default: today `YYYY-MM-DD`): Date for stats calculation.
* **Purpose:** Single consolidated KPI endpoint powering the main overview dashboard.
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Dashboard statistics retrieved successfully",
    "data": {
      "asOfDate": "2026-10-15",
      "goldRates": [
        {
          "karatCode": "24K",
          "karatName": "24 Karat (999)",
          "metalType": "gold",
          "sellingRatePerGram": 7950.00,
          "buyingRatePerGram": 7650.00,
          "rateDate": "2026-10-15"
        },
        {
          "karatCode": "22K",
          "karatName": "22 Karat (916 Hallmark)",
          "metalType": "gold",
          "sellingRatePerGram": 7350.00,
          "buyingRatePerGram": 7050.00,
          "rateDate": "2026-10-15"
        }
      ],
      "todayStats": {
        "salesCount": 12,
        "salesAmount": 458200.00,
        "salesNetWeightGrams": 62.450,
        "salesCollectedAmount": 410000.00,
        "purchasesCount": 2,
        "purchasesAmount": 150000.00,
        "purchasesWeightGrams": 20.000,
        "expensesCount": 4,
        "expensesAmount": 3850.00
      },
      "cashDrawer": {
        "cashInHand": 128450.00,
        "bankBalance": 850000.00,
        "totalLiquidFunds": 978450.00
      },
      "inventorySummary": {
        "inStockItemsCount": 485,
        "inStockGrossWeight": 3420.500,
        "inStockNetWeight": 3280.200,
        "totalSoldItems": 142,
        "categoryBreakdown": [
          { "category_name": "Gold Ring", "item_count": 120, "total_gross_weight": "680.500" },
          { "category_name": "Necklace Set", "item_count": 45, "total_gross_weight": "1120.000" }
        ]
      },
      "recentSales": [
        {
          "id": 105,
          "invoice_number": "INV-20261015-0012",
          "invoice_date": "2026-10-15",
          "customer_name": "Rohan Sharma",
          "grand_total": "45250.00",
          "payment_status": "paid",
          "total_net_weight": "6.120"
        }
      ],
      "recentStockEntries": [
        {
          "id": 42,
          "entry_number": "STK-20261015-0003",
          "entry_date": "2026-10-15",
          "entry_type": "production_inward",
          "total_items_count": 8,
          "total_gross_weight": "48.200"
        }
      ],
      "directoryCounts": {
        "totalCustomers": 1840,
        "totalVendors": 42,
        "totalKarigars": 18
      }
    }
  }
  ```

---

## 3. Jewellery Masters

### `GET /purities`
* **Access:** Authenticated
* **Purpose:** Returns active gold purities (24K, 22K, 18K, 14K) with karat percentages.
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Purities retrieved successfully",
    "data": [
      {
        "id": 1,
        "karatName": "24 Karat (999)",
        "karatCode": "24K",
        "purityPercentage": 99.900,
        "hallmarkCode": "999",
        "description": "Pure Bullion / Fine Gold",
        "isActive": true
      },
      {
        "id": 2,
        "karatName": "22 Karat (916 Hallmark)",
        "karatCode": "22K",
        "purityPercentage": 91.667,
        "hallmarkCode": "916",
        "description": "Standard Indian Gold Ornaments",
        "isActive": true
      }
    ]
  }
  ```

### `POST /purities`
* **Access:** Authenticated
* **Request Body:**
  ```json
  {
    "karat_name": "20 Karat Special",
    "karat_code": "20K",
    "purity_percentage": 83.333,
    "hallmarkCode": "833",
    "description": "Special regional standard"
  }
  ```

---

### `GET /jewellery-categories`
* **Access:** Authenticated
* **Purpose:** Returns active jewellery categories (Rings, Necklaces, Bangles, Chains, Earrings, Coins).
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Categories retrieved successfully",
    "data": [
      {
        "id": 1,
        "categoryName": "Gold Ring",
        "categoryCode": "RNG",
        "defaultHsnCode": "7113",
        "description": "Ladies & Gents Gold Rings",
        "isActive": true
      }
    ]
  }
  ```

### `POST /jewellery-categories`
* **Access:** Authenticated
* **Request Body:**
  ```json
  {
    "category_name": "Mangalsutra",
    "category_code": "MGL",
    "default_hsn_code": "7113",
    "description": "Traditional gold mangalsutra with black beads"
  }
  ```

---

### `GET /daily-gold-rates/today`
* **Access:** Authenticated
* **Purpose:** Returns today's active selling and buying board rates. (Falls back to latest recorded date if today's rate has not been set yet).

### `GET /daily-gold-rates`
* **Access:** Authenticated
* **Query Parameters:** `date` (YYYY-MM-DD), `page` (default: 1)
* **Purpose:** Historical board rate changes log.

### `POST /daily-gold-rates`
* **Access:** Authenticated
* **Purpose:** Set/update the store's board rate for today.
* **Request Body:**
  ```json
  {
    "rate_date": "2026-10-15",
    "metal_type": "gold",
    "purity_id": 2,
    "selling_rate_per_gram": 7350.00,
    "buying_rate_per_gram": 7050.00,
    "notes": "Morning opening bullion board rate"
  }
  ```
  *(Accepts aliases `rate_per_gram` for `selling_rate_per_gram` and `buyback_rate_per_gram` for `buying_rate_per_gram`).*

---

## 4. Business Profile & Settings

### `GET /business-profile`
* **Access:** Authenticated
* **Purpose:** Retrieves store name, GSTIN, legal address, phone, invoice numbering prefix, and GST rates.

### `PUT /business-profile`
* **Access:** Authenticated (Admin/Owner)
* **Request Body:**
  ```json
  {
    "business_name": "Bharat Gold & Diamond Jewellers",
    "tagline": "Purity Guaranteed Since 1985",
    "gstin": "19AAAAA0000A1Z5",
    "pan": "AAAAA0000A",
    "hallmarking_license_no": "HM/WB/2026/889",
    "is_gst_registered": true,
    "address_line1": "124 Bowbazar Street",
    "city": "Kolkata",
    "state_id": 1,
    "state_code": 19,
    "pincode": "700012",
    "phone": "033-22445566",
    "email": "contact@bharatjewellers.com",
    "invoice_prefix": "INV-",
    "purchase_prefix": "PUR-",
    "stock_entry_prefix": "STK-",
    "gold_gst_rate": 3.00,
    "terms_and_conditions": "Subject to Kolkata Jurisdiction. Goods once sold will be exchanged within 7 days against original invoice."
  }
  ```

---

## 5. Parties CRM (Customers, Vendors, Karigars)

### `GET /parties`
* **Access:** Authenticated
* **Query Parameters:**
  * `type` (optional): `customer` | `vendor` | `karigar`
  * `search` (optional): Matches name, phone, or GSTIN
  * `per_page` (optional, default: 30)
  * `page` (optional)
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Parties retrieved successfully",
    "data": [
      {
        "id": 1,
        "partyName": "Arijit Das",
        "partyType": "customer",
        "phone": "9830123456",
        "email": "arijit@example.com",
        "address": "Flat 4B, Salt Lake Sector 1",
        "city": "Kolkata",
        "stateId": 1,
        "stateCode": 19,
        "pincode": "700064",
        "gstin": null,
        "pan": "ABCDE1234F",
        "openingBalance": 0.00,
        "currentBalance": 15000.00,
        "metalOpeningBalanceGrams": 0.000,
        "metalCurrentBalanceGrams": 0.000,
        "creditLimit": 100000.00,
        "isActive": true
      }
    ]
  }
  ```

### `POST /parties`
* **Access:** Authenticated
* **Request Body:**
  ```json
  {
    "party_name": "Shree Ganesh Bullion",
    "party_type": "vendor",
    "phone": "9830998877",
    "email": "sales@shreeganesh.com",
    "city": "Kolkata",
    "state_id": 1,
    "state_code": 19,
    "gstin": "19AABCU9603R1ZM",
    "pan": "AABCU9603R",
    "opening_balance": 0.00
  }
  ```

### `GET /parties/{id}`
* **Purpose:** Party details including transaction history (`sales`, `purchases`, `payments`).

### `PUT /parties/{id}`
* **Purpose:** Update party contact or balance information.

### `DELETE /parties/{id}`
* **Errors:** `409 Conflict` if the party has existing sales or purchase transaction history.

---

## 6. Jewellery Items & Tags (Showroom Inventory)

### `GET /jewellery-items`
* **Access:** Authenticated
* **Query Parameters:**
  * `status` (optional, default: `in_stock`): `in_stock` | `sold` | `returned` | `reserved` | `all`
  * `category_id` (optional): Filter by category
  * `purity_id` (optional): Filter by purity (e.g. 22K)
  * `search` (optional): Item name, `tag_code`, or `huid`
  * `per_page` (optional, default: 30)
  * `page` (optional)
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Jewellery items retrieved successfully",
    "data": [
      {
        "id": 55,
        "tagCode": "TAG-261015-1042",
        "itemName": "Traditional Floral Kada",
        "categoryId": 3,
        "category": { "id": 3, "categoryName": "Bangle / Kada" },
        "purityId": 2,
        "purity": { "id": 2, "karatCode": "22K", "purityPercentage": 91.667 },
        "hsnCode": "7113",
        "grossWeight": 24.500,
        "stoneWeight": 0.000,
        "netGoldWeight": 24.500,
        "fineGoldWeight": 22.458,
        "stoneValue": 0.00,
        "purchaseCost": 180000.00,
        "huid": "AB1234",
        "sourceType": "production",
        "status": "in_stock",
        "soldDate": null,
        "notes": null
      }
    ]
  }
  ```

### `GET /jewellery-items/tag/{tagCode}`
* **Access:** Authenticated
* **Purpose:** Direct barcode / RFID tag scan lookup. Ideal for POS scanning!
* **Response (200 OK):** Returns single `JewelleryItemResource`.
* **Errors:** `404 Not Found` if tag is unregistered.

### `POST /jewellery-items`
* **Access:** Authenticated
* **Purpose:** Register new piece in inventory (calculates `net_gold_weight` automatically).
* **Request Body:**
  ```json
  {
    "tag_code": "TAG-RNG-916-0042",
    "item_name": "Solitaire Men's Gold Ring",
    "category_id": 1,
    "purity_id": 2,
    "hsn_code": "7113",
    "gross_weight": 7.450,
    "stone_weight": 0.200,
    "stone_value": 3500.00,
    "purchase_cost": 54800.00,
    "huid": "KL9876",
    "source_type": "production"
  }
  ```

### `PUT /jewellery-items/{id}`
* **Purpose:** Edit weights, stone cost, or HUID code.

### `DELETE /jewellery-items/{id}`
* **Errors:** `409 Conflict` if the item has already been marked as `sold`.

---

## 7. Stock Entries (Inward from Karigars)

### `GET /stock-entries`
* **Query Parameters:** `type` (optional), `page` (optional)
* **Response:** Paginated list of inward batches with artisan relations.

### `POST /stock-entries`
* **Purpose:** Workshop / Karigar batch inward. Automatically creates showroom inventory items with barcode tags!
* **Request Body:**
  ```json
  {
    "entry_date": "2026-10-15",
    "entry_type": "production_inward",
    "artisan_id": 5,
    "remarks": "Diwali Special Choker Necklace batch",
    "items": [
      {
        "category_id": 2,
        "purity_id": 2,
        "item_name": "Kundan Choker Necklace",
        "tag_code": "TAG-CHK-2026-001",
        "gross_weight": 42.500,
        "stone_weight": 4.200,
        "estimated_rate_per_gram": 7350.00,
        "making_cost": 12500.00,
        "stone_cost": 6500.00,
        "huid": "MN5678"
      }
    ]
  }
  ```

---

## 8. Procurement & Purchases

### `GET /purchases`
* **Query Parameters:** `party_id`, `type`, `page`
* **Purpose:** List vendor bullion and wholesale jewellery purchase bills.

### `POST /purchases`
* **Purpose:** Record gold purchase from wholesale merchant or bullion bank.
* **Request Body:**
  ```json
  {
    "purchase_date": "2026-10-15",
    "vendor_bill_no": "WB/2026/894",
    "vendor_bill_date": "2026-10-14",
    "party_id": 2,
    "purchase_type": "bullion_raw_gold",
    "is_gst_applicable": true,
    "tax_type": "intra_state",
    "paid_amount": 50000.00,
    "payment_account_id": 2,
    "payment_mode": "bank_transfer",
    "items": [
      {
        "purity_id": 1,
        "item_description": "24K Gold 100g Bar",
        "gross_weight": 100.000,
        "stone_weight": 0.000,
        "rate_per_gram": 7800.00,
        "making_charges": 0.00,
        "stone_charges": 0.00,
        "gst_rate": 3.00
      }
    ]
  }
  ```

### `POST /purchases/{id}/cancel`
* **Purpose:** Cancel a purchase invoice. Checks if any generated items were sold, reverts stock, and rolls back vendor payable balance.
* **Request Body:**
  ```json
  {
    "reason": "Supplier delivered incorrect hallmark purity certificate"
  }
  ```

---

## 9. Point of Sale (POS) & Sales

### `GET /sales`
* **Query Parameters:** `party_id`, `status` (`paid`, `unpaid`, `partially_paid`), `from_date`, `to_date`, `page`

### `GET /sales/{id}`
* **Purpose:** Full sales invoice with all items, old gold exchange details, tax breakup, and customer info.

### `POST /sales` (Generate POS Invoice)
* **Purpose:** The core retail billing endpoint. Supports barcode inventory items, custom ornaments, old gold trade-in buyback deduction, GST tax calculation, and payment drawer logging.
* **Request Body:**
  ```json
  {
    "invoice_date": "2026-10-15",
    "party_id": 1,
    "customer_name": "Priya Sen",
    "customer_mobile": "9831122334",
    "customer_email": "priya.sen@example.com",
    "customer_address": "45 Park Street, Kolkata",
    "customer_pan": "ABCDE1234F",
    "place_of_supply_state_id": 1,
    "place_of_supply_state_code": 19,
    "invoice_type": "tax_invoice",
    "is_gst_applicable": true,
    "discount_amount": 1000.00,
    "paid_amount": 65000.00,
    "payment_account_id": 1,
    "payment_mode": "cash",
    "notes": "Thank you for shopping with us",
    "items": [
      {
        "jewellery_item_id": 55,
        "category_id": 3,
        "purity_id": 2,
        "item_name": "Traditional Floral Kada",
        "tag_code": "TAG-261015-1042",
        "huid": "AB1234",
        "gross_weight": 24.500,
        "stone_weight": 0.000,
        "gold_rate_per_gram": 7350.00,
        "making_charge_type": "per_gram",
        "making_charge_rate": 650.00,
        "hallmarking_charge": 45.00,
        "gst_rate": 3.00
      }
    ],
    "old_gold_exchanges": [
      {
        "item_description": "Old 22K Broken Chain",
        "gross_weight": 8.500,
        "dust_stone_weight": 0.300,
        "purity_percentage": 91.667,
        "buying_rate_per_gram": 7050.00,
        "remarks": "Customer exchange trade-in"
      }
    ]
  }
  ```
* **Critical Legal Validation Rule:**
  * If `payment_mode === 'cash'` and `paid_amount >= 200000`, `customer_pan` is **strictly required**. Omitting it returns HTTP 422: `"Customer PAN is legally mandatory for cash payments of ₹2,00,000 or more."`
* **Response (201 Created):** Returns full `SaleResource` with computed grand total, net payable, round-off, CGST/SGST amounts, and changes item status to `sold`.

### `POST /sales/{id}/cancel`
* **Purpose:** Cancel a sale invoice. Automatically reverts sold items back to `in_stock`, restores customer balance, and records cancellation audit.
* **Request Body:**
  ```json
  {
    "reason": "Customer cancelled order before delivery"
  }
  ```

---

## 10. Sales Returns & Credit Notes

### `GET /sales-returns`
* **Query Parameters:** `party_id`, `sale_id`, `from_date`, `to_date`, `page`

### `POST /sales-returns`
* **Purpose:** Issue a GST-compliant Credit Note against a sale invoice. Restocks returned jewellery and manages cash/ledger refunds.
* **Request Body:**
  ```json
  {
    "sale_id": 105,
    "credit_note_date": "2026-10-15",
    "party_id": 1,
    "refund_mode": "cash_refund",
    "refund_account_id": 1,
    "reason": "Size misfit, exchanged for larger size",
    "items": [
      {
        "sale_item_id": 12,
        "jewellery_item_id": 55,
        "category_id": 3,
        "purity_id": 2,
        "item_name": "Traditional Floral Kada",
        "gross_weight": 24.500,
        "stone_weight": 0.000,
        "rate_per_gram": 7350.00,
        "making_charges_refunded": 15925.00,
        "gst_rate": 3.00,
        "stock_action": "restocked_as_original"
      }
    ]
  }
  ```

---

## 11. Accounts & Cash Drawer

### `GET /accounts`
* **Query Parameters:** `type` (`cash`, `bank`, `direct_expense`, etc.), `is_cash=1`, `is_bank=1`, `search`
* **Purpose:** Returns list of accounts in the Chart of Accounts with live current balances.

### `GET /accounts/drawer/cash-summary`
* **Query Parameters:** `date` (YYYY-MM-DD)
* **Purpose:** Real-time cash-in-hand counter drawer summary for opening, today's cash sales receipts, cash vendor payments, and closing balance.
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Cash drawer summary retrieved",
    "data": {
      "asOfDate": "2026-10-15",
      "totalCashBalance": 128450.00,
      "totalBankBalance": 850000.00,
      "totalLiquidFunds": 978450.00,
      "todayCashInflow": 65000.00,
      "todayCashOutflow": 1200.00,
      "netCashMovement": 63800.00,
      "cashAccounts": [ ... ],
      "bankAccounts": [ ... ]
    }
  }
  ```

### `GET /accounts/{id}/statement`
* **Query Parameters:** `from_date`, `to_date`, `page`
* **Purpose:** Ledger statement with debit/credit entries, running balance, and voucher references.

---

## 12. Payments & Vouchers

### `GET /payments`
* **Query Parameters:** `type` (`receipt` | `payment`), `party_id`, `account_id`, `mode`, `from_date`, `to_date`

### `POST /payments`
* **Purpose:** Record manual payment voucher or money receipt (e.g. customer paying old balance, advance deposit, or vendor settlement).
* **Request Body:**
  ```json
  {
    "payment_date": "2026-10-15",
    "payment_type": "receipt",
    "account_id": 1,
    "party_id": 1,
    "payment_mode": "upi",
    "transaction_reference": "UPI/26101598442",
    "amount": 25000.00,
    "notes": "Advance for Diwali Lakshmi Gold Coin"
  }
  ```

---

## 13. Operating Expenses & Incomes

### `GET /expense-incomes`
* **Query Parameters:** `type` (`expense` | `income`), `account_id`, `from_date`, `to_date`

### `POST /expense-incomes`
* **Purpose:** Record daily showroom operational expenses (electricity, tea, security, staff refreshments, store rent) with GST input tax credit claim.
* **Request Body:**
  ```json
  {
    "entry_date": "2026-10-15",
    "entry_type": "expense",
    "account_id": 18,
    "payment_account_id": 1,
    "amount": 1800.00,
    "payment_mode": "cash",
    "description": "Showroom cleaning materials and AC maintenance",
    "is_gst_claimed": true,
    "tax_amount": 274.58,
    "vendor_gstin": "19ABCDE1234F1Z5"
  }
  ```

### `DELETE /expense-incomes/{id}`
* **Purpose:** Deletes expense entry and reverts posted financial voucher entries.

---

## 14. Statutory GST Reports

### `GET /gst/status`
* **Purpose:** Check organization GSTIN, registration status, default HSN code (`7113`), and statutory tax percentages.

### `GET /gst/gstr1-summary`
* **Query Parameters:** `from_date` (YYYY-MM-DD), `to_date` (YYYY-MM-DD)
* **Purpose:** GSTR-1 Outward Supplies return summary:
  * Table 4: B2B Invoices (Customers with 15-digit GSTIN)
  * Table 5: B2CL (Inter-state invoices > ₹2.5 Lakhs to unregistered customers)
  * Table 7: B2CS (Retail sales intra-state and inter-state <= ₹2.5 Lakhs)
  * Table 9B: CDNR / CDNUR (Credit Notes from Sales Returns)
  * Table 12: HSN Summary (HSN 7113 articles of jewellery, total gross weight, net weight, taxable value, CGST/SGST/IGST).

### `GET /gst/gstr3b-summary`
* **Query Parameters:** `from_date` (YYYY-MM-DD), `to_date` (YYYY-MM-DD)
* **Purpose:** GSTR-3B Tax Liability & Input Tax Credit (ITC):
  * Table 3.1: Net Outward Taxable Supplies (Gross Sales minus Sales Returns).
  * Table 4: Eligible Input Tax Credit (Purchases from GST registered suppliers + GST Claimed Expenses).
  * Net Cash Tax Payable: $\text{Output Tax Liability} - \text{Eligible ITC}$.

---

## 15. Administration

### Reference Data:
* `GET /user-types`: User roles list (`Admin`, `Developer`, `Owner`, `Staff`).
* `GET /designations`: Designation master list.
* `GET /departments`: Department master list.
* `GET /genders`: Gender reference.
* `GET /states`: Indian States list with 2-digit GST state codes (e.g. 19 for West Bengal, 27 for Maharashtra).
* `GET /states/{stateId}`: State details.
* `GET /districts`: Districts list.

### Tier 1 Restricted (Admin / Developer / Owner):
* `GET /users`: List system users with employee links.
* `POST /users`: Provision new employee account.
* `GET /employees`: Employee roster.
* `POST /employees`: Add new employee.
* `PUT /employees/{employeeId}`: Update employee.
* `DELETE /employees/{employeeId}`: Remove employee.
* `GET /backups`: List existing `.sql` database backup dumps.
* `POST /backups`: Trigger an instant MySQL database backup dump.
* `GET /backups/{filename}`: Download `.sql` file.
* `DELETE /backups/{filename}`: Delete a backup file.
