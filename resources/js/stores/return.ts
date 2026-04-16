import { defineStore } from "pinia";
import { ref } from "vue";
import { returnService } from "@/services/returnService";
import type {
  ReturnResponse,
  ReviewReturnPayload,
  CreateReturnPayload,
} from "@/types/return";

export const useReturnStore = defineStore("return", () => {
  // ── State ──────────────────────────────────────────────

  const returns = ref<ReturnResponse[]>([]);
  const meta = ref<any>(null);

  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  // ── Actions ────────────────────────────────────────────

  function reset() {
    error.value = null;
    successMessage.value = null;
  }

  /** User: Submit return request */
  async function createReturn(
    loanId: number,
    payload: CreateReturnPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await returnService.createReturn(loanId, payload);
      successMessage.value = res.message;
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /** Employee/Admin: Fetch list of returns */
  async function fetchReturns(params?: any) {
    loading.value = true;
    error.value = null;

    try {
      const res = await returnService.getReturns(params);
      returns.value = res.data;
      meta.value = res.meta ?? null;
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /** Employee: Confirm/Review a return */
  async function confirmReturn(
    loanId: number,
    payload: ReviewReturnPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await returnService.confirmReturn(loanId, payload);
      successMessage.value = res.message;
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      loading.value = false;
    }
  }

  return {
    // state
    returns,
    meta,
    loading,
    error,
    successMessage,

    // actions
    reset,
    createReturn,
    fetchReturns,
    confirmReturn,
  };
});
