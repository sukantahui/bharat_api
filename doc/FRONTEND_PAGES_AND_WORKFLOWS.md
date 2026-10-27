# 🖥️ Bharat Jewellery ERP & POS — Frontend Pages & UI/UX Workflows

> **Purpose:** Detailed page-by-page design specifications, component breakdowns, state management flows, calculations, and modal wireframes for the React frontend agent.

---

## 🎨 Design Philosophy & Aesthetic Guidelines

Jewellery retail and wholesale software demands an interface that evokes **prestige, trust, precision, and elegance**:
* **Color Palette:**
  * **Deep Midnight Slate:** `#0f172a` (Sidebar, Dark mode, Table headers)
  * **Warm Champagne Gold:** `#d97706`, `#f59e0b`, `#fbbf24` (Accents, active buttons, gold rate ticker, primary badges)
  * **Silken Pearl White:** `#f8fafc`, `#ffffff` (Card backgrounds, clean invoice layout)
  * **Emerald Success:** `#10b981` (Paid status, in-stock badges, cash drawer inflow)
  * **Ruby Crimson:** `#ef4444` (Unpaid alerts, cancel invoice, delete confirmation)
* **Typography:** Modern, crisp sans-serif e.g. `Inter`, `Outfit`, or `Plus Jakarta Sans`. Numeric columns (weights, prices, HUIDs, tags) must use tabular monospace or tabular lining figures (`font-mono` or `tabular-nums`) for perfect alignment.
* **Layout Structure:**
  * Collapsible Sidebar (Left) with categorized navigation groups:
    * **Retail POS:** New Sale (POS), Sales Invoices, Sales Returns (Credit Notes)
    * **Inventory & Workshop:** Jewellery Catalog, Barcode Tags, Karigar Inward (Stock Entry), Daily Gold Rates
    * **Procurement & CRM:** Purchases, Parties (Customers / Vendors / Karigars)
    * **Finance & Cash Drawer:** Cash Drawer Summary, Bank Accounts, Payments / Receipts, Expenses & Incomes
    * **Compliance:** GSTR-1 Outward Supplies, GSTR-3B Tax Summary
    * **Administration:** Business Profile, Employee Roster, User Accounts, Database Backups
  * Top Navigation Bar (Header):
    * Live Gold Rate Ticker (24K: ₹7,950/g • 22K: ₹7,350/g) with quick "Update Board Rate" shortcut.
    * Cash-in-Hand Drawer indicator widget with quick drawer status.
    * User profile dropdown (Logged-in employee name, role badge, Logout button).

---

## 1. Authentication (`LoginPage.tsx`)

### Layout & Elements:
* Centered card on a subtle deep-slate textured background with a subtle gold glow effect.
* Jewellery store branding (Logo, "Bharat Jewellery ERP", "Purity & Trust").
* Form Fields:
  * `Username / Email` (Input with user icon, autoFocus)
  * `Password` (Input with eye icon to toggle visibility)
* "Remember Session" checkbox.
* "Sign In to ERP Terminal" button with loading spinner when request is pending.
* Error alert box displaying `errors` or `message` on 401/422.

### State & Workflow:
1. User submits form $\to$ Trigger `POST /api/login`.
2. On success $\to$ Save `token` in `localStorage` (`bharat_token`), store `user` object in Zustand `authStore`, trigger notification `"Welcome back, [Name]"`, and redirect to `/dashboard`.
3. If rate limited (HTTP 429) $\to$ Show countdown timer: `"Too many login attempts. Please wait 60 seconds."`

---

## 2. Executive Dashboard (`DashboardPage.tsx`)

### Sections:
1. **Top Metric Cards (KPI Row):**
   * **Today's Gross Sales:** Value in ₹ (`todayStats.salesAmount`), sales count (`todayStats.salesCount`), and total gold weight sold (`todayStats.salesNetWeightGrams` g).
   * **Today's Collections:** Collected amount in ₹ (`todayStats.salesCollectedAmount`), showing cash vs. bank split.
   * **Today's Purchases:** Bullion / ornament procurement cost in ₹ (`todayStats.purchasesAmount`).
   * **Cash Drawer Balance:** Total liquid funds in ₹ (`cashDrawer.totalLiquidFunds`) with cash-in-hand and bank balances.
