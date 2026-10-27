# 💎 Bharat Jewellery ERP & POS — Frontend Agent Master Architecture Guide

> **Target Audience:** Frontend AI Agent & React Developers  
> **Backend Framework:** Laravel 12.x / PHP 8.4 • **Database:** MySQL 8.x  
> **API Paradigm:** RESTful JSON with Laravel Sanctum (Bearer Token)  
> **Base URL (Localhost):** `http://localhost/bharat_api/public/api` (or `http://127.0.0.1:8000/api` if using `php artisan serve`)  
> **Last Verified:** October 2026

---

## 1. Domain & Business Overview

**Bharat Jewellery ERP** is a specialized, production-ready Enterprise Resource Planning (ERP) and Point of Sale (POS) retail software engineered specifically for Indian gold and jewellery retailers, wholesalers, and bullion merchants.

### Core Business Domains Managed:
1. **Purities & Karats:** 24K (999 bullion), 22K (916 hallmark), 18K (750 hallmark), 14K (585 hallmark).
2. **Daily Gold Board Rates:** Daily opening selling and buying rates per gram tracked historically.
3. **Inventory & HUID Barcoding:** Individual ornaments tagged with unique `tag_code`, `huid` (Hallmark Unique Identification 6-digit alphanumeric code), gross weight, stone weight, net gold weight, fine gold weight, and live status (`in_stock`, `sold`, `returned`, `reserved`).
4. **Artisan / Karigar Production Inward (Stock Entries):** Inward batches from karigars with making charges, stone costs, and automatic inventory stock generation.
5. **Procurement / Purchases:** Bullion raw gold, wholesale ornaments, and customer scrap buybacks with GST and vendor ledger accounting.
6. **Point of Sale (POS) & GST Invoicing:**
   - Barcode scanning / tag lookup.
   - Formula: $\text{Taxable} = (\text{Net Weight} \times \text{Today Rate}) + \text{Making Charges} + \text{Stone Value} + \text{Hallmarking} - \text{Discount}$.
   - GST: 3% on gold jewellery (Intra-state: 1.5% CGST + 1.5% SGST; Inter-state: 3.0% IGST).
   - Old Gold Exchange / Buyback: Trade-in deduction on the spot.
   - Indian Tax Rule: Customer PAN is legally mandatory for cash payments of ₹2,00,000 or more.
7. **Sales Returns / Credit Notes:** Credit notes linked to original sales invoices, restocking returned items, and adjusting party/cash accounts.
8. **Double-Entry Financial Accounting & Cash Drawer:** Real-time cash drawer tracking (cash-in-hand, bank accounts, receipts, payments, ledger statements).
9. **Statutory GST Reports:** GSTR-1 (Table 4 B2B, Table 5 B2CL, Table 7 B2CS, Table 9B Credit Notes, Table 12 HSN) and GSTR-3B (Output tax liability vs. Eligible Input Tax Credit).
10. **Role-Based Access Control:** `Admin`, `Developer`, `Owner`, `Staff`.

---

## 2. Recommended Frontend Tech Stack (React Latest)

To build a world-class, premium UI for this ERP, the frontend agent should use:

* **Core:** React 19 (or latest React 18+), Vite 6+ / Next.js
* **Language:** TypeScript 5.x (Strict mode enabled)
* **Styling:** Tailwind CSS v3/v4 + Curated Premium Jewellery Design Tokens
  * *Primary Palette:* Deep Slate/Navy (`#0f172a`, `#1e293b`), Champagne Gold (`#d97706`, `#eab308`, `#fef3c7`), Pearl White (`#f8fafc`), Rose Gold (`#fb7185`).
* **Icons:** `lucide-react`
* **Server State & Caching:** `@tanstack/react-query` (v5)
* **Client / UI State:** `zustand` (for Auth Session, Cart / POS Active Ticket, Theme)
* **HTTP Client:** `axios` with interceptors for Sanctum tokens and unified error handling
* **Forms & Validation:** `react-hook-form` + `zod`
* **Tables & Sorting:** `@tanstack/react-table`
* **Charts & Analytics:** `recharts`
* **Print & Invoice Export:** `react-to-print` (for thermal receipts & A4/A5 GST Tax Invoices)
* **Feedback / Notifications:** `sonner` or `react-hot-toast`

