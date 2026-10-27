/**
 * Bharat Jewellery ERP & POS — TypeScript Type Definitions
 * 
 * Copy-paste this file into: src/types/index.ts (or src/types/api.ts)
 */

// ============================================================================
// 1. GENERIC API ENVELOPES & PAGINATION
// ============================================================================

export interface ApiResponse<T> {
  status: boolean;
  message: string;
  data: T;
  errors?: Record<string, string[]>;
}

export interface PaginatedMeta {
  current_page: number;
  first_page_url: string;
  from: number | null;
  last_page: number;
  last_page_url: string;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  links?: any;
  meta?: PaginatedMeta;
}

// ============================================================================
// 2. AUTHENTICATION & USER MANAGEMENT
// ============================================================================

export type UserRole = 'Admin' | 'Developer' | 'Owner' | 'Staff' | 'Sales' | 'Accountant';

export interface UserProfile {
  id: number;
  userName: string;
  name: string;
  email: string;
  role: UserRole;
  mobile: string | null;
  department: string;
  designation: string;
  employeeId: number | null;
}

export interface LoginPayload {
  email: string; // or username
  password: string;
}

export interface LoginResponseData {
  user: UserProfile;
  token: string;
}

export interface RegisterUserPayload {
  email: string;
  password: string;
  password_confirmation?: string;
  user_type_id: number;
  employee_id: number;
}

// ============================================================================
// 3. DASHBOARD & KPI ANALYTICS
// ============================================================================

export interface GoldRateTickerItem {
  karatCode: string;
  karatName: string;
  metalType: 'gold' | 'silver' | 'platinum';
  sellingRatePerGram: number;
  buyingRatePerGram: number;
  rateDate: string;
}

export interface DashboardTodayStats {
  salesCount: number;
  salesAmount: number;
  salesNetWeightGrams: number;
  salesCollectedAmount: number;
  purchasesCount: number;
  purchasesAmount: number;
  purchasesWeightGrams: number;
  expensesCount: number;
  expensesAmount: number;
}

export interface DashboardCashDrawer {
  cashInHand: number;
  bankBalance: number;
  totalLiquidFunds: number;
}

export interface CategoryStockBreakdown {
  category_name: string;
  item_count: number;
  total_gross_weight: string | number;
}

export interface DashboardInventorySummary {
  inStockItemsCount: number;
  inStockGrossWeight: number;
  inStockNetWeight: number;
  totalSoldItems: number;
  categoryBreakdown: CategoryStockBreakdown[];
}

export interface RecentSaleItem {
  id: number;
  invoice_number: string;
  invoice_date: string;
  customer_name: string;
  grand_total: string | number;
  payment_status: string;
  total_net_weight: string | number;
}

export interface RecentStockEntryItem {
  id: number;
  entry_number: string;
  entry_date: string;
  entry_type: string;
  total_items_count: number;
  total_gross_weight: string | number;
}

export interface DashboardStatsData {
  asOfDate: string;
  goldRates: GoldRateTickerItem[];
  todayStats: DashboardTodayStats;
  cashDrawer: DashboardCashDrawer;
  inventorySummary: DashboardInventorySummary;
  recentSales: RecentSaleItem[];
  recentStockEntries: RecentStockEntryItem[];
  directoryCounts: {
    totalCustomers: number;
    totalVendors: number;
    totalKarigars: number;
  };
}

// ============================================================================
// 4. JEWELLERY MASTERS (PURITIES, CATEGORIES, DAILY RATES)
// ============================================================================

export interface Purity {
  id: number;
  karatName: string;
  karatCode: string;
  purityPercentage: number;
  hallmarkCode: string | null;
  description: string | null;
  isActive: boolean;
}

export interface JewelleryCategory {
  id: number;
  categoryName: string;
  categoryCode: string;
  defaultHsnCode: string;
  description: string | null;
  isActive: boolean;
}

export interface DailyGoldRate {
  id: number;
  rateDate: string;
  metalType: string;
  purityId: number;
  purity?: Purity;
  sellingRatePerGram: number;
  buyingRatePerGram: number;
  notes: string | null;
}

