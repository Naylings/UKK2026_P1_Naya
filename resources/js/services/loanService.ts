import { AxiosError } from "axios";
import type { ApiErrorResponse } from "@/types/auth";

import { loanApi } from "@/api/loan";
import type {
    AvailableToolUnit,
    CreateLoanPayload,
    CreateLoanResponse,
    LoanListResponse,
    LoanReviewPayload,
    LoanReviewResponse,
    ToolUnitAvailabilityParams,
} from "@/types/loan";


export function parseLoanError(error: unknown): string {
    if (error instanceof AxiosError) {
        const data = error.response?.data as ApiErrorResponse | undefined;

        if (data?.message) return data.message;

        switch (error.response?.status) {
            case 401:
                return "Sesi tidak valid. Silakan login ulang.";
            case 403:
                return "Anda tidak memiliki akses.";
            case 404:
                return "Data tidak ditemukan.";
            case 422:
                return "Data tidak valid.";
            case 500:
                return "Terjadi kesalahan server.";
        }
    }

    return "Terjadi kesalahan tidak diketahui.";
}


export const loanRequestService = {
    
    async getAvailableUnits(
        params: ToolUnitAvailabilityParams,
    ): Promise<AvailableToolUnit[]> {
        try {
            const res = await loanApi.getAvailable(params);
            return res.data;
        } catch (error) {
            throw parseLoanError(error);
        }
    },

    
    async submitLoan(payload: CreateLoanPayload): Promise<CreateLoanResponse> {
        try {
            const res = await loanApi.createLoan(payload);
            return res;
        } catch (error) {
            throw parseLoanError(error);
        }
    },

    async getAllLoans(params?: {
        status?: string;
        search?: string;
        page?: number;
        per_page?: number;
    }): Promise<LoanListResponse> {
        try {
            const res = await loanApi.getLoans(params);
            return res;
        } catch (error) {
            throw parseLoanError(error);
        }
    },

    async getMyLoans(params?: {
        status?: string;
        search?: string;
        page?: number;
        per_page?: number;
    }): Promise<LoanListResponse> {
        console.log("loanService.getMyLoans params:", params);
        try {
            const res = await loanApi.getMyLoans(params);
            return res;
        } catch (error) {
            throw parseLoanError(error);
        }
    },

    async approveLoan(
        loanId: number,
        payload?: LoanReviewPayload,
    ): Promise<LoanReviewResponse> {
        try {
            const res = await loanApi.approveLoan(loanId, payload);
            return res;
        } catch (error) {
            throw parseLoanError(error);
        }
    },

    async rejectLoan(
        loanId: number,
        payload?: LoanReviewPayload,
    ): Promise<LoanReviewResponse> {
        try {
            const res = await loanApi.rejectLoan(loanId, payload);
            return res;
        } catch (error) {
            throw parseLoanError(error);
        }
    },
};
