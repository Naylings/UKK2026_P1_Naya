
import type { Tool } from "@/types/tool";
import apiClient from "./client";
import type {
    AvailableToolUnitsResponse,
    CreateLoanPayload,
    CreateLoanResponse,
    LoanListResponse,
    LoanReviewPayload,
    LoanReviewResponse,
} from "@/types/loan";

export const loanApi = {
    
    getTool: async (toolId: number): Promise<Tool> => {
        const res = await apiClient.get<Tool>(`/tools/${toolId}`);
        return res.data;
    },

    

    getAvailable: async (params: {
        tool_id: number;
        loan_date: string;
        due_date: string;
    }): Promise<AvailableToolUnitsResponse> => {
        const res = await apiClient.get<AvailableToolUnitsResponse>(
            "/tool-units/available",
            { params },
        );

        return res.data;
    },

    
    createLoan: async (
        payload: CreateLoanPayload,
    ): Promise<CreateLoanResponse> => {
        const res = await apiClient.post<CreateLoanResponse>("/loans", payload);
        return res.data;
    },

    getLoans: async (params?: {
        status?: string;
        search?: string;
        per_page?: number;
        page?: number;
    }): Promise<LoanListResponse> => {
        const res = await apiClient.get<LoanListResponse>("/loans", { params });

        return res.data;
    },

    getMyLoans: async (params?: {
        status?: string;
        search?: string;
        page?: number;
        per_page?: number;
    }): Promise<LoanListResponse> => {
        const res = await apiClient.get<LoanListResponse>("/loans/my", {
            params,
        });

        return res.data;
    },

    approveLoan: async (
        loanId: number,
        payload?: LoanReviewPayload,
    ): Promise<LoanReviewResponse> => {
        const res = await apiClient.post<LoanReviewResponse>(
            `/loans/${loanId}/approve`,
            payload,
        );

        return res.data;
    },

    rejectLoan: async (
        loanId: number,
        payload?: LoanReviewPayload,
    ): Promise<LoanReviewResponse> => {
        const res = await apiClient.post<LoanReviewResponse>(
            `/loans/${loanId}/reject`,
            payload,
        );

        return res.data;
    },
};
