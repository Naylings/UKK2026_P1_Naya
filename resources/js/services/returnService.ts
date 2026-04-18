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

  
  async getReturns(params?: any): Promise<ReturnListResponse> {
    try {
      return await returnApi.getReturns(params);
    } catch (error) {
      throw parseError(error);
    }
  },

  
  async getReturnById(id: number): Promise<ReturnResponse> {
    try {
      const res = await returnApi.getReturnById(id);
      return res.data;
    } catch (error) {
      throw parseError(error);
    }
  },

  
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