export interface StoreDailyGoldRatePayload {
  rate_date: string;
  metal_type?: 'gold' | 'silver';
  purity_id: number;
  selling_rate_per_gram?: number;
  buying_rate_per_gram?: number;
  rate_per_gram?: number;       // alias accepted by backend
  buyback_rate_per_gram?: number; // alias accepted by backend
  notes?: string;
}

// ============================================================================
// 5. PARTIES (CUSTOMERS, VENDORS, KARIGARS)
// ============================================================================

export type PartyType = 'customer' | 'vendor' | 'karigar' | 'both';

export interface Party {
  id: number;
  partyName: string;
  partyType: PartyType;
  phone: string | null;
  email: string | null;
  address: string | null;
  city: string | null;
  stateId: number | null;
  stateCode: number | null;
  pincode: string | null;
  gstin: string | null;
  pan: string | null;
  openingBalance: number;
  currentBalance: number;
  metalOpeningBalanceGrams: number;
  metalCurrentBalanceGrams: number;
  creditLimit: number;
  isActive: boolean;
  notes: string | null;
  createdAt: string;
}

export interface StorePartyPayload {
  name?: string;
  party_name?: string;
  party_type: PartyType;
  mobile?: string;
  phone?: string;
  email?: string;
  address?: string;
  city?: string;
  state_id?: number;
  state_code?: number;
  pincode?: string;
  gstin?: string;
  pan?: string;
  opening_balance?: number;
  metal_opening_balance_grams?: number;
  credit_limit?: number;
  notes?: string;
}

// ============================================================================
// 6. JEWELLERY INVENTORY ITEMS & TAGS
// ============================================================================

export type JewelleryItemStatus = 'in_stock' | 'sold' | 'returned' | 'reserved';

export interface JewelleryItem {
  id: number;
  tagCode: string;
  itemName: string;
  categoryId: number;
  category?: JewelleryCategory;
  purityId: number;
  purity?: Purity;
  hsnCode: string;
  grossWeight: number;
  stoneWeight: number;
  netGoldWeight: number;
  fineGoldWeight: number;
  stoneValue: number;
  purchaseCost: number;
  huid: string | null;
  sourceType: 'production' | 'purchase' | 'customer_trade_in' | 'manual';
  status: JewelleryItemStatus;
  soldDate: string | null;
  notes: string | null;
  createdAt: string;
}

export interface StoreJewelleryItemPayload {
  tag_code?: string;
  item_name: string;
  category_id: number;
  purity_id: number;
  hsn_code?: string;
  gross_weight: number;
  stone_weight?: number;
  stone_value?: number;
  purchase_cost?: number;
  huid?: string;
  source_type?: string;
  notes?: string;
}

// ============================================================================
// 7. STOCK ENTRY (PRODUCTION INWARD FROM KARIGAR)
// ============================================================================

export interface StockEntryItemPayload {
  item_name: string;
  category_id: number;
  purity_id: number;
  tag_code?: string;
  huid?: string;
  gross_weight: number;
  stone_weight?: number;
  estimated_rate_per_gram?: number;
  making_cost?: number;
  stone_cost?: number;
}

export interface StoreStockEntryPayload {
  entry_date: string;
  entry_type?: 'production_inward' | 'opening_stock' | 'consignment_inward';
  artisan_id?: number; // Karigar party ID
  remarks?: string;
  items: StockEntryItemPayload[];
}

export interface StockEntry {
  id: number;
  entryNumber: string;
  entryDate: string;
  entryType: string;
  artisanId: number | null;
  artisan?: Party;
  totalItemsCount: number;
  totalGrossWeight: number;
  totalStoneWeight: number;
  totalNetWeight: number;
  totalFineGoldWeight: number;
  totalEstimatedValuation: number;
  remarks: string | null;
  items?: any[];
  createdAt: string;
}

// ============================================================================
// 8. SALES & POINT OF SALE (POS)
// ============================================================================

export type PaymentMode = 'cash' | 'bank_transfer' | 'upi' | 'card' | 'cheque';
export type InvoiceType = 'tax_invoice' | 'bill_of_supply' | 'cash_memo';
export type TaxType = 'intra_state' | 'inter_state' | 'non_gst';

