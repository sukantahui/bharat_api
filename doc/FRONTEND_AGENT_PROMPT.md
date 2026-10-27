# 🤖 Frontend Agent Prompt: Build Bharat Jewellery ERP & POS

> **Instructions for the User:**  
> Copy and paste the entire prompt below directly into your Frontend AI Agent (in Antigravity, Cursor, Windsurf, Claude Code, or Copilot) to generate the complete React frontend application.

---

```markdown
# MISSION PROMPT: Build Production-Ready Frontend for Bharat Jewellery ERP & POS

You are a Principal Frontend Architect and Senior React Engineer. Your objective is to build a complete, state-of-the-art, production-grade Enterprise Resource Planning (ERP) and Point of Sale (POS) retail web application for "Bharat Jewellery" using React (latest), TypeScript, and Tailwind CSS.

The backend API is fully implemented and running locally in Laravel 12.x. All API documentation, database schemas, TypeScript definitions, page workflows, and API clients have been prepared for you in the `doc/` directory of the backend repository:
- `doc/README.md` — Master Architecture Guide
- `doc/API_REFERENCE.md` — Complete 77+ Endpoints Reference
- `doc/FRONTEND_PAGES_AND_WORKFLOWS.md` — Screen-by-Screen UI/UX Specifications
- `doc/DATA_MODELS_AND_TYPES.ts` — Ready-to-use TypeScript definitions
- `doc/API_CLIENT_EXAMPLE.ts` — Ready-to-use Axios client & React Query hooks

---

## 1. TECHNICAL STACK & REQUIREMENTS

1. **Framework:** React 19 / latest with Vite (`npm create vite@latest frontend -- --template react-ts`)
2. **Language:** TypeScript 5.x (Strict mode)
3. **Styling:** Tailwind CSS (v3 or v4) + Curated Premium Jewellery Design System:
   - Primary Background: Deep Slate `#0f172a` (Sidebar, Dark accents) & Pure White `#ffffff` (Card sheets)
   - Accent & Gold Highlights: Warm Champagne Gold `#d97706`, `#f59e0b`, `#fbbf24`
   - Numeric & Table Fonts: Tabular Monospace / Lining Figures for weights (e.g. `24.500 g`) and prices (`₹1,85,400.00`)
4. **Icons:** `lucide-react`
5. **State Management:**
   - Server Cache: `@tanstack/react-query` (v5)
   - Client / Session State: `zustand` (Auth session, active POS cart)
6. **HTTP Client:** `axios` with Sanctum Bearer token interceptor and 401 redirect
7. **Form Management:** `react-hook-form` + `zod`
8. **Print & Invoice Export:** `react-to-print` (Supporting both A4/A5 GST Tax Invoice & 80mm Thermal Slip)
9. **Charts & Analytics:** `recharts`
10. **Notifications:** `sonner` or `react-hot-toast`

---

## 2. BACKEND API INTEGRATION DETAILS

* **Base URL:** `http://localhost/bharat_api/public/api` (or `http://127.0.0.1:8000/api`)
* **Auth Protocol:** Laravel Sanctum Bearer Token
* **Auth Endpoints:**
  * Login: `POST /login` $\to$ returns `{ status: true, data: { token, user } }`
  * Session Check: `GET /me`
  * Logout: `POST /logout`
* **Response Envelope Format:** Every API response wraps data in:
  ```json
  {
    "status": true,
    "message": "...",
    "data": { ... }
  }
  ```
  Validation errors return HTTP 422 with `{ "status": false, "message": "...", "errors": { ... } }`.

---

## 3. CORE PAGES & MODULES TO IMPLEMENT

Implement the following comprehensive modules based on `doc/FRONTEND_PAGES_AND_WORKFLOWS.md`:

### 1. Authentication
- `LoginPage`: Sleek card with email/username, password, remember session, and error toast.

### 2. Main Layout & Dashboard
- `AppLayout`: Collapsible sidebar with navigation groups, top header with live gold rate ticker and cash-in-hand drawer widget.
- `DashboardPage`:
  - Gold Rate Ticker (24K, 22K, 18K selling/buying rates with "Update Rate" modal).
  - KPI Cards (Today's Sales ₹, Today's Collected Cash ₹, Purchases ₹, Cash Drawer Balance ₹).
  - Showroom Inventory Donut Chart by category.
  - Recent Sales Invoices table with quick-view button.

### 3. Point of Sale (POS) & GST Invoicing (`/pos`) — *CRITICAL WORKFLOW*
- Customer Selector / Quick Create Modal (Party type = `customer`).
- Barcode / Tag Scan Input: Types or scans tag code $\to$ triggers `GET /api/jewellery-items/tag/{tagCode}` $\to$ auto-fills gross weight, stone weight, net gold weight, HUID, and today's gold rate.
- Manual Item Entry fallback for custom ornaments.
- Real-time Calculations:
  - $\text{Net Gold Wt} = \text{Gross Wt} - \text{Stone Wt}$
  - $\text{Gold Amount} = \text{Net Wt} \times \text{Board Rate}$
  - $\text{Making Charges} = \text{per\_gram} \times \text{Gross Wt}$ (or fixed/%)
  - $\text{Taxable} = \text{Gold Amount} + \text{Making Charges} + \text{Stone Value} + \text{Hallmarking} - \text{Discount}$
  - $\text{GST (3%)} = \text{Intra-state: 1.5% CGST + 1.5% SGST} \text{ or } \text{Inter-state: 3% IGST}$
