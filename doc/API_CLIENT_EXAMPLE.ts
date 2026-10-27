/**
 * Bharat Jewellery ERP & POS — Complete Axios API Client & React Query Hooks
 * 
 * Copy-paste this file into: src/api/client.ts (or modularize into src/api/)
 */

import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import type {
  ApiResponse,
  LoginPayload,
  LoginResponseData,
  UserProfile,
  DashboardStatsData,
  Purity,
  JewelleryCategory,
  DailyGoldRate,
  StoreDailyGoldRatePayload,
  JewelleryItem,
  StoreJewelleryItemPayload,
  Party,
  StorePartyPayload,
  Sale,
  StoreSalePayload,
  Purchase,
  StorePurchasePayload,
  SalesReturn,
  StoreSalesReturnPayload,
  Account,
  CashDrawerSummaryData,
  AccountStatementData,
  Payment,
  StorePaymentPayload,
  ExpenseIncome,
  StoreExpenseIncomePayload,
  GstStatusData,
  Gstr1SummaryData,
  Gstr3bSummaryData,
  BusinessProfile,
  UpdateBusinessProfilePayload,
  BackupItem,
} from '../types';

// ============================================================================
// 1. AXIOS CLIENT INSTANCE & INTERCEPTORS
// ============================================================================

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/bharat_api/public/api';

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000,
});

// Request Interceptor: Attach Sanctum Bearer Token
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('bharat_token');
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response Interceptor: Unified Error Handling & Auto-Logout on 401
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiResponse<any>>) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('bharat_token');
      localStorage.removeItem('bharat_user');
      // Redirect to login if not already there
      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

// Helper for extracting response payload data
export const unwrap = <T>(promise: Promise<{ data: ApiResponse<T> }>): Promise<T> =>
  promise.then((res) => res.data.data);

// ============================================================================
// 2. RAW API SERVICE FUNCTIONS
// ============================================================================

