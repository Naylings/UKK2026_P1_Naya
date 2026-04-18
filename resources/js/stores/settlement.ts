




import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { settlementService } from "@/services/settlementService";
import type { Settlement } from "@/types/settlement";

export const useSettlementStore = defineStore("settlement", () => {
  

  const settlements = ref<Settlement[]>([]);
  const currentSettlement = ref<Settlement | null>(null);

  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  

  const settlementCount = computed(() => settlements.value.length);
  const hasSettlements = computed(() => settlements.value.length > 0);

  

  
  async function fetchSettlements(params?: {
    search?: string;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await settlementService.getAll(params);

      settlements.value = res.data;

      currentPage.value = res.meta.current_page;
      lastPage.value = res.meta.last_page;
      total.value = res.meta.total;
      perPage.value = res.meta.per_page;

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function fetchSettlementById(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      currentSettlement.value = await settlementService.getById(id);
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function settleViolation(
    violationId: number,
    description: string
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await settlementService.settle(
        violationId,
        description
      );

      successMessage.value = result.message;

      
      settlements.value.unshift(result.settlement);

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  function clearError(): void {
    error.value = null;
  }

  
  function reset(): void {
    settlements.value = [];
    currentSettlement.value = null;
    loading.value = false;
    error.value = null;
    successMessage.value = null;
  }

  return {
    
    currentPage,
    lastPage,
    total,
    perPage,

    
    settlements,
    currentSettlement,
    loading,
    error,
    successMessage,

    
    settlementCount,
    hasSettlements,

    
    fetchSettlements,
    fetchSettlementById,
    settleViolation,
    clearError,
    reset,

    
    $reset() {
      settlements.value = [];
      currentSettlement.value = null;
      loading.value = false;
      error.value = null;
      successMessage.value = null;
      currentPage.value = 1;
      lastPage.value = 1;
      total.value = 0;
      perPage.value = 10;
    },
  };
});