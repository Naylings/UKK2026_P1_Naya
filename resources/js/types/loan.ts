import type { ToolUnit } from "./toolunit";

// ─────────────────────────────────────────────
// types/loan.ts
// Semua type yang berhubungan dengan loan request domain
// Availability (Loan search feature)
// ─────────────────────────────────────────────

export interface ToolUnitAvailabilityParams {
    tool_id: number;
    loan_date: string; // YYYY-MM-DD
    due_date: string; // YYYY-MM-DD
}
export interface AvailableToolUnit extends ToolUnit {
    availability_reason: string;
}
export interface AvailableToolUnitsResponse {
    data: AvailableToolUnit[];
    message: string;
}

export type LoanStatus = "pending" | "active" | "rejected" | "closed";

export interface LoanFilters {
  status: string;
  search?: string;
  page: number;
  per_page: number;
}

export interface Loan {
    id: number;
    tool_id: number;
    unit_code: string;
    user_id: number;

    status: LoanStatus;

    loan_date: string;
    due_date: string;

    purpose: string;
    notes?: string | null;

    created_at: string;
    updated_at?: string | null;
}

export interface CreateLoanPayload {
    tool_id: number;
    unit_code: string;
    loan_date: string; // YYYY-MM-DD
    due_date: string;  // YYYY-MM-DD
    purpose: string;
}

export interface LoanUser {
    id: number;
    name: string;
}

export interface LoanTool {
    id: number;
    name: string;
}

export interface LoanUnit {
    code: string;
}

export interface LoanResponse {
    id: number;
    status: LoanStatus;
    loan_date: string;
    due_date: string;
    purpose: string;

    user: LoanUser;
    tool: LoanTool;
    unit: LoanUnit;
}

export interface CreateLoanResponse {
    message: string;
    data: LoanResponse;
}

export interface LoanListResponse {
    data: LoanResponse[];
    meta?: {
        current_page: number;
        last_page: number;
        total: number;
    };
}