export const api = {
  // ── Auth ──────────────────────────────────────────────────────────────────
  auth: {
    login: (payload: LoginPayload) => unwrap<LoginResponseData>(apiClient.post('/login', payload)),
    me: () => unwrap<UserProfile>(apiClient.get('/me')),
    logout: () => unwrap<any>(apiClient.post('/logout')),
    revokeAll: () => unwrap<any>(apiClient.post('/revokeAll')),
  },

  // ── Dashboard ─────────────────────────────────────────────────────────────
  dashboard: {
    getStats: (date?: string) =>
      unwrap<DashboardStatsData>(apiClient.get('/dashboard', { params: { date } })),
  },

  // ── Jewellery Masters ─────────────────────────────────────────────────────
  masters: {
    getPurities: () => unwrap<Purity[]>(apiClient.get('/purities')),
    createPurity: (data: Partial<Purity>) => unwrap<Purity>(apiClient.post('/purities', data)),
    getCategories: () => unwrap<JewelleryCategory[]>(apiClient.get('/jewellery-categories')),
    createCategory: (data: Partial<JewelleryCategory>) =>
      unwrap<JewelleryCategory>(apiClient.post('/jewellery-categories', data)),
    getDailyRates: (params?: { date?: string; page?: number }) =>
      unwrap<any>(apiClient.get('/daily-gold-rates', { params })),
    getTodayRates: () => unwrap<DailyGoldRate[]>(apiClient.get('/daily-gold-rates/today')),
    storeDailyRate: (payload: StoreDailyGoldRatePayload) =>
      unwrap<DailyGoldRate>(apiClient.post('/daily-gold-rates', payload)),
  },

  // ── Inventory Items & Tags ────────────────────────────────────────────────
  inventory: {
    list: (params?: { status?: string; category_id?: number; purity_id?: number; search?: string; page?: number; per_page?: number }) =>
      unwrap<any>(apiClient.get('/jewellery-items', { params })),
    getById: (id: number) => unwrap<JewelleryItem>(apiClient.get(`/jewellery-items/${id}`)),
    getByTag: (tagCode: string) => unwrap<JewelleryItem>(apiClient.get(`/jewellery-items/tag/${encodeURIComponent(tagCode)}`)),
    create: (payload: StoreJewelleryItemPayload) => unwrap<JewelleryItem>(apiClient.post('/jewellery-items', payload)),
    update: (id: number, payload: Partial<StoreJewelleryItemPayload>) =>
      unwrap<JewelleryItem>(apiClient.put(`/jewellery-items/${id}`, payload)),
    delete: (id: number) => unwrap<any>(apiClient.delete(`/jewellery-items/${id}`)),
  },

  // ── Stock Entries (Karigar Inward) ────────────────────────────────────────
  stockEntries: {
    list: (params?: { type?: string; page?: number }) => unwrap<any>(apiClient.get('/stock-entries', { params })),
    getById: (id: number) => unwrap<any>(apiClient.get(`/stock-entries/${id}`)),
    create: (payload: any) => unwrap<any>(apiClient.post('/stock-entries', payload)),
  },

  // ── Sales & POS ───────────────────────────────────────────────────────────
  sales: {
    list: (params?: { party_id?: number; status?: string; from_date?: string; to_date?: string; page?: number }) =>
      unwrap<any>(apiClient.get('/sales', { params })),
    getById: (id: number) => unwrap<Sale>(apiClient.get(`/sales/${id}`)),
    create: (payload: StoreSalePayload) => unwrap<Sale>(apiClient.post('/sales', payload)),
    cancel: (id: number, reason: string) => unwrap<Sale>(apiClient.post(`/sales/${id}/cancel`, { reason })),
  },

  // ── Purchases & Procurement ───────────────────────────────────────────────
  purchases: {
    list: (params?: { party_id?: number; type?: string; page?: number }) =>
      unwrap<any>(apiClient.get('/purchases', { params })),
    getById: (id: number) => unwrap<Purchase>(apiClient.get(`/purchases/${id}`)),
    create: (payload: StorePurchasePayload) => unwrap<Purchase>(apiClient.post('/purchases', payload)),
    cancel: (id: number, reason: string) => unwrap<Purchase>(apiClient.post(`/purchases/${id}/cancel`, { reason })),
  },

  // ── Sales Returns (Credit Notes) ──────────────────────────────────────────
  salesReturns: {
    list: (params?: { party_id?: number; sale_id?: number; from_date?: string; to_date?: string; page?: number }) =>
      unwrap<any>(apiClient.get('/sales-returns', { params })),
    getById: (id: number) => unwrap<SalesReturn>(apiClient.get(`/sales-returns/${id}`)),
    create: (payload: StoreSalesReturnPayload) => unwrap<SalesReturn>(apiClient.post('/sales-returns', payload)),
  },

  // ── Parties (Customers, Vendors, Karigars) ────────────────────────────────
  parties: {
    list: (params?: { type?: string; search?: string; page?: number; per_page?: number }) =>
      unwrap<any>(apiClient.get('/parties', { params })),
    getById: (id: number) => unwrap<Party>(apiClient.get(`/parties/${id}`)),
    create: (payload: StorePartyPayload) => unwrap<Party>(apiClient.post('/parties', payload)),
    update: (id: number, payload: Partial<StorePartyPayload>) => unwrap<Party>(apiClient.put(`/parties/${id}`, payload)),
    delete: (id: number) => unwrap<any>(apiClient.delete(`/parties/${id}`)),
  },

  // ── Accounts & Cash Drawer ────────────────────────────────────────────────
  accounts: {
    list: (params?: { type?: string; is_cash?: number; is_bank?: number; search?: string }) =>
      unwrap<Account[]>(apiClient.get('/accounts', { params })),
    getById: (id: number) => unwrap<Account>(apiClient.get(`/accounts/${id}`)),
    cashDrawer: (date?: string) => unwrap<CashDrawerSummaryData>(apiClient.get('/accounts/drawer/cash-summary', { params: { date } })),
    statement: (id: number, params?: { from_date?: string; to_date?: string; page?: number }) =>
      unwrap<AccountStatementData>(apiClient.get(`/accounts/${id}/statement`, { params })),
  },

  // ── Payments & Receipts ───────────────────────────────────────────────────
  payments: {
    list: (params?: { type?: string; party_id?: number; account_id?: number; mode?: string; from_date?: string; to_date?: string; page?: number }) =>
      unwrap<any>(apiClient.get('/payments', { params })),
    getById: (id: number) => unwrap<Payment>(apiClient.get(`/payments/${id}`)),
    create: (payload: StorePaymentPayload) => unwrap<Payment>(apiClient.post('/payments', payload)),
  },

  // ── Expenses & Incomes ────────────────────────────────────────────────────
  expenses: {
    list: (params?: { type?: string; account_id?: number; from_date?: string; to_date?: string; page?: number }) =>
      unwrap<any>(apiClient.get('/expense-incomes', { params })),
    getById: (id: number) => unwrap<ExpenseIncome>(apiClient.get(`/expense-incomes/${id}`)),
    create: (payload: StoreExpenseIncomePayload) => unwrap<ExpenseIncome>(apiClient.post('/expense-incomes', payload)),
    delete: (id: number) => unwrap<any>(apiClient.delete(`/expense-incomes/${id}`)),
  },

  // ── GST Reports ───────────────────────────────────────────────────────────
  gst: {
    status: () => unwrap<GstStatusData>(apiClient.get('/gst/status')),
    gstr1Summary: (params: { from_date: string; to_date: string }) =>
      unwrap<Gstr1SummaryData>(apiClient.get('/gst/gstr1-summary', { params })),
    gstr3bSummary: (params: { from_date: string; to_date: string }) =>
      unwrap<Gstr3bSummaryData>(apiClient.get('/gst/gstr3b-summary', { params })),
  },

  // ── Business Profile & Backups ────────────────────────────────────────────
  profile: {
    get: () => unwrap<BusinessProfile>(apiClient.get('/business-profile')),
    update: (payload: UpdateBusinessProfilePayload) => unwrap<BusinessProfile>(apiClient.put('/business-profile', payload)),
  },
  backups: {
    list: () => unwrap<BackupItem[]>(apiClient.get('/backups')),
    create: () => unwrap<any>(apiClient.post('/backups')),
    downloadUrl: (filename: string) => `${API_BASE_URL}/backups/${filename}`,
    delete: (filename: string) => unwrap<any>(apiClient.delete(`/backups/${filename}`)),
  },
};

