
import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { appConfigService } from "@/services/appConfigService";
import type { AppConfig, UpdateAppConfigPayload } from "@/types/appconfig";

export const useAppConfigStore = defineStore("appconfig", () => {

  const config = ref<AppConfig | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);


  const isLoaded = computed(() => config.value !== null);
  const updatedAt = computed(() => config.value?.updated_at ?? null);


  
  async function fetchConfig(): Promise<boolean> {
    loading.value = true;
    error.value = null;

    try {
      const data = await appConfigService.fetchConfig();
      config.value = data;
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : "Gagal mengambil konfigurasi";
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function updateConfig(payload: UpdateAppConfigPayload): Promise<boolean> {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    const validationErrors = appConfigService.validatePayload(payload);
    if (validationErrors.length > 0) {
      error.value = validationErrors.join(", ");
      loading.value = false;
      return false;
    }

    try {
      const data = await appConfigService.updateConfig(payload);
      config.value = data;
      successMessage.value = "Konfigurasi aplikasi berhasil diperbarui";
      return true;
    } catch (err) {
      error.value = err instanceof Error ? err.message : "Gagal memperbarui konfigurasi";
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  function clearMessages(): void {
    error.value = null;
    successMessage.value = null;
  }

  
  function reset(): void {
    config.value = null;
    loading.value = false;
    error.value = null;
    successMessage.value = null;
  }

  return {
    config,
    loading,
    error,
    successMessage,

    isLoaded,
    updatedAt,

    fetchConfig,
    updateConfig,
    clearMessages,
    reset,

    $reset() {
      config.value = null;
      loading.value = false;
      error.value = null;
      successMessage.value = null;
    },
  };
});