export interface SaleItemPayload {
  jewellery_item_id?: number; // Tag item ID from inventory
  category_id?: number;
  purity_id: number;
  item_name: string;
  tag_code?: string;
  huid?: string;
  hsn_code?: string;
  gross_weight: number;
  stone_weight?: number;
  gold_rate_per_gram: number; // Board rate applied
  rate_per_gram?: number;     // alias accepted
  making_charge_type?: 'per_gram' | 'percentage' | 'fixed';
  making_charge_rate?: number;
  making_charges?: number;
  stone_charges?: number;
  hallmarking_charge?: number;
  discount_amount?: number;
  gst_rate?: number;          // Default 3.00
}

export interface SaleOldGoldExchangePayload {
  item_description: string;
  gross_weight: number;
  dust_stone_weight?: number;
  dust_weight?: number;       // alias accepted
  purity_percentage?: number; // or purity_id
  purity_id?: number;
  buying_rate_per_gram: number;
  rate_per_gram?: number;     // alias accepted
  remarks?: string;
}

export interface StoreSalePayload {
  invoice_number?: string;
  invoice_date: string;
  party_id?: number | null;
  customer_name: string;
  customer_mobile?: string;
  customer_email?: string;
  customer_address?: string;
  customer_gstin?: string;
  customer_pan?: string;       // REQUIRED by Indian Tax Law if cash payment >= 2,00,000
  place_of_supply_state_id?: number;
  place_of_supply_state_code?: number;
  invoice_type?: InvoiceType;
  is_gst_applicable?: boolean;
  tax_type?: TaxType;
  discount_amount?: number;
  paid_amount?: number;
  payment_account_id?: number; // Cash Drawer or Bank Account ID
  deposit_account_id?: number; // alias accepted
  payment_mode?: PaymentMode;
  transaction_reference?: string;
  notes?: string;
  items: SaleItemPayload[];
  old_gold_exchanges?: SaleOldGoldExchangePayload[];
}

export interface SaleItemResource {
  id: number;
  jewelleryItemId: number | null;
  categoryId: number | null;
  purityId: number;
  itemName: string;
  tagCode: string | null;
  huid: string | null;
  hsnCode: string;
  grossWeight: number;
  stoneWeight: number;
  netWeight: number;
  fineGoldWeight: number;
  goldRatePerGram: number;
  goldAmount: number;
  makingChargeType: string;
  makingChargeRate: number;
  makingCharges: number;
  stoneCharges: number;
  hallmarkingCharge: number;
  discountAmount: number;
  taxableAmount: number;
  gstRate: number;
  cgstAmount: number;
  sgstAmount: number;
  igstAmount: number;
  totalAmount: number;
}

export interface SaleOldGoldExchangeResource {
  id: number;
  itemDescription: string;
  grossWeight: number;
  dustStoneWeight: number;
  netWeight: number;
  purityPercentage: number;
  fineGoldWeight: number;
  buyingRatePerGram: number;
  exchangeValue: number;
  remarks: string | null;
}

export interface Sale {
  id: number;
  invoiceNumber: string;
  invoiceDate: string;
  partyId: number | null;
  party?: Party;
  customerName: string;
  customerMobile: string | null;
  customerEmail: string | null;
  customerAddress: string | null;
  customerGstin: string | null;
  customerPan: string | null;
  placeOfSupplyStateId: number | null;
  placeOfSupplyStateCode: number;
  invoiceType: InvoiceType;
  isGstApplicable: boolean;
  taxType: TaxType;
  totalGrossWeight: number;
  totalStoneWeight: number;
  totalNetWeight: number;
  totalFineGoldWeight: number;
  totalGoldAmount: number;
  totalMakingCharges: number;
  totalStoneCharges: number;
  hallmarkingCharges: number;
  otherCharges: number;
  discountAmount: number;
  taxableAmount: number;
  cgstRate: number;
  cgstAmount: number;
  sgstRate: number;
  sgstAmount: number;
  igstRate: number;
  igstAmount: number;
  totalTaxAmount: number;
  oldGoldExchangeAmount: number;
  roundOff: number;
  grandTotal: number;
  paidAmount: number;
  balanceAmount: number;
  paymentStatus: 'paid' | 'partially_paid' | 'unpaid';
  isCancelled: boolean;
  cancellationReason: string | null;
  notes: string | null;
  items?: SaleItemResource[];
  oldGoldExchanges?: SaleOldGoldExchangeResource[];
  createdAt: string;
}

