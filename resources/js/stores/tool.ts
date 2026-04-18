




import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { toolService } from "@/services/toolService";
import type { Tool } from "@/types/tool";

export const useToolStore = defineStore("tool", () => {
  

  const tools = ref<Tool[]>([]);
  const currentTool = ref<Tool | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  

  const toolCount = computed(() => tools.value.length);
  const hasTools = computed(() => tools.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);

  

  async function fetchTools(params?: {
    search?: string;
    category?: string | number;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await toolService.getAll(params);

      tools.value = res.data;
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

  async function fetchToolById(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      currentTool.value = await toolService.getById(id);
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function createTool(payload: FormData): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await toolService.create(payload);
      tools.value.push(result.tool);
      successMessage.value = result.message;
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function updateTool(
    id: number,
    payload: FormData,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await toolService.update(id, payload);
      const updated = result.tool;
      successMessage.value = result.message;

      const index = tools.value.findIndex((t) => t.id === id);
      if (index !== -1) {
        tools.value[index] = updated;
      }

      if (currentTool.value?.id === id) {
        currentTool.value = updated;
      }

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function deleteTool(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      successMessage.value = await toolService.delete(id);

      tools.value = tools.value.filter((t) => t.id !== id);

      if (currentTool.value?.id === id) {
        currentTool.value = null;
      }

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
    tools.value = [];
    currentTool.value = null;
    loading.value = false;
    error.value = null;
    successMessage.value = null;
    currentPage.value = 1;
    lastPage.value = 1;
    total.value = 0;
    perPage.value = 10;
  }

  return {
    currentPage,
    perPage,
    lastPage,
    total,
    tools,
    currentTool,
    loading,
    error,
    successMessage,
    toolCount,
    hasTools,
    isLoading,
    hasError,
    fetchTools,
    fetchToolById,
    createTool,
    updateTool,
    deleteTool,
    clearError,
    reset,

    
    $reset() {
      tools.value = [];
      currentTool.value = null;
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
