export type ReturnStatus = "ready" | "returned" | "expired";

export interface CreateReturnPayload {
    proof?: File | null;
    notes?: string | null;
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

export interface ReturnConditionItem {
    id: string;
    unit_code: string;
    conditions: "good" | "broken" | "maintenance";
    notes?: string | null;
    recorded_at: string;
}

export interface ReturnLoan {
    id: number;
    status: string;
    loan_date: string;
    due_date: string;

    tool?: {
        id: number;
        name: string;
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
    notes?: string | null;
    created_at: string;

    employee?: ReturnEmployee | null;

    conditions?: ReturnConditionItem[]; // 🔥 FIX: plural array

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
    };
}
