import { defineStore } from "pinia";
import { ref } from "vue";
import { returnService } from "@/services/returnService";
import type {
  ReturnResponse,
  ReviewReturnPayload,
  CreateReturnPayload,
} from "@/types/return";

export const useReturnStore = defineStore("return", () => {
  

  const returns = ref<ReturnResponse[]>([]);
  const meta = ref<any>(null);

  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  

  function reset() {
    error.value = null;
    successMessage.value = null;
  }

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
    
    returns,
    meta,
    loading,
    error,
    successMessage,

    
    reset,
    createReturn,
    fetchReturns,
    confirmReturn,

    
    $reset() {
      returns.value = [];
      meta.value = null;
      loading.value = false;
      error.value = null;
      successMessage.value = null;
    },
  };
});
