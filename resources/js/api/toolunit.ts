// ─────────────────────────────────────────────
// api/toolunit.ts
// Pure HTTP calls untuk tool unit management — tidak ada side effect
// ─────────────────────────────────────────────

import type {
    PaginatedToolUnitsResponse,
    ToolUnit,
    ToolUnitResponse,
    CreateToolUnitPayload,
    UpdateToolUnitPayload,
    RecordConditionPayload,
    ConditionHistoryResponse,
    UnitCondition,
} from "@/types/toolunit";
import apiClient from "./client";
import type { ApiMessageResponse, LaravelApiResponse } from "@/types/auth";
import type { AvailableToolUnitsResponse } from "@/types/loan";

export const toolUnitApi = {
    /**
     * GET /api/tool-units
     * Ambil semua unit dengan pagination, filter, dan search
     */
    list: async (params?: {
        search?: string;
        tool_id?: number;
        status?: string;
        per_page?: number;
        page?: number;
    }): Promise<PaginatedToolUnitsResponse> => {
        const res = await apiClient.get<PaginatedToolUnitsResponse>(
            "/tool-units",
            {
                params,
            },
        );
        return res.data;
    },

    /**
     * GET /api/tool-units/:code
     * Ambil detail unit berdasarkan code
     */
    get: async (code: string): Promise<ToolUnit> => {
        const res = await apiClient.get<ToolUnitResponse>(
            `/tool-units/${code}`,
        );
        return res.data.data;
    },

    /**
     * POST /api/tool-units
     * Buat unit baru (single atau bulk)
     */
    create: async (
        payload: CreateToolUnitPayload,
    ): Promise<LaravelApiResponse<ToolUnit | ToolUnit[]>> => {
        const res = await apiClient.post<
            LaravelApiResponse<ToolUnit | ToolUnit[]>
        >("/tool-units", payload);
        return res.data;
    },

    /**
     * PUT /api/tool-units/:code
     * Update status unit
     */
    update: async (
        code: string,
        payload: UpdateToolUnitPayload,
    ): Promise<LaravelApiResponse<ToolUnit>> => {
        const res = await apiClient.put<LaravelApiResponse<ToolUnit>>(
            `/tool-units/${code}`,
            payload,
        );
        return res.data;
    },

    /**
     * DELETE /api/tool-units/:code
     * Hapus unit
     */
    delete: async (code: string): Promise<ApiMessageResponse> => {
        const res = await apiClient.delete<ApiMessageResponse>(
            `/tool-units/${code}`,
        );
        return res.data;
    },

    /**
     * POST /api/tool-units/:code/record-condition
     * Catat kondisi unit baru
     */
    recordCondition: async (
        code: string,
        payload: RecordConditionPayload,
    ): Promise<LaravelApiResponse<UnitCondition>> => {
        const res = await apiClient.post<LaravelApiResponse<UnitCondition>>(
            `/tool-units/${code}/record-condition`,
            payload,
        );
        return res.data;
    },

    /**
     * GET /api/tool-units/:code/history
     * Ambil history kondisi unit
     */
    getConditionHistory: async (
        code: string,
    ): Promise<ConditionHistoryResponse> => {
        const res = await apiClient.get<ConditionHistoryResponse>(
            `/tool-units/${code}/history`,
        );
        return res.data;
    },
};