2. **Live Gold Board Rates Ticker:**
   * Cards displaying 24K Bullion (999), 22K Hallmark (916), 18K (750), and 14K (585) selling & buying rates per gram.
   * "Change Today's Rate" button opening the rate modal.
3. **Showroom Stock Distribution:**
   * Donut chart (Recharts) showing inventory split by category (Rings, Bangles, Necklaces, Chains, Coins).
   * Quick metrics: Total pieces in stock (`inventorySummary.inStockItemsCount`), total showroom gross weight (`inventorySummary.inStockGrossWeight` g), total net gold weight (`inventorySummary.inStockNetWeight` g).
4. **Recent Sales Activity Table:**
   * Shows last 5 invoices: Invoice No, Customer, Net Weight, Total (₹), Payment Status, Date.
   * "View / Print Invoice" button directly navigating to invoice preview.
5. **Quick Action Shortcuts:**
   * `[+ New POS Sale]`, `[+ Scan Barcode Tag]`, `[+ Inward Stock]`, `[+ Record Expense]`, `[+ Cash Receipt]`.

---

## 3. Point of Sale Terminal (`PosBillingPage.tsx`) — Core Retail Workflow

The heart of the entire application. Designed for high-speed, mistake-proof checkout during busy festival and wedding seasons.

### UI Layout (Two Columns):

```
┌─────────────────────────────────────────────────────────┬──────────────────────────────────┐
│  LEFT COLUMN: Bill Items & Scanning (65% width)        │  RIGHT COLUMN: Summary & Payment │
│  ┌───────────────────────────────────────────────────┐  │  ┌────────────────────────────┐  │
│  │ 1. Customer Selection / Quick Add Modal           │  │  │ Invoice Type: [Tax / Memo] │  │
│  └───────────────────────────────────────────────────┘  │  ├────────────────────────────┤  │
│  ┌───────────────────────────────────────────────────┐  │  │ Taxable Value:     ₹45,000 │  │
│  │ 2. Barcode Scan / Tag Lookup Input [TAG-XXXXX]    │  │  │ Making Charges:     ₹4,200 │  │
│  │    [+] Manual Item Entry [+] Open Catalog Modal   │  │  │ Stone Value:          ₹800 │  │
│  └───────────────────────────────────────────────────┘  │  │ Hallmarking:           ₹45 │  │
│  ┌───────────────────────────────────────────────────┐  │  │ Discount:           -₹500 │  │
│  │ 3. Active Cart Items Table                        │  │  │ CGST (1.5%):        ₹743 │  │
│  │    Tag | Item | Purity | Gross | Stone | Net | Rate│  │  │ SGST (1.5%):        ₹743 │  │
│  │    Making | GST | Line Total | [Delete]           │  │  ├────────────────────────────┤  │
│  └───────────────────────────────────────────────────┘  │  │ Subtotal:          ₹51,031 │  │
│  ┌───────────────────────────────────────────────────┐  │  │ Old Gold Trade-In: -₹15,000│  │
│  │ 4. Old Gold Exchange / Buyback Accordion Section   │  │  │ Round Off:          -₹0.31│  │
│  │    [+ Add Old Gold Scrap Item]                    │  │  ├────────────────────────────┤  │
│  │    Desc | Gross Wt | Dust | Purity % | Rate | Total│  │  │ NET GRAND TOTAL:  ₹36,031 │  │
│  └───────────────────────────────────────────────────┘  │  ├────────────────────────────┤  │
│                                                         │  │ Payment Mode: [Cash / UPI] │  │
│                                                         │  │ Customer PAN: [Required]   │  │
│                                                         │  │ [COMPLETE SALE & PRINT]    │  │
│                                                         │  └────────────────────────────┘  │
└─────────────────────────────────────────────────────────┴──────────────────────────────────┘
```

### Detailed POS Features & Edge Cases:

1. **Barcode / Tag Scan Auto-Fill:**
   * When user scans or types a tag code (e.g. `TAG-261015-1042`) and hits Enter:
   * Frontend immediately calls `GET /api/jewellery-items/tag/{tagCode}`.
   * If found and `status === 'in_stock'`:
     * Item is added to cart table.
     * `gross_weight`, `stone_weight`, `category_id`, `purity_id`, `huid`, and `stone_value` are automatically filled.
     * Gold rate per gram defaults to today's board rate for that item's purity.
     * Calculates: $\text{Net Gold Weight} = \text{Gross Weight} - \text{Stone Weight}$.
     * Calculates: $\text{Gold Amount} = \text{Net Weight} \times \text{Rate Per Gram}$.
     * Making charges computed according to type (`per_gram` e.g. ₹600/g, `percentage` e.g. 10%, or `fixed`).
   * If item is already sold or reserved $\to$ Show red toast: `"Item [TAG] is already sold on [Date]"`.

2. **Old Gold Exchange (Trade-In / Buyback):**
   * Customer brings old jewellery to exchange against new purchase.
   * User adds Old Gold Item:
     * Description (e.g. "Old 22K Hallmarked Chain").
     * Gross Weight (e.g. `8.500` g).
     * Dust / Melting / Stone deduction (e.g. `0.300` g).
     * Net Gold Weight = `8.200` g.
     * Purity percentage (defaults to 91.667% for 22K or selectable purity).
     * Fine Gold Weight = $\text{Net Weight} \times (\text{Purity} / 100)$.
     * Buying Rate per gram = Today's buying board rate.
     * Exchange Value = $\text{Net Weight} \times \text{Buying Rate}$.
   * **Exchange total is subtracted directly from the invoice payable amount.**

3. **Tax & Legal PAN Rule Verification:**
   * Inter-state vs. Intra-state GST auto-detection: If customer place of supply is outside West Bengal (State Code $\neq 19$), apply 3% IGST. Otherwise apply 1.5% CGST + 1.5% SGST.
   * **PAN Mandatory Check:**
     * If `payment_mode === 'cash'` and `paid_amount >= 200000`, the `customer_pan` field must be non-empty and match Indian PAN format (`[A-Z]{5}[0-9]{4}[A-Z]{1}`).
     * If missing, disable the "Complete Sale" button and show prompt: *"Indian Income Tax Law requires Customer PAN for cash transactions of ₹2 Lakhs or more."*

4. **Printable Invoice Modal:**
   * Upon successful sale (`POST /api/sales` returns HTTP 201):
   * Open Print Preview Modal with toggle between:
     * **A4 / A5 Official GST Tax Invoice:** Full company header, GSTIN, HSN Code 7113, HUID, hallmark logos, breakup of gold value vs making charges, terms and customer sign block.
     * **Thermal Receipt (3-inch / 80mm):** Compact POS receipt for thermal counter printers.

---

## 4. Showroom Inventory & Barcode Catalog (`InventoryListPage.tsx`)

### Key Features:
* **Status Filter Tabs:** `In Stock` (default), `Sold`, `Returned`, `Reserved`, `All`.
* **Category & Purity Dropdown Filters:** Fast filtering by Karat (24K, 22K, 18K) or Category (Rings, Bangles, etc.).
* **Search Bar:** Real-time debounced search by tag code, HUID, or item title.
* **Inventory Data Table:**
  * Tag Code (with copy icon and click-to-print barcode sticker)
  * Item Name & Category
  * Purity Badge (e.g. `22K (916)` in amber badge)
  * HUID Badge (e.g. `HM-AB1234`)
  * Gross Wt (g), Stone Wt (g), Net Gold Wt (g), Fine Wt (g)
  * Purchase Cost (₹)
  * Status Badge (`in_stock` = green, `sold` = blue, `returned` = purple)
  * Actions: View Details, Edit Item, Delete Item (blocked if sold).
* **"Add New Jewellery Item" Drawer / Modal:** Allows adding pieces received from other sources or opening inventory.

---

## 5. Daily Gold Board Rates (`DailyRatesPage.tsx`)

