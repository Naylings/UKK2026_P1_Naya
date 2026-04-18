import type { UserDetail } from "./user";






export type AppealStatus = 'pending' | 'approved' | 'rejected';

export interface AppealFilters {
    status?: AppealStatus;
    search?: string;
    page?: number;
    per_page?: number;
}

export interface Appeal {
    id: number;
    reason: string;
    status: AppealStatus;
    credit_changed?: number | null;
    admin_notes?: string | null;
    created_at: string;
    reviewed_at?: string | null;

    user: {
        id: number;
        details?: UserDetail | null;
    };
    
    reviewer?: {
        id: number;
        email: string;
        role: string;
        details?: UserDetail | null;
    } | null;
}

export interface CreateAppealPayload {
    reason: string;
}

export interface CreateAppealResponse {
    message: string;
    data: Appeal;
}

export interface ReviewAppealPayload {
    status: AppealStatus;
    credit_changed?: number | string;
    admin_notes?: string;
}

export interface ReviewAppealResponse {
    message: string;
    data: Appeal;
}

export interface AppealListResponse {
  data: Appeal[];
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}


