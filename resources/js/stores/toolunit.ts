// ─────────────────────────────────────────────
// stores/toolunit.ts
// Pinia store untuk tool unit management — state, getters, actions
// ─────────────────────────────────────────────

import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { toolunitService } from "@/services/toolunitService";
import type {
  ToolUnit,
  UnitStatus,
  UpdateToolUnitPayload,
  CreateToolUnitPayload,
  RecordConditionPayload,
  UnitCondition,
} from "@/types/toolunit";

export const useToolUnitStore = defineStore("toolunit", () => {
  // ── State ──────────────────────────────────────────────────────────────

  const toolUnits = ref<ToolUnit[]>([]);
  const currentUnit = ref<ToolUnit | null>(null);
  const conditionHistory = ref<UnitCondition[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  // ── Pagination ────────────────────────────────────────────────────────────

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  // ── Getters ────────────────────────────────────────────────────────────

  const unitCount = computed(() => toolUnits.value.length);
  const hasUnits = computed(() => toolUnits.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);

  const availableUnits = computed(
    () => toolUnits.value.filter((u) => u.is_available).length,
  );
  const lentUnits = computed(
    () => toolUnits.value.filter((u) => u.is_lent).length,
  );
  const nonactiveUnits = computed(
    () => toolUnits.value.filter((u) => u.is_nonactive).length,
  );

  // ── Actions ────────────────────────────────────────────────────────────

  async function fetchUnits(params?: {
    search?: string;
    tool_id?: number;
    status?: UnitStatus;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await toolunitService.getAll(params);

      toolUnits.value = res.data;
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

  async function fetchUnitByCode(code: string): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      currentUnit.value = await toolunitService.getByCode(code);
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Buat unit baru (single atau bulk).
   */
  async function createUnit(payload: CreateToolUnitPayload): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await toolunitService.create(payload);

      // Handle both single unit dan bulk units
      if (Array.isArray(result.units)) {
        toolUnits.value.push(...result.units);
      } else {
        toolUnits.value.push(result.units);
      }

      successMessage.value = result.message;
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function updateUnit(
    code: string,
    payload: UpdateToolUnitPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await toolunitService.update(code, payload);
      const updated = result.units as ToolUnit;
      successMessage.value = result.message;

      const index = toolUnits.value.findIndex((u) => u.code === code);
      if (index !== -1) {
        toolUnits.value[index] = updated;
      }

      if (currentUnit.value?.code === code) {
        currentUnit.value = updated;
      }

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function deleteUnit(code: string): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      successMessage.value = await toolunitService.delete(code);

      toolUnits.value = toolUnits.value.filter((u) => u.code !== code);

      if (currentUnit.value?.code === code) {
        currentUnit.value = null;
      }

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Catat kondisi unit.
   */
  async function recordCondition(
    code: string,
    payload: RecordConditionPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await toolunitService.recordCondition(code, payload);

      // Update currentUnit jika ada
      if (currentUnit.value?.code === code && result.condition) {
        currentUnit.value.latest_condition = result.condition;
      }

      successMessage.value = "Kondisi unit berhasil dicatat.";
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  /**
   * Ambil history kondisi unit.
   */
  async function fetchConditionHistory(code: string): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      conditionHistory.value = await toolunitService.getConditionHistory(code);
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

  function clearSuccessMessage(): void {
    successMessage.value = null;
  }

  function reset(): void {
    toolUnits.value = [];
    currentUnit.value = null;
    conditionHistory.value = [];
    loading.value = false;
    error.value = null;
    successMessage.value = null;
    currentPage.value = 1;
    lastPage.value = 1;
    total.value = 0;
    perPage.value = 10;
  }

  return {
    // State
    toolUnits,
    currentUnit,
    conditionHistory,
    loading,
    error,
    successMessage,
    currentPage,
    perPage,
    lastPage,
    total,

    // Getters
    unitCount,
    hasUnits,
    isLoading,
    hasError,
    availableUnits,
    lentUnits,
    nonactiveUnits,

    // Actions
    fetchUnits,
    fetchUnitByCode,
    createUnit,
    updateUnit,
    deleteUnit,
    recordCondition,
    fetchConditionHistory,
    clearError,
    clearSuccessMessage,
    reset,
  };
});
