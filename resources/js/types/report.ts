




export type ReportType =
  | 'loans'
  | 'returns'
  | 'violations'
  | 'settlements'
  | 'inventory'
  | 'conditions'
  | 'users'
  | 'activity_logs';

export interface ReportFilters {
  start_date?: string; 
  end_date?: string; 
  [key: string]: any;
}


export interface LoanReportItem {
  id: number;
  user: string;
  tool: string;
  status: string;
  loan_date: string;
  due_date: string;
}


export interface ReturnReportItem {
  id: number;
  user: string;
  tool: string;
  employee: string;
  return_date: string;
  reviewed: boolean;
}


export interface ViolationReportItem {
  id: number;
  user: string;
  tool: string;
  type: string;
  fine: number;
  status: string;
  created_at: string;
}


export interface SettlementReportItem {
  id: number;
  user: string;
  employee: string;
  description: string;
  settled_at: string;
}


export interface InventoryReportItem {
  tool: string;
  category: string;
  total_unit: number;
  available: number;
  lent: number;
  nonactive: number;
}


export interface UnitConditionReportItem {
  unit_code: string;
  tool: string;
  condition: string;
  recorded_at: string;
  notes: string;
}


export interface UserReportItem {
  email: string;
  name: string;
  role: string;
  credit_score: number;
  restricted: boolean;
}


export interface ActivityLogReportItem {
  user: string;
  action: string;
  module: string;
  description: string;
  created_at: string;
}

export type ReportItem =
  | LoanReportItem
  | ReturnReportItem
  | ViolationReportItem
  | SettlementReportItem
  | InventoryReportItem
  | UnitConditionReportItem
  | UserReportItem
  | ActivityLogReportItem;

export interface ReportPreviewResponse {
  data: ReportItem[];
  message: string;
}

export interface ReportExportRequest {
  type: ReportType;
  filters: ReportFilters;
}

export interface ReportState {
  selectedType: ReportType | null;
  filters: ReportFilters;
  previewData: ReportItem[];
  loading: boolean;
  error: string | null;
  successMessage: string | null;
}