// ============================================================================
// 9. PURCHASES & PROCUREMENT
// ============================================================================

export interface PurchaseItemPayload {
  purity_id: number;
  category_id?: number;
  item_description: string;
  hsn_code?: string;
  gross_weight: number;
  stone_weight?: number;
  rate_per_gram: number;
  making_charges?: number;
  stone_charges?: number;
  gst_rate?: number;
}

export interface StorePurchasePayload {
  purchase_invoice_no?: string;
  purchase_date: string;
  vendor_bill_no?: string;
  vendor_bill_date?: string;
  party_id: number; // Vendor ID
  purchase_type?: 'vendor_jewellery' | 'bullion_raw_gold' | 'customer_old_gold_buyback';
  is_gst_applicable?: boolean;
  tax_type?: TaxType;
  other_charges?: number;
  discount_amount?: number;
  paid_amount?: number;
  payment_account_id?: number;
  payment_mode?: PaymentMode;
  notes?: string;
  items: PurchaseItemPayload[];
}

export interface PurchaseItemResource {
  id: number;
  purityId: number;
  categoryId: number | null;
  itemDescription: string;
  grossWeight: number;
  stoneWeight: number;
  netWeight: number;
  purityPercentage: number;
  fineGoldWeight: number;
  ratePerGram: number;
  goldAmount: number;
  makingCharges: number;
  stoneCharges: number;
  taxableAmount: number;
  gstRate: number;
  cgstAmount: number;
  sgstAmount: number;
  igstAmount: number;
  totalAmount: number;
}

export interface Purchase {
  id: number;
  purchaseInvoiceNo: string;
  purchaseDate: string;
  vendorBillNo: string | null;
  vendorBillDate: string | null;
  partyId: number;
  party?: Party;
  purchaseType: string;
  isGstApplicable: boolean;
  taxType: TaxType;
  totalGrossWeight: number;
  totalNetWeight: number;
  totalFineGoldWeight: number;
  totalGoldAmount: number;
  totalMakingCharges: number;
  totalStoneCharges: number;
  otherCharges: number;
  discountAmount: number;
  taxableAmount: number;
  cgstRate: number;
  cgstAmount: number;
  sgstRate: number;
  sgstAmount: number;
  igstRate: number;
  igstAmount: number;
  totalTaxAmount: number;
  roundOff: number;
  grandTotal: number;
  paidAmount: number;
  balanceAmount: number;
  paymentStatus: 'paid' | 'partially_paid' | 'unpaid';
  isCancelled: boolean;
  cancellationReason: string | null;
  itcEligibility: string;
  notes: string | null;
  items?: PurchaseItemResource[];
  createdAt: string;
}

// ============================================================================
// 10. SALES RETURNS & CREDIT NOTES
// ============================================================================

export interface SalesReturnItemPayload {
  sale_item_id?: number;
  jewellery_item_id?: number;
  category_id?: number;
  purity_id: number;
  item_name: string;
  tag_code?: string;
  gross_weight: number;
  stone_weight?: number;
  rate_per_gram: number;
  making_charges_refunded?: number;
  stone_charges_refunded?: number;
  gst_rate?: number;
  stock_action?: 'restocked_as_original' | 'sent_to_melting' | 'repaired_restocked' | 'damaged_scrap';
}

export interface StoreSalesReturnPayload {
  sale_id: number;
  credit_note_no?: string;
  credit_note_date: string;
  party_id?: number;
  refund_mode?: 'credit_to_ledger' | 'cash_refund' | 'bank_refund';
  refund_account_id?: number;
  reason?: string;
  notes?: string;
  items: SalesReturnItemPayload[];
}

