// ─────────────────────────────────────────────
// stores/settlement.ts
// Pinia store untuk settlement (pelunasan)
// ─────────────────────────────────────────────

import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { settlementService } from "@/services/settlementService";
import type { Settlement } from "@/types/settlement";

export const useSettlementStore = defineStore("settlement", () => {
  // ── State ──────────────────────────────────────────────

  const settlements = ref<Settlement[]>([]);
  const currentSettlement = ref<Settlement | null>(null);

  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  // ── Pagination ─────────────────────────────────────────

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  // ── Getters ────────────────────────────────────────────

  const settlementCount = computed(() => settlements.value.length);
  const hasSettlements = computed(() => settlements.value.length > 0);

  // ── Actions ────────────────────────────────────────────

  /**
   * Ambil semua settlement
   */
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

  /**
   * Ambil detail settlement
   */
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

  /**
   * Proses pelunasan (SETTLE)
   */
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

      // optional: push ke list (kalau mau langsung tampil)
      settlements.value.unshift(result.settlement);

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Clear error
   */
  function clearError(): void {
    error.value = null;
  }

  /**
   * Reset state
   */
  function reset(): void {
    settlements.value = [];
    currentSettlement.value = null;
    loading.value = false;
    error.value = null;
    successMessage.value = null;
  }

  return {
    // pagination
    currentPage,
    lastPage,
    total,
    perPage,

    // state
    settlements,
    currentSettlement,
    loading,
    error,
    successMessage,

    // getters
    settlementCount,
    hasSettlements,

    // actions
    fetchSettlements,
    fetchSettlementById,
    settleViolation,
    clearError,
    reset,
  };
});