---

## 3. Recommended Frontend Project Directory Structure

```
frontend/
├── public/
│   ├── favicon.ico
│   └── logo.svg
├── src/
│   ├── api/
│   │   ├── client.ts             # Axios instance, baseURL, interceptors
│   │   ├── auth.ts               # Login, me, logout
│   │   ├── dashboard.ts          # /api/dashboard KPI stats
│   │   ├── jewellery.ts          # Items, tags, purities, categories, daily rates
│   │   ├── sales.ts              # POS, invoices, cancel, returns
│   │   ├── purchases.ts          # Procurement, bills, cancel
│   │   ├── parties.ts            # Customers, vendors, karigars
│   │   ├── accounts.ts           # Cash drawer, ledger, payments, expenses
│   │   └── gst.ts                # GSTR-1, GSTR-3B, status
│   ├── assets/
│   ├── components/
│   │   ├── common/               # Button, Input, Modal, Table, Badge, Card, Spinner
│   │   ├── layout/               # Header, Sidebar, AppLayout, ProtectedRoute
│   │   ├── pos/                  # ItemScanner, WeightCalculator, OldGoldExchange, PaymentModal
│   │   ├── print/                # InvoiceTemplateA4, InvoiceReceiptThermal, BarcodeTagLabel
│   │   └── dashboard/            # GoldRateTicker, KpiStatCard, CashDrawerWidget, CategoryChart
│   ├── context/ / store/
│   │   ├── authStore.ts          # Zustand store for user, token, role
│   │   └── posStore.ts           # Active billing ticket, item list, old gold trade-ins
│   ├── hooks/
│   │   ├── useAuth.ts
│   │   ├── useDailyRates.ts
│   │   ├── useJewelleryItem.ts
│   │   └── useDebounce.ts
│   ├── pages/
│   │   ├── auth/
│   │   │   └── LoginPage.tsx
│   │   ├── dashboard/
│   │   │   └── DashboardPage.tsx
│   │   ├── pos/
│   │   │   ├── PosBillingPage.tsx
│   │   │   ├── InvoiceListPage.tsx
│   │   │   └── InvoiceDetailPage.tsx
│   │   ├── inventory/
│   │   │   ├── InventoryListPage.tsx
│   │   │   ├── AddJewelleryItemPage.tsx
│   │   │   └── BarcodeScanPage.tsx
│   │   ├── masters/
│   │   │   ├── DailyRatesPage.tsx
│   │   │   ├── CategoriesPage.tsx
│   │   │   └── PuritiesPage.tsx
│   │   ├── stock-entry/
│   │   │   ├── StockEntryListPage.tsx
│   │   │   └── CreateStockEntryPage.tsx
│   │   ├── purchases/
│   │   │   ├── PurchaseListPage.tsx
│   │   │   └── CreatePurchasePage.tsx
│   │   ├── returns/
│   │   │   ├── SalesReturnListPage.tsx
│   │   │   └── CreateCreditNotePage.tsx
│   │   ├── parties/
│   │   │   ├── PartyListPage.tsx
│   │   │   └── PartyDetailPage.tsx
│   │   ├── accounts/
│   │   │   ├── CashDrawerPage.tsx
│   │   │   ├── AccountsListPage.tsx
│   │   │   ├── AccountStatementPage.tsx
│   │   │   └── ExpenseIncomePage.tsx
│   │   ├── gst/
│   │   │   ├── Gstr1ReportPage.tsx
│   │   │   └── Gstr3bReportPage.tsx
│   │   └── settings/
│   │       ├── BusinessProfilePage.tsx
│   │       ├── UsersManagementPage.tsx
│   │       └── BackupsPage.tsx
│   ├── types/
│   │   └── index.ts              # Full TypeScript definitions (see DATA_MODELS_AND_TYPES.ts)
│   ├── utils/
│   │   ├── formatters.ts         # Currency format (₹), gram weight (0.000g), dates
│   │   ├── calculations.ts       # Jewellery pricing math, GST breakdown
│   │   └── panValidator.ts       # 10-char PAN regex & ₹2L cash validation
│   ├── App.tsx                   # Routes and QueryClientProvider
│   ├── main.tsx
│   └── index.css
├── .env.example
├── package.json
├── tsconfig.json
└── vite.config.ts
```

