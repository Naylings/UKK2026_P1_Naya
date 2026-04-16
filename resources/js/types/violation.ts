export interface ViolationUser {
  id: number;
  email?: string;
  details?: {
    name: string;
  } | null;
}

export interface ViolationLoan {
  id: number;
  loan_date?: string | null;
  due_date?: string | null;
  purpose?: string | null;
  tool?: {
    id: number;
    name: string;
    price?: number;
    item_type?: string;
    code_slug?: string;
  } | null;
  unit?: {
    code: string;
    status?: string;
  } | null;
}

export interface ViolationEmployeeRef {
  id: number;
  email?: string;
  details?: {
    name: string;
  } | null;
}

export interface ViolationToolReturn {
  id: number;
  return_date?: string | null;
  created_at?: string | null;
  employee?: ViolationEmployeeRef | null;
}

export interface ViolationSettlement {
  id: number;
  description: string;
  settled_at?: string | null;
  employee?: ViolationEmployeeRef | null;
}

export interface Violation {
  id: number;
  type: "late" | "damaged" | "lost" | "other";
  total_score: number;
  fine: number;
  description?: string | null;
  status: "active" | "settled";
  created_at?: string | null;
  user?: ViolationUser | null;
  loan?: ViolationLoan | null;
  tool_return?: ViolationToolReturn | null;
  settlement?: ViolationSettlement | null;
}

export interface ViolationResponse {
  data: Violation;
}

export interface ViolationListResponse {
  data: Violation[];
  meta: {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
  };
}