### Key Features:
* **"Today's Active Rates" Card:** Big display of today's 24K Bullion, 22K Hallmark, 18K Jewellery rates.
* **"Set Today's Rates" Modal:**
  * Date (defaults to today)
  * Table of purities:
    * 24 Karat (999): Selling Rate (₹/g), Buying / Melt Rate (₹/g)
    * 22 Karat (916): Selling Rate (₹/g), Buying / Melt Rate (₹/g)
    * 18 Karat (750): Selling Rate (₹/g), Buying / Melt Rate (₹/g)
    * 14 Karat (585): Selling Rate (₹/g), Buying / Melt Rate (₹/g)
  * "Save & Broadcast Board Rate" button $\to$ submits `POST /api/daily-gold-rates`.
* **Historical Rate Chart & Table:** Line chart showing gold price trends over the last 30/90 days.

---

## 6. Stock Entry / Karigar Production Inward (`CreateStockEntryPage.tsx`)

### Key Features:
* Handles batches of freshly crafted jewellery received from workshop goldsmiths / karigars.
* Header: Entry Date, Karigar / Artisan selector (`GET /api/parties?type=karigar`), Remarks.
* Item Repeater Grid:
  * Category, Purity, Item Name, Gross Weight (g), Stone Weight (g), Stone Cost (₹), Making Cost (₹), HUID.
  * Tag code auto-generator (e.g. `TAG-261015-0001`).
* Summary Bar: Total pieces, total gross weight, total net gold weight, total estimated valuation.
* On Submit $\to$ Calls `POST /api/stock-entries`.
* Success $\to$ Show batch receipt with option to **"Print All Barcode Stickers for this Batch"**.

---

## 7. Procurement & Purchases (`PurchaseListPage.tsx` & `CreatePurchasePage.tsx`)

### Key Features:
* Handles purchases of:
  * Bullion bars (24K fine gold from banks / bullion dealers)
  * Finished ornaments from wholesale jewellery manufacturers
  * Customer scrap old gold buybacks
* Form Inputs:
  * Vendor Selection (`GET /api/parties?type=vendor`)
  * Vendor Bill No & Vendor Bill Date
  * GST Applicable toggle (Auto-computes ITC eligibility)
  * Purchase Items grid: Purity, description, gross weight, net weight, rate/g, making charges, GST rate.
  * Payment section: Amount paid immediately, Payment Account (Cash / Bank account), Payment Mode.
* **Cancel Purchase Modal:**
  * Accessible from Purchase List / Detail page.
  * Prompts for cancellation reason.
  * Calls `POST /api/purchases/{id}/cancel`.
  * Safely verifies no purchased items were sold before rolling back stock and vendor payable balance.

---

## 8. Sales Returns & Credit Notes (`SalesReturnListPage.tsx` & `CreateCreditNotePage.tsx`)

### Key Features:
* Select original invoice number $\to$ Frontend fetches `GET /api/sales/{id}`.
* Displays all items from original invoice with checkboxes.
* User selects item(s) being returned.
* Option to refund making charges (partial or full).
* **Restock Action Selector:**
  * `Restock as Original in Showroom` (reverts status to `in_stock`)
  * `Send to Workshop for Melting / Scrap`
  * `Send for Repair`
* Refund Mode:
  * `Credit to Customer Ledger Balance`
  * `Immediate Cash Refund from Drawer`
  * `Bank Transfer Refund`
* Submits `POST /api/sales-returns` $\to$ generates Credit Note (e.g. `CRN-2026-0001`) with printable Credit Note voucher.

---

## 9. Parties CRM (`PartyListPage.tsx` & `PartyDetailPage.tsx`)

### Key Features:
* Tabbed views: **Customers**, **Vendors**, **Karigars (Artisans)**.
* Key Columns: Name, Mobile, City, GSTIN, PAN, Financial Balance (₹ Receivable / Payable), Metal Balance (Grams of Gold).
* Quick Filters: Outstanding Balance (> 0), Metal Due (> 0).
* **Party Detail Page:**
  * Contact info & GST details card.
  * Ledger Balance card with `[Receive Payment]` or `[Make Payment]` button.
  * Embedded Tabs:
    * Sales Invoices History
    * Purchases / Stock Inward History
    * Payment Receipts / Vouchers History
    * Account Statement with date filter.

