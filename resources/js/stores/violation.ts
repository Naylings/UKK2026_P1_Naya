import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { violationService } from "@/services/violationService";
import type { Violation } from "@/types/violation";

export const useViolationStore = defineStore("violation", () => {
  const violations = ref<Violation[]>([]);
  const currentViolation = ref<Violation | null>(null);

  const loading = ref(false);
  const error = ref<string | null>(null);

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  const violationCount = computed(() => violations.value.length);

  async function fetchViolations(params?: {
    search?: string;
    status?: string;
    type?: string;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await violationService.getAll(params);

      violations.value = res.data;
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

  async function fetchViolationById(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      currentViolation.value = await violationService.getById(id);
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  function reset() {
    violations.value = [];
    currentViolation.value = null;
    error.value = null;
    loading.value = false;
  }

  return {
    violations,
    currentViolation,
    loading,
    error,
    currentPage,
    lastPage,
    total,
    perPage,
    violationCount,
    fetchViolations,
    fetchViolationById,
    reset,
  };
});
