




import { defineStore } from 'pinia';
import { ref } from 'vue';
import { reportService } from '@/services/reportService';
import type {
  ReportType,
  ReportFilters,
  ReportItem,
  ReportState,
} from '@/types/report';

export const useReportStore = defineStore('report', () => {
  

  const selectedType = ref<ReportType | null>(null);
  const filters = ref<ReportFilters>({});
  const previewData = ref<ReportItem[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const successMessage = ref<string | null>(null);
  const exporting = ref(false);

  

  
  function selectReportType(type: ReportType) {
    selectedType.value = type;
    previewData.value = [];
    error.value = null;
    successMessage.value = null;
  }

  
  function updateFilters(newFilters: Partial<ReportFilters>) {
    filters.value = {
      ...filters.value,
      ...newFilters,
    };
  }

  
  function clearFilters() {
    filters.value = {};
    error.value = null;
  }

  
  async function fetchPreview(): Promise<boolean> {
    if (!selectedType.value) {
      error.value = 'Pilih tipe laporan terlebih dahulu';
      return false;
    }

    loading.value = true;
    error.value = null;

    try {
      previewData.value = await reportService.getPreview(
        selectedType.value,
        filters.value,
      );
      successMessage.value = `Preview ${selectedType.value} berhasil dimuat`;
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      loading.value = false;
    }
  }

  
  async function exportReport(): Promise<boolean> {
    if (!selectedType.value) {
      error.value = 'Pilih tipe laporan terlebih dahulu';
      return false;
    }

    exporting.value = true;
    error.value = null;

    try {
      const blob = await reportService.exportReport(
        selectedType.value,
        filters.value,
      );

      const timestamp = new Date().toISOString().split('T')[0];
      const filename = `${selectedType.value}-${timestamp}.xlsx`;
      reportService.downloadExcel(blob, filename);

      successMessage.value = `Export ${selectedType.value} berhasil`;
      return true;
    } catch (err: any) {
      error.value = err;
      return false;
    } finally {
      exporting.value = false;
    }
  }

  
  function reset() {
    selectedType.value = null;
    filters.value = {};
    previewData.value = [];
    loading.value = false;
    error.value = null;
    successMessage.value = null;
    exporting.value = false;
  }

  

  return {
    
    selectedType,
    filters,
    previewData,
    loading,
    exporting,
    error,
    successMessage,

    
    selectReportType,
    updateFilters,
    clearFilters,
    fetchPreview,
    exportReport,
    reset,

    
    $reset() {
      selectedType.value = null;
      filters.value = {};
      previewData.value = [];
      loading.value = false;
      error.value = null;
      successMessage.value = null;
      exporting.value = false;
    },
  };
});