---

## 4. Authentication & Security Flow

1. **Protocol:** Laravel Sanctum Token Authentication.
2. **Login:** Send `POST /api/login` with `{ "email": "...", "password": "..." }`.
3. **Response Payload:**
   ```json
   {
     "status": true,
     "message": "login successfully",
     "data": {
       "token": "1|abcdef123456789...",
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
       }
     }
   }
   ```
4. **Token Storage:** Store `token` in `localStorage` or `sessionStorage` (under key `bharat_token`).
5. **Request Header:** Pass `Authorization: Bearer <token>` on all requests. Also set `Accept: application/json`.
6. **Session Check:** Call `GET /api/me` on app boot to restore user profile.
7. **401 Unauthorized Interceptor:** If any API request returns HTTP 401, clear stored token and redirect to `/login`.
8. **Role-Based Guards:**
   - Tier 1 (Admin / Owner / Developer): Access to `/settings/users`, `/settings/employees`, `/settings/backups`.
   - General (Staff): Access to POS, Inventory, Daily Rates, Purchases, Returns, Parties, Reports.

---

## 5. API Response Protocol

Every single response from `bharat_api` adheres strictly to this structure:

### Success Response (`status: true`, HTTP 200/201)
```json
{
  "status": true,
  "message": "Action completed successfully",
  "data": { ... } | [ ... ]
}
```

### Validation Error Response (`status: false`, HTTP 422)
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "gross_weight": ["The gross weight field is required."],
    "customer_pan": ["Customer PAN is legally mandatory for cash payments of ₹2,00,000 or more."]
  }
}
```

### Error / Conflict Response (`status: false`, HTTP 400, 401, 403, 404, 409)
```json
{
  "status": false,
  "message": "Cannot delete jewellery item that has already been sold."
}
```

---

## 6. Documentation Files in this `doc/` Folder

| File | Purpose |
| :--- | :--- |
| [`doc/README.md`](file:///e:/wamp64/www/bharat_api/doc/README.md) | This master architecture guide and project overview. |
| [`doc/API_REFERENCE.md`](file:///e:/wamp64/www/bharat_api/doc/API_REFERENCE.md) | Exhaustive documentation of all 77+ endpoints, parameters, schemas, and examples. |
| [`doc/FRONTEND_PAGES_AND_WORKFLOWS.md`](file:///e:/wamp64/www/bharat_api/doc/FRONTEND_PAGES_AND_WORKFLOWS.md) | Screen-by-screen specifications, UI components, states, calculations, and modal logic. |
| [`doc/DATA_MODELS_AND_TYPES.ts`](file:///e:/wamp64/www/bharat_api/doc/DATA_MODELS_AND_TYPES.ts) | Complete, ready-to-use TypeScript interfaces and types for the frontend. |
| [`doc/API_CLIENT_EXAMPLE.ts`](file:///e:/wamp64/www/bharat_api/doc/API_CLIENT_EXAMPLE.ts) | Copy-pasteable Axios client, interceptors, and React Query custom hooks. |
| [`doc/FRONTEND_AGENT_PROMPT.md`](file:///e:/wamp64/www/bharat_api/doc/FRONTEND_AGENT_PROMPT.md) | Turnkey prompt you can feed directly to an AI agent to build the whole React app! |

---

## 7. Environment Setup for Frontend

Create `.env` in the root of your React application:

```ini
# Vite React Environment Configuration
VITE_API_BASE_URL="http://localhost/bharat_api/public/api"
VITE_APP_NAME="Bharat Jewellery ERP & POS"
VITE_DEFAULT_CURRENCY="INR"
VITE_CURRENCY_SYMBOL="₹"
```
*(If running Laravel via `php artisan serve`, change URL to `http://127.0.0.1:8000/api`)*.