- **Old Gold Exchange (Trade-In / Buyback):** Accordion to enter old scrap gold brought by customer $\to$ computes purity melt value $\to$ deducts directly from total bill.
- **Indian PAN Regulation Validation:** If payment mode is `cash` and paid amount $\ge ₹2,00,000$, require `customer_pan` (`[A-Z]{5}[0-9]{4}[A-Z]{1}`).
- **Printable Invoices:**
  - Full A4/A5 GST Tax Invoice with hallmark logos, HUID, terms, and customer sign.
  - 80mm Thermal Receipt.
- `InvoiceListPage` and `InvoiceDetailPage` with "Cancel Invoice" button (`POST /api/sales/{id}/cancel`).

### 4. Showroom Inventory & Barcode Tags (`/inventory`)
- Filter tabs: `In Stock`, `Sold`, `Returned`, `All`.
- Filters: Category, Purity (22K, 18K), Search by Tag / HUID / Name.
- Table: Tag code, Item name, Purity badge, HUID, Gross Wt, Stone Wt, Net Gold Wt, Cost, Status badge.
- Print individual Barcode Tag Sticker.
- "Add New Jewellery Item" modal.

### 5. Daily Gold Board Rates (`/masters/rates`)
- Display today's board rates.
- "Set Today's Rates" modal for 24K, 22K, 18K, 14K selling and buying rates.
- Historical rates trend line chart.

### 6. Karigar Production Inward (`/stock-entries`)
- Form to inward batch of finished jewellery from goldsmith/karigar.
- Automatically generates showroom inventory items with barcode tags.

### 7. Procurement & Purchases (`/purchases`)
- Record bullion raw gold or wholesale finished ornament purchase from vendors.
- Tax breakdown & Input Tax Credit (ITC) tracking.
- Cancel Purchase modal (`POST /api/purchases/{id}/cancel`) with inventory rollback.

### 8. Sales Returns & Credit Notes (`/sales-returns`)
- Issue GST Credit Note against past invoice.
- Select returned items $\to$ choose restock action (`Restock in Showroom`, `Melt into Scrap`).
- Refund via Cash Drawer or Customer Ledger balance.

### 9. Parties CRM (`/parties`)
- Tabs: Customers, Vendors, Karigars.
- Track Financial Balance (₹ Receivable / Payable) and Metal Balance (Grams of Gold).
- Party Detail page with transaction history tabs and Account Statement.

### 10. Cash Drawer & Financial Accounts (`/accounts`)
- Real-time Cash Drawer Counter balance (Opening, Inflow, Outflow, Closing).
- Bank Accounts list.
- New Payment Voucher modal (`Receipt` or `Payment`).
- Account Statement with date filter.

### 11. Showroom Expenses & Incomes (`/expenses`)
- Record daily expenses (tea, electricity, maintenance) with GST Input Tax Credit claim.

### 12. Statutory GST Reports (`/gst-reports`)
- GSTR-1: Table 4 (B2B), Table 5 (B2CL), Table 7 (B2CS), Table 9B (Credit Notes), Table 12 (HSN Summary).
- GSTR-3B: Output Tax Liability vs Eligible Input Tax Credit (ITC) with Net Cash Payable calculation.

### 13. Settings & Administration (`/settings`)
- Business Profile: Store name, GSTIN, invoice prefixes, address.
- Employee Roster & User Accounts.
- Database Backups: Trigger one-click backup (`POST /api/backups`) and download `.sql` files.

---

## 4. EXECUTION PLAN

Execute the development in sequential phases:

* **Phase 1: Project Setup & Design System**
  - Initialize Vite React TypeScript project.
  - Setup Tailwind CSS with gold/navy color tokens.
  - Setup Lucide icons, Sonner toast, and font styling.
* **Phase 2: Core Data Types & API Client**
  - Copy `doc/DATA_MODELS_AND_TYPES.ts` into `src/types/index.ts`.
  - Copy `doc/API_CLIENT_EXAMPLE.ts` into `src/api/client.ts`.
  - Setup Zustand `authStore` and `posStore`.
* **Phase 3: Shell Layout & Authentication**
  - Implement `LoginPage`, `AppLayout`, `Sidebar`, `Header`, and `ProtectedRoute`.
* **Phase 4: POS Retail Terminal & Dashboard**
  - Build `DashboardPage` with KPI cards and live rate ticker.
  - Build `PosBillingPage` with tag scanning, old gold exchange, and printable invoice.
* **Phase 5: Inventory, Masters & Procurement**
  - Build `InventoryListPage`, `DailyRatesPage`, `StockEntryPage`, `PurchaseListPage`, and `SalesReturnListPage`.
* **Phase 6: CRM, Accounts, GST Reports & Settings**
  - Build `PartiesPage`, `CashDrawerPage`, `ExpenseIncomePage`, `GstReportsPage`, and `SettingsPage`.

Make the user interface stunning, fast, and 100% bug-free!
```
