
import type { PaginatedToolsResponse, Tool, ToolResponse } from "@/types/tool";
import apiClient from "./client";
import type { ApiMessageResponse, LaravelApiResponse } from "@/types/auth";

export const toolsApi = {
  
  list: async (params?: {
    search?: string;
    category?: string;
    per_page?: number;
    page?: number;
  }): Promise<PaginatedToolsResponse> => {
    const res = await apiClient.get<PaginatedToolsResponse>("/tools", { params });
    return res.data;
  },

  
  get: async (id: number): Promise<Tool> => {
    const res = await apiClient.get<ToolResponse>(`/tools/${id}`);
    return res.data.data;
  },

  
  create: async (payload: FormData): Promise<LaravelApiResponse<Tool>> => {
    const res = await apiClient.post<LaravelApiResponse<Tool>>("/tools", payload, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
    return res.data;
  },

  
  update: async (
    id: number,
    payload: FormData,
  ): Promise<LaravelApiResponse<Tool>> => {
    payload.append("_method", "PUT");

    const res = await apiClient.post<LaravelApiResponse<Tool>>(
      `/tools/${id}`,
      payload,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      },
    );
    return res.data;
  },

  
  delete: async (id: number): Promise<string> => {
    const res = await apiClient.delete<ApiMessageResponse>(`/tools/${id}`);
    return res.data.message;
  },
};