export interface SalesReturn {
  id: number;
  creditNoteNo: string;
  creditNoteDate: string;
  saleId: number;
  sale?: Sale;
  partyId: number | null;
  party?: Party;
  customerName: string;
  totalGrossWeight: number;
  totalNetWeight: number;
  totalCreditAmount: number;
  refundMode: string;
  reason: string | null;
  items?: any[];
  createdAt: string;
}

// ============================================================================
// 11. FINANCIAL ACCOUNTS & CASH DRAWER
// ============================================================================

export type AccountType = 'cash' | 'bank' | 'direct_expense' | 'indirect_expense' | 'direct_income' | 'indirect_income' | 'liability' | 'asset';

export interface AccountGroup {
  id: number;
  groupName: string;
  primaryType: 'assets' | 'liabilities' | 'income' | 'expenses';
}

export interface Account {
  id: number;
  accountCode: string;
  accountName: string;
  accountGroupId: number;
  accountGroup?: AccountGroup;
  accountType: AccountType;
  openingBalance: number;
  currentBalance: number;
  bankAccountNumber: string | null;
  bankIfsc: string | null;
  isActive: boolean;
}

export interface CashDrawerSummaryData {
  asOfDate: string;
  totalCashBalance: number;
  totalBankBalance: number;
  totalLiquidFunds: number;
  todayCashInflow: number;
  todayCashOutflow: number;
  netCashMovement: number;
  cashAccounts: Account[];
  bankAccounts: Account[];
}

export interface StatementEntry {
  id: number;
  financial_voucher_id: number;
  voucher_date: string;
  voucher_number: string;
  entry_type: 'debit' | 'credit';
  amount: number;
  narration: string;
}

export interface AccountStatementData {
  account: Account;
  fromDate: string;
  toDate: string;
  currentBalance: number;
  periodDebit: number;
  periodCredit: number;
  entries: PaginatedResponse<StatementEntry>;
}

// ============================================================================
// 12. PAYMENTS & VOUCHERS
// ============================================================================

export interface StorePaymentPayload {
  payment_number?: string;
  payment_date: string;
  payment_type: 'receipt' | 'payment';
  account_id: number;
  party_id?: number | null;
  reference_type?: 'sale' | 'purchase' | 'advance' | 'other';
  reference_id?: number;
  payment_mode: PaymentMode;
  transaction_reference?: string;
  amount: number;
  notes?: string;
}

export interface Payment {
  id: number;
  paymentNumber: string;
  paymentDate: string;
  paymentType: 'receipt' | 'payment';
  accountId: number;
  account?: Account;
  partyId: number | null;
  party?: Party;
  referenceType: string;
  referenceId: number | null;
  paymentMode: PaymentMode;
  transactionReference: string | null;
  amount: number;
  notes: string | null;
  createdAt: string;
}

// ============================================================================
// 13. OPERATING EXPENSES & INCOMES
// ============================================================================

export interface StoreExpenseIncomePayload {
  entry_number?: string;
  entry_date: string;
  entry_type: 'expense' | 'income';
  account_id: number;         // Expense head account e.g. Tea/Electricity
  payment_account_id: number; // Cash or Bank Drawer account
  amount: number;
  payment_mode: PaymentMode;
  reference_no?: string;
  description?: string;
  is_gst_claimed?: boolean;
  tax_amount?: number;
  vendor_gstin?: string;
}

export interface ExpenseIncome {
  id: number;
  entryNumber: string;
  entryDate: string;
  entryType: 'expense' | 'income';
  accountId: number;
  account?: Account;
  paymentAccountId: number;
  paymentAccount?: Account;
  amount: number;
  paymentMode: PaymentMode;
  referenceNo: string | null;
  description: string | null;
  isGstClaimed: boolean;
  taxAmount: number;
  vendorGstin: string | null;
  createdAt: string;
}

// ============================================================================
// 14. STATUTORY GST REPORTS
// ============================================================================

export interface GstStatusData {
  isGstRegistered: boolean;
  gstin: string | null;
  businessName: string | null;
  stateCode: number | null;
  stateName: string | null;
  hsnCode: string;
  goldGstRate: number;
  silverGstRate: number;
  makingGstRate: number;
}

