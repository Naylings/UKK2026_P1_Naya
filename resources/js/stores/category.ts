




import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { categoryService } from "@/services/categoryService";
import type {
  Category,
  CreateCategoryPayload,
  UpdateCategoryPayload,
} from "@/types/category";

export const useCategoryStore = defineStore("category", () => {
  

  const categories = ref<Category[]>([]);
  const currentCategory = ref<Category | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  

  const currentPage = ref(1);
  const lastPage = ref(1);
  const total = ref(0);
  const perPage = ref(10);

  

  const categoryCount = computed(() => categories.value.length);
  const hasCategories = computed(() => categories.value.length > 0);

  

  
  async function fetchCategories(params?: {
    search?: string;
    per_page?: number;
    page?: number;
  }): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const res = await categoryService.getAll(params);

      categories.value = res.data;

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

  
  
  async function createCategory(payload: CreateCategoryPayload): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await categoryService.create(payload);
      categories.value.push(result.category);
      successMessage.value = result.message;
      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function updateCategory(
    id: number,
    payload: UpdateCategoryPayload,
  ): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const result = await categoryService.update(id, payload);
      const updated = result.category;
      successMessage.value = result.message;

      
      const index = categories.value.findIndex((c) => c.id === id);
      if (index !== -1) {
        categories.value[index] = updated;
      }

      
      if (currentCategory.value?.id === id) {
        currentCategory.value = updated;
      }

      return true;
    } catch (err) {
      error.value = err as string;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function deleteCategory(id: number): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      successMessage.value = await categoryService.delete(id);

      
      categories.value = categories.value.filter((c) => c.id !== id);

      
      if (currentCategory.value?.id === id) {
        currentCategory.value = null;
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
    categories.value = [];
    currentCategory.value = null;
    loading.value = false;
    error.value = null;
  }

  return {
    currentPage,
    perPage,
    lastPage,
    total,
    
    categories,
    currentCategory,
    loading,
    error,
    successMessage,
    
    categoryCount,
    hasCategories,
    
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    clearError,
    reset,

    
    $reset() {
      categories.value = [];
      currentCategory.value = null;
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
