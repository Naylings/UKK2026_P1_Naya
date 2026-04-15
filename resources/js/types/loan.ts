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

export type LoanStatus = "pending" | "approve" | "rejected" | "expired";

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
    due_date: string; // YYYY-MM-DD
    purpose: string;
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

export interface LoanReviewPayload {
    notes?: string;
}

export interface LoanReviewResponse {
    message: string;
    data: LoanResponse;
}

export interface LoanUserDetail {
    nik: string;
    name: string;
    no_hp: string;
    address: string;
    birth_date?: string;
}

export interface LoanUser {
    id: number;
    details?: LoanUserDetail | null;
}

export interface LoanTool {
    id: number;
    name: string;
}

export interface LoanUnit {
    code: string;
    status?: string;
}

export interface LoanEmployee {
    id: number;
    email?: string;
    role?: string;
    details?: LoanUserDetail | null;
}

export interface LoanReview {
    employee_id?: number;
    notes?: string | null;
    employee?: LoanEmployee | null;
}

export interface LoanResponse {
    id: number;
    status: LoanStatus;

    loan_date: string;
    due_date: string;
    purpose: string;
    notes?: string | null;

    user?: LoanUser;
    tool?: LoanTool;
    unit?: LoanUnit;
    review?: LoanReview;

    employee?: LoanEmployee | null;

    created_at: string;
}

export type LoanReviewAction = "approve" | "reject";

