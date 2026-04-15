import apiClient from "./client";
import type {
    CreateReturnPayload,
    CreateReturnResponse,
    ReturnListResponse,
} from "@/types/return";

export const returnApi = {
    // =========================
    // CREATE RETURN (BY LOAN ID)
    // POST /returns/{loanId}
    // =========================
    createReturn: async (
        loanId: number,
        payload: CreateReturnPayload,
    ): Promise<CreateReturnResponse> => {
        const formData = new FormData();

        if (payload.notes) {
            formData.append("notes", payload.notes);
        }

        if (payload.proof) {
            formData.append("proof", payload.proof);
        }

        const res = await apiClient.post<CreateReturnResponse>(
            `/returns/${loanId}`,
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            },
        );

        return res.data;
    },

    // =========================
    // LIST RETURNS
    // GET /returns
    // =========================
    getReturns: async (params?: {
        status?: string;
        search?: string;
        page?: number;
        per_page?: number;
    }): Promise<ReturnListResponse> => {
        const res = await apiClient.get<ReturnListResponse>("/returns", {
            params,
        });

        return res.data;
    },

    // =========================
    // GET RETURN DETAIL
    // GET /returns/{id}
    // (INI RETURN ID, BUKAN LOAN ID)
    // =========================
    getReturnById: async (returnId: number) => {
        const res = await apiClient.get(`/returns/${returnId}`);
        return res.data;
    },

    // =========================
    // CONFIRM RETURN (EMPLOYEE)
    // POST /returns/{loanId}/confirm
    // =========================
    confirmReturn: async (loanId: number) => {
        const res = await apiClient.post(`/returns/${loanId}/confirm`);
        return res.data;
    },
};