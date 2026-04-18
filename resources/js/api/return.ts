import apiClient from "./client";
import type {
  CreateReturnPayload,
  CreateReturnResponse,
  ReturnListResponse,
  ReturnResponse,
  ReviewReturnPayload,
} from "@/types/return";

export const returnApi = {
  
  createReturn: async (
    loanId: number,
    payload: CreateReturnPayload,
  ): Promise<CreateReturnResponse> => {
    const formData = new FormData();


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

  
  getReturnById: async (
    returnId: number,
  ): Promise<{ data: ReturnResponse }> => {
    const res = await apiClient.get(`/returns/${returnId}`);
    return res.data;
  },

  
  confirmReturn: async (
    loanId: number,
    payload: ReviewReturnPayload,
  ): Promise<CreateReturnResponse> => {
    const res = await apiClient.post<CreateReturnResponse>(
      `/returns/${loanId}/confirm`,
      payload,
    );

    return res.data;
  },
};
