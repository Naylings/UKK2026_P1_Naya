export interface CreateReturnPayload {
  proof?: File | null;
}

export interface ReviewReturnPayload {
  condition: ReturnConditionType;
  condition_notes?: string | null;
  violation_type?: "late" | "damaged" | "lost" | "other" | null;
  total_score?: number | null;
  fine?: number | null;
  description?: string | null;
}

export interface ReturnEmployee {
  id: number;
  email?: string;
  role?: string;
  details?: {
    nik: string;
    name: string;
    no_hp: string;
    address: string;
    birth_date?: string;
  } | null;
}

export type ReturnConditionType = "good" | "broken" | "maintenance";

export interface ReturnConditionItem {
  id: string;
  unit_code: string;
  conditions: ReturnConditionType;
  notes?: string | null;
  recorded_at: string;
}

export interface ReturnLoan {
  id: number;
  status: string;
  loan_date: string;
  due_date: string;
  purpose: string;
  tool?: {
    id: number;
    name: string;
    price: number;
    item_type: "single" | "bundle" | "bundle_tool";
    code_slug?: string;
    bundle_components?: Array<{
      name: string;
      qty: number;
      code: string;
      price: number;
    }> | null;
  };
  unit?: {
    code: string;
    status?: string;
  };
}

export interface ReturnViolation {
  id: number;
  type: string;
  fine: number;
  total_score: number;
  description?: string;
  status: string;
}

export interface ReturnResponse {
  id: number;
  return_date: string;
  proof?: string | null;
  created_at: string;
  employee?: ReturnEmployee | null;
  conditions?: ReturnConditionItem[];
  loan?: ReturnLoan | null;
  violation?: ReturnViolation | null;
}

export interface CreateReturnResponse {
  message: string;
  data: ReturnResponse;
}

export interface ReturnListResponse {
  data: ReturnResponse[];
  meta?: {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
  };
}
