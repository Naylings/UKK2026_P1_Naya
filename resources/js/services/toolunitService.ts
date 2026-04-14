// ─────────────────────────────────────────────
// services/toolunitService.ts
// Business logic FE: parsing response, error handling
// ─────────────────────────────────────────────

import { AxiosError } from "axios";
import { toolUnitApi } from "@/api/toolunit";
import type {
    ToolUnit,
    CreateToolUnitPayload,
    UpdateToolUnitPayload,
    RecordConditionPayload,
    PaginatedToolUnitsResponse,
    UnitCondition,
    ToolUnitQueryParams,
} from "@/types/toolunit";
import type { ApiErrorResponse } from "@/types/auth";

interface ToolUnitMutationResult {
    units: ToolUnit | ToolUnit[];
    message: string;
    isBulk: boolean;
}

interface ConditionRecordResult {
    condition: UnitCondition;
    message: string;
}

// ── Error helper ──────────────────────────────────────────────────────────

/**
 * Ekstrak pesan error dari response BE maupun network error.
 * Selalu mengembalikan string yang siap ditampilkan ke user.
 */
export function parseToolUnitError(error: unknown): string {
    if (error instanceof AxiosError) {
        const data = error.response?.data as ApiErrorResponse | undefined;

        if (data?.message) return data.message;

        switch (error.response?.status) {
            case 401:
                return "Sesi tidak valid. Silakan login ulang.";
            case 403:
                return "Anda tidak memiliki akses.";
            case 404:
                return "Unit tool tidak ditemukan.";
            case 422:
                return "Data yang dikirim tidak valid.";
            case 500:
                return "Terjadi kesalahan pada server. Coba lagi nanti.";
        }
    }

    return "Terjadi kesalahan tidak diketahui.";
}

// ── Service methods ───────────────────────────────────────────────────────

export const toolunitService = {
    /**
     * Ambil semua unit dengan pagination, filter, dan search.
     */
    async getAll(
        params?: ToolUnitQueryParams,
    ): Promise<PaginatedToolUnitsResponse> {
        try {
            return await toolUnitApi.list(params);
        } catch (error) {
            throw parseToolUnitError(error);
        }
    },

    /**
     * Ambil unit berdasarkan code.
     */
    async getByCode(code: string): Promise<ToolUnit> {
        try {
            return await toolUnitApi.get(code);
        } catch (error) {
            throw parseToolUnitError(error);
        }
    },

    /**
     * Buat unit baru (single atau bulk).
     */
    async create(
        payload: CreateToolUnitPayload,
    ): Promise<ToolUnitMutationResult> {
        try {
            const response = await toolUnitApi.create(payload);
            const quantity = payload.quantity ?? 1;
            const isBulk = quantity > 1;

            return {
                units: response.data,
                message:
                    response.message ??
                    (isBulk
                        ? `${quantity} unit berhasil dibuat.`
                        : "Unit berhasil dibuat."),
                isBulk,
            };
        } catch (error) {
            throw parseToolUnitError(error);
        }
    },

    /**
     * Update status unit.
     */
    async update(
        code: string,
        payload: UpdateToolUnitPayload,
    ): Promise<ToolUnitMutationResult> {
        try {
            const response = await toolUnitApi.update(code, payload);
            return {
                units: response.data,
                message: response.message ?? "Unit berhasil diupdate.",
                isBulk: false,
            };
        } catch (error) {
            throw parseToolUnitError(error);
        }
    },

    /**
     * Hapus unit.
     */
    async delete(code: string): Promise<string> {
        try {
            const response = await toolUnitApi.delete(code);
            return response.message ?? "Unit berhasil dihapus.";
        } catch (error) {
            throw parseToolUnitError(error);
        }
    },

    /**
     * Catat kondisi unit.
     */
    async recordCondition(
        code: string,
        payload: RecordConditionPayload,
    ): Promise<ConditionRecordResult> {
        try {
            const response = await toolUnitApi.recordCondition(code, payload);
            return {
                condition: response.data,
                message: response.message ?? "Kondisi unit berhasil dicatat.",
            };
        } catch (error) {
            throw parseToolUnitError(error);
        }
    },

    /**
     * Ambil history kondisi unit.
     */
    async getConditionHistory(code: string): Promise<UnitCondition[]> {
        try {
            const response = await toolUnitApi.getConditionHistory(code);
            return response.data;
        } catch (error) {
            throw parseToolUnitError(error);
        }
    },
};
