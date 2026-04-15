import { AxiosError } from "axios";
import type { ApiErrorResponse } from "@/types/auth";
import { returnApi } from "@/api/return";
import type { CreateReturnPayload, CreateReturnResponse, ReturnListResponse } from "@/types/return";

// ─────────────────────────────────────────────
// Error parser
// ─────────────────────────────────────────────

export function parseReturnError(error: unknown): string {
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

// ─────────────────────────────────────────────
// RETURN SERVICE
// ─────────────────────────────────────────────

export const returnService = {
    async createReturn(
        loanId: number,
        payload: CreateReturnPayload,
    ): Promise<CreateReturnResponse> {
        try {
            const res = await returnApi.createReturn(loanId, payload);
            return res;
        } catch (error) {
            throw parseReturnError(error);
        }
    },

    async getReturns(params?: {
        status?: string;
        search?: string;
        page?: number;
        per_page?: number;
    }): Promise<ReturnListResponse> {
        try {
            const res = await returnApi.getReturns(params);
            return res;
        } catch (error) {
            throw parseReturnError(error);
        }
    },
};