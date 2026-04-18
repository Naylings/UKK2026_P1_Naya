



export interface DashboardParams {
  search?: string;
  per_page?: number;
}

export interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}





export interface AdminDashboardSummary {
  total_users: number;
  total_tools: number;
  active_loans: number;
  pending_loans: number;
  pending_returns: number;
  active_violations: number;
}

export interface AdminDashboardAlerts {
  overdue_loans: number;
  due_today: number;
  unreviewed_returns: number;
  unsettled_violations: number;
}

export interface AdminDashboardStats {
  returns_today: number;
  settlements_today: number;
}

export interface AdminDashboardResponse {
  summary: AdminDashboardSummary;
  alerts: AdminDashboardAlerts;

  pending: {
    loans: any[];
    returns: any[];
    violations: any[];
  };

  recent_activities: SimpleActivityLog[];
  stats: AdminDashboardStats;
}





export interface UserDashboardSummary {
  credit_score: number;
  is_restricted: boolean;
  has_active_loan: boolean;
  total_violations_count: number;
}

export interface UserDashboardAlerts {
  has_overdue: boolean;
  is_due_soon: boolean;
  has_active_violation: boolean;
}





export interface SimpleLoan {
  id: number;
  unit_code?: string;
  loan_date?: string;
  due_date?: string;
  status?: string;
  tool: {
    id?: number;
    name: string;
  };
  unit?: {
    code: string;
    tool_id?: number;
  };
}

export interface SimpleReturn {
  id: number;
  return_date: string;
  reviewed: boolean;
  proof?: string;
  loan: SimpleLoan;
}

export interface SimpleViolation {
  id: number;
  type: string;
  total_score?: number;
  fine: number;
  status: string;
  description: string;
  settlement?: SimpleSettlement;
}

export interface SimpleSettlement {
  id: number;
  description: string;
  settled_at: string;
  violation?: {
    id: number;
    loan?: SimpleLoan;
  };
}

export interface SimpleAppeal {
  id: number;
  reason: string;
  status: string;
}

export interface SimpleRecommendation {
  id: number;
  name: string;
}

export interface SimpleActivityLog {
  id: number;
  action: string;
  module: string;
  description: string;
  created_at: string;
}





export interface UserDashboardResponse {
  summary: UserDashboardSummary;
  alerts: UserDashboardAlerts;

  active_loans: Paginated<SimpleLoan>;
  return_history: SimpleReturn[];

  violations: Paginated<SimpleViolation>;
  settlements: SimpleSettlement[];

  appeals: SimpleAppeal[];
  recommendations: SimpleRecommendation[];

  recent_activities: SimpleActivityLog[];
}





export type DashboardResponse =
  | { role: "admin"; data: AdminDashboardResponse }
  | { role: "user"; data: UserDashboardResponse };