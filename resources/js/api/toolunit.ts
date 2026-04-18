
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

    
    get: async (code: string): Promise<ToolUnit> => {
        const res = await apiClient.get<ToolUnitResponse>(
            `/tool-units/${code}`,
        );
        return res.data.data;
    },

    
    create: async (
        payload: CreateToolUnitPayload,
    ): Promise<LaravelApiResponse<ToolUnit | ToolUnit[]>> => {
        const res = await apiClient.post<
            LaravelApiResponse<ToolUnit | ToolUnit[]>
        >("/tool-units", payload);
        return res.data;
    },

    
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

    
    delete: async (code: string): Promise<ApiMessageResponse> => {
        const res = await apiClient.delete<ApiMessageResponse>(
            `/tool-units/${code}`,
        );
        return res.data;
    },

    
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

    
    getConditionHistory: async (
        code: string,
    ): Promise<ConditionHistoryResponse> => {
        const res = await apiClient.get<ConditionHistoryResponse>(
            `/tool-units/${code}/history`,
        );
        return res.data;
    },
};