---

## 10. Financial Accounts & Cash Drawer (`CashDrawerPage.tsx` & `AccountsListPage.tsx`)

### Key Features:
* **Live Cash Drawer Widget:**
  * Opening Cash Balance
  * Today's Cash Sales Receipts (+)
  * Today's Customer Advance / Old Debt Collections (+)
  * Today's Cash Expenses (-)
  * Today's Cash Vendor Payments (-)
  * Current Closing Cash-in-Hand Balance
* **Bank Accounts Summary:**
  * Current balances of Current Account, Savings Account, UPI Settlement Account.
* **"New Payment Voucher" Modal:**
  * Type: `Receipt (Money Received)` or `Payment (Money Paid Out)`
  * Account: Cash Drawer or Bank Account
  * Party: Select Customer / Vendor / Karigar
  * Mode: Cash, Bank Transfer, UPI, Cheque
  * Reference ID / Transaction Ref
* **Account Statement Modal:**
  * Date range selector $\to$ shows debits, credits, running balance, and voucher narrative.

---

## 11. Operating Expenses & Incomes (`ExpenseIncomePage.tsx`)

### Key Features:
* Record daily showroom expenses:
  * Category Head: Showroom Tea & Refreshments, Electricity Bill, Staff Welfare, Store Maintenance, Security, Advertising.
  * Payment Account: Cash Drawer or Bank Account.
  * GST Claim checkbox: If vendor is registered, input GSTIN and tax amount to claim Input Tax Credit (ITC) for GSTR-3B.
* Monthly Expense vs. Income bar chart.
* Export to Excel / CSV.

---

## 12. Statutory GST Reports (`Gstr1ReportPage.tsx` & `Gstr3bReportPage.tsx`)

### GSTR-1 Page (Outward Supplies):
* Month / Quarter selector.
* Tabbed GSTR tables:
  * **Table 4 (B2B):** Invoices issued to customers with valid 15-digit GSTIN. Shows Taxable Value, CGST, SGST, IGST.
  * **Table 5 (B2CL):** Inter-state invoices > ₹2.5 Lakhs to unregistered buyers.
  * **Table 7 (B2CS):** All retail intra-state consumer sales aggregated by tax rate (3%).
  * **Table 9B (CDNR / CDNUR):** Credit notes issued against sales returns.
  * **Table 12 (HSN Summary):** HSN 7113 summary with UQC (GMS), total gross weight, net gold weight, taxable value, and tax breakdown.
* "Export GSTR-1 JSON for GST Portal" button.

### GSTR-3B Page (Monthly Tax Summary):
* **Table 3.1: Output Tax Liability** (Gross Sales minus Sales Returns).
* **Table 4: Eligible Input Tax Credit** (Purchases from registered vendors + Claimed showroom expenses).
* **Net Cash Tax Payable Calculation:**
  $$\text{Net CGST Payable} = \text{Output CGST} - \text{Eligible CGST ITC}$$
  $$\text{Net SGST Payable} = \text{Output SGST} - \text{Eligible SGST ITC}$$
* Clear visual summary of whether store owes tax or has surplus ITC to carry forward.

---

## 13. Settings & Administration (`SettingsPage.tsx`)

### Sub-tabs:
1. **Business Profile:** Edit store name, tagline, GSTIN, PAN, license number, default prefixes (`INV-`, `PUR-`, `STK-`), invoice footer terms.
2. **Employee Roster:** List employees, assign departments and designations.
3. **User Management:** Create employee login accounts with assigned role (`Admin`, `Developer`, `Owner`, `Staff`).
4. **Database Backups:**
   * One-click "Create Database Backup Now" button $\to$ executes `POST /api/backups`.
   * Table of existing `.sql` backup files with size and timestamp.
   * Direct download link (`GET /api/backups/{filename}`) and delete button.
