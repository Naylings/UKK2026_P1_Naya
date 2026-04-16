import apiClient from "./client";
import type {
  CreateReturnPayload,
  CreateReturnResponse,
  ReturnListResponse,
  ReturnResponse,
  ReviewReturnPayload,
} from "@/types/return";

export const returnApi = {
  /**
   * POST /api/returns/{loanId}
   * Submit return request (User)
   */
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

  /**
   * GET /api/returns
   * List returns (Petugas/Admin)
   */
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

  /**
   * GET /api/returns/{id}
   * Get return detail
   */
  getReturnById: async (
    returnId: number,
  ): Promise<{ data: ReturnResponse }> => {
    const res = await apiClient.get(`/returns/${returnId}`);
    return res.data;
  },

  /**
   * POST /api/returns/{loanId}/confirm
   * Confirm/Review return (Employee)
   */
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