export interface Gstr1SummaryData {
  period: {
    fromDate: string;
    toDate: string;
  };
  totalTaxableSupplies: number;
  totalOutputTax: number;
  table4_b2b: {
    count: number;
    taxableAmount: number;
    cgstAmount: number;
    sgstAmount: number;
    igstAmount: number;
    totalAmount: number;
    invoices: Array<{
      invoiceNo: string;
      invoiceDate: string;
      customerName: string;
      customerGstin: string;
      posStateCode: number;
      taxableAmount: number;
      cgstAmount: number;
      sgstAmount: number;
      igstAmount: number;
      totalAmount: number;
    }>;
  };
  table5_b2cl: {
    count: number;
    taxableAmount: number;
    igstAmount: number;
    invoices: any[];
  };
  table7_b2cs: {
    taxableAmount: number;
    cgstAmount: number;
    sgstAmount: number;
    igstAmount: number;
    totalTax: number;
  };
  table9b_cdnr: {
    count: number;
    taxableAmount: number;
    cgstAmount: number;
    sgstAmount: number;
    igstAmount: number;
    notes: any[];
  };
  table12_hsn: Array<{
    hsnCode: string;
    description: string;
    uqc: string;
    totalItems: number;
    totalGrossWeight: number;
    totalNetWeight: number;
    taxableAmount: number;
    cgstAmount: number;
    sgstAmount: number;
    igstAmount: number;
    totalValue: number;
  }>;
}

export interface Gstr3bSummaryData {
  period: {
    fromDate: string;
    toDate: string;
  };
  table3_1_outwardSupplies: {
    taxableAmount: number;
    cgstAmount: number;
    sgstAmount: number;
    igstAmount: number;
    totalOutputTax: number;
  };
  table4_eligibleItc: {
    taxableAmount: number;
    cgstAmount: number;
    sgstAmount: number;
    igstAmount: number;
    totalItcAvailable: number;
  };
  netPayableAfterItc: {
    netCgstPayable: number;
    netSgstPayable: number;
    netIgstPayable: number;
    totalCashPayable: number;
  };
}

// ============================================================================
// 15. BUSINESS PROFILE & SETTINGS
// ============================================================================

export interface BusinessProfile {
  id: number;
  businessName: string;
  tagline: string | null;
  gstin: string | null;
  pan: string | null;
  hallmarkingLicenseNo: string | null;
  isGstRegistered: boolean;
  addressLine1: string | null;
  addressLine2: string | null;
  city: string | null;
  districtId: number | null;
  stateId: number | null;
  stateCode: number;
  pincode: string | null;
  phone: string | null;
  email: string | null;
  invoicePrefix: string;
  purchasePrefix: string;
  stockEntryPrefix: string;
  goldGstRate: number;
  silverGstRate: number;
  makingGstRate: number;
  hsnCode: string;
  financialYearStart: string;
  termsAndConditions: string | null;
  logoUrl: string | null;
}

export interface UpdateBusinessProfilePayload {
  business_name?: string;
  tagline?: string;
  gstin?: string;
  pan?: string;
  hallmarking_license_no?: string;
  is_gst_registered?: boolean;
  address_line1?: string;
  address_line2?: string;
  city?: string;
  state_id?: number;
  state_code?: number;
  pincode?: string;
  phone?: string;
  email?: string;
  invoice_prefix?: string;
  purchase_prefix?: string;
  stock_entry_prefix?: string;
  gold_gst_rate?: number;
  silver_gst_rate?: number;
  making_gst_rate?: number;
  terms_and_conditions?: string;
}

// ============================================================================
// 16. ADMINISTRATION MASTERS (EMPLOYEES, DEPARTMENTS, STATES)
// ============================================================================

export interface Employee {
  id: number;
  employee_name: string;
  email: string;
  mobile: string;
  department_id: number;
  designation_id: number;
  department?: { id: number; department_name: string };
  designation?: { id: number; designation_name: string };
}

export interface Department {
  id: number;
  department_name: string;
}

export interface Designation {
  id: number;
  designation_name: string;
}

export interface State {
  id: number;
  state_name: string;
  state_code: number;
}

export interface District {
  id: number;
  district_name: string;
  state_id: number;
}

export interface BackupItem {
  filename: string;
  size: string;
  created_at: string;
}
