import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { appealService } from "@/services/appealService";
import type {
  Appeal,
  AppealFilters,
  CreateAppealPayload,
  ReviewAppealPayload,
  AppealListResponse,
} from "@/types/appeal";

export const useAppealStore = defineStore("appeal", () => {
  const appeals = ref<Appeal[]>([]);
  const myAppeals = ref<Appeal[]>([]);
  const currentAppeal = ref<Appeal | null>(null);
  
  const loading = ref(false);
  const creating = ref(false);
  const reviewing = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);

  const meta = ref<AppealListResponse["meta"] | null>(null);
  const currentPage = ref(1);
  const perPage = ref(10);

  const appealCount = computed(() => appeals.value.length);
  const pendingCount = computed(() => appeals.value.filter(a => a.status === 'pending').length);

  async function fetchAll(params?: AppealFilters) {
    loading.value = true;
    error.value = null;

    try {
      const res = await appealService.getAll({ ...params, per_page: perPage.value, page: params?.page || currentPage.value });
      appeals.value = res.data;
      meta.value = res.meta;
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function fetchMy(params?: AppealFilters) {
    loading.value = true;
    error.value = null;

    try {
      const res = await appealService.getMy({ ...params, per_page: perPage.value, page: params?.page || currentPage.value });
      myAppeals.value = res.data;
      meta.value = res.meta;
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      loading.value = false;
    }
  }

  async function create(payload: CreateAppealPayload): Promise<boolean> {
    creating.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const res = await appealService.createAppeal(payload);
      successMessage.value = res.message;

      await fetchMy();
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      creating.value = false;
    }
  }

  async function review(id: number, payload: ReviewAppealPayload): Promise<boolean> {
    reviewing.value = true;
    error.value = null;
    successMessage.value = null;

    try {
      const res = await appealService.reviewAppeal(id, payload);
      successMessage.value = res.message;

      const index = appeals.value.findIndex(a => a.id === id);
      if (index !== -1) {
        appeals.value[index] = res.data;
      }

      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      reviewing.value = false;
    }
  }

  function reset() {
    appeals.value = [];
    myAppeals.value = [];
    currentAppeal.value = null;
    loading.value = false;
    creating.value = false;
    reviewing.value = false;
    error.value = null;
    successMessage.value = null;
    meta.value = null;
    currentPage.value = 1;
  }

  return {
    appeals,
    myAppeals,
    currentAppeal,
    loading,
    creating,
    reviewing,
    error,
    successMessage,
    meta,
    currentPage,
    perPage,
    appealCount,
    pendingCount,

    fetchAll,
    fetchMy,
    create,
    review,

    $reset: reset,
  };
});

