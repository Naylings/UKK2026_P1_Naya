import { returnApi } from "@/api/return";
import { parseLoanError as parseError } from "./loanService";
import type {
  CreateReturnPayload,
  CreateReturnResponse,
  ReturnListResponse,
  ReturnResponse,
  ReviewReturnPayload,
} from "@/types/return";

export const returnService = {
  /**
   * Submit return request (User)
   */
  async createReturn(
    loanId: number,
    payload: CreateReturnPayload,
  ): Promise<CreateReturnResponse> {
    try {
      return await returnApi.createReturn(loanId, payload);
    } catch (error) {
      throw parseError(error);
    }
  },

  /**
   * Get all returns (Employee/Admin)
   */
  async getReturns(params?: any): Promise<ReturnListResponse> {
    try {
      return await returnApi.getReturns(params);
    } catch (error) {
      throw parseError(error);
    }
  },

  /**
   * Get return detail by ID
   */
  async getReturnById(id: number): Promise<ReturnResponse> {
    try {
      const res = await returnApi.getReturnById(id);
      return res.data;
    } catch (error) {
      throw parseError(error);
    }
  },

  /**
   * Confirm and review return (Employee)
   */
  async confirmReturn(
    loanId: number,
    payload: ReviewReturnPayload,
  ): Promise<CreateReturnResponse> {
    try {
      return await returnApi.confirmReturn(loanId, payload);
    } catch (error) {
      throw parseError(error);
    }
  },
};