// ============================================================================
// 3. REACT QUERY CUSTOM HOOKS (Ready for Components)
// ============================================================================

export const useDashboardStats = (date?: string) =>
  useQuery({
    queryKey: ['dashboard', date],
    queryFn: () => api.dashboard.getStats(date),
    refetchInterval: 60000, // Poll every minute for live counter sales
  });

export const useTodayGoldRates = () =>
  useQuery({
    queryKey: ['daily-gold-rates', 'today'],
    queryFn: () => api.masters.getTodayRates(),
    staleTime: 5 * 60 * 1000,
  });

export const usePurities = () =>
  useQuery({
    queryKey: ['purities'],
    queryFn: () => api.masters.getPurities(),
    staleTime: 60 * 60 * 1000,
  });

export const useJewelleryCategories = () =>
  useQuery({
    queryKey: ['jewellery-categories'],
    queryFn: () => api.masters.getCategories(),
    staleTime: 60 * 60 * 1000,
  });

export const useJewelleryItemByTag = (tagCode: string, enabled = true) =>
  useQuery({
    queryKey: ['jewellery-item', 'tag', tagCode],
    queryFn: () => api.inventory.getByTag(tagCode),
    enabled: Boolean(tagCode) && enabled,
    retry: false,
  });

export const useCashDrawerSummary = (date?: string) =>
  useQuery({
    queryKey: ['accounts', 'cash-drawer', date],
    queryFn: () => api.accounts.cashDrawer(date),
  });

export const useAccounts = (type?: string) =>
  useQuery({
    queryKey: ['accounts', type],
    queryFn: () => api.accounts.list({ type }),
  });

export const useCreateSale = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: StoreSalePayload) => api.sales.create(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sales'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
      queryClient.invalidateQueries({ queryKey: ['jewellery-items'] });
      queryClient.invalidateQueries({ queryKey: ['accounts'] });
    },
  });
};

export const useCancelSale = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, reason }: { id: number; reason: string }) => api.sales.cancel(id, reason),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['sales'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
      queryClient.invalidateQueries({ queryKey: ['jewellery-items'] });
    },
  });
};
