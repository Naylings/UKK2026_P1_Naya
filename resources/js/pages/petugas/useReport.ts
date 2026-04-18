
import { computed, ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useReportStore } from '@/stores/report';
import type { ReportType, ReportFilters } from '@/types/report';

export function useReport() {
  const store = useReportStore();
  const toast = useToast();


  const showFormDialog = ref(false);
  const formData = ref<{
    type: ReportType | null;
    start_date: Date | null;
    end_date: Date | null;
    search?: string;
  }>({
    type: null,
    start_date: null,
    end_date: null,
  });

  function formatDateToYMD(d: Date | null): string | undefined {
    if (!d) return undefined;
    const iso = d.toISOString();
    return iso.split('T')[0];
  }

  const reportTypes: { value: ReportType; label: string }[] = [
    { value: 'loans', label: 'Laporan Peminjaman' },
    { value: 'returns', label: 'Laporan Pengembalian' },
    { value: 'violations', label: 'Laporan Pelanggaran' },
    { value: 'settlements', label: 'Laporan Pelunasan' },
    { value: 'inventory', label: 'Laporan Inventaris' },
    { value: 'conditions', label: 'Laporan Kondisi Unit' },
    { value: 'users', label: 'Laporan Pengguna' },
    { value: 'activity_logs', label: 'Laporan Aktivitas' },
  ];


  const selectedTypeLabel = computed(() => {
    const type = formData.value.type;
    if (!type) return 'Pilih Laporan';
    return reportTypes.find((t) => t.value === type)?.label || type;
  });

  const hasDateFilters = computed(() => {
    return ['loans', 'returns', 'violations', 'settlements'].includes(
      formData.value.type as string,
    );
  });

  const hasPreviewData = computed(() => store.previewData.length > 0);

  const isFormValid = computed(() => {
    if (!formData.value.type) return false;
    if (hasDateFilters.value) {
      return formData.value.start_date && formData.value.end_date;
    }
    return true;
  });

  const columns = computed(() => {
    if (!store.selectedType || !hasPreviewData.value) return [];

    const firstRow = store.previewData[0];
    if (!firstRow) return [];

    return Object.keys(firstRow).map((key) => ({
      field: key,
      header: formatHeaderName(key),
    }));
  });


  function formatHeaderName(key: string): string {
    return key
      .replace(/_/g, ' ')
      .split(' ')
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }

  function openFormDialog() {
    showFormDialog.value = true;
  }

  function closeFormDialog() {
    showFormDialog.value = false;
  }

  async function submitForm() {
    if (!isFormValid.value) return;

    store.clearFilters();

    store.selectReportType(formData.value.type!);

    const filters: ReportFilters = {};
    if (hasDateFilters.value) {
      filters.start_date = formatDateToYMD(formData.value.start_date as Date | null);
      filters.end_date = formatDateToYMD(formData.value.end_date as Date | null);
    }
    if (formData.value.search?.trim()) {
      filters.search = formData.value.search.trim();
    }

    store.updateFilters(filters);
    const success = await store.fetchPreview();

    if (success) {
      toast.add({
        severity: 'success',
        summary: store.successMessage || 'Preview berhasil dimuat',
        life: 3000
      });
    } else {
      toast.add({
        severity: 'error',
        summary: store.error || 'Gagal memuat preview',
        life: 5000
      });
    }

    closeFormDialog();
  }

async function handleExport() {
    const success = await store.exportReport();
    if (success) {
      toast.add({
        severity: 'success',
        summary: store.successMessage || 'Export berhasil',
        life: 3000
      });
    } else {
      toast.add({
        severity: 'error',
        summary: store.error || 'Gagal export',
        life: 5000
      });
    }
  }

  function backToForm() {
    store.reset();
    formData.value = {
      type: null,
      start_date: null,
      end_date: null,
      search: '',
    };
    store.clearFilters();
  }


  return {
    store,
    showFormDialog,
    formData,
    reportTypes,
    selectedTypeLabel,
    hasDateFilters,
    hasPreviewData,
    isFormValid,
    columns,

    formatHeaderName,
    openFormDialog,
    closeFormDialog,
    submitForm,
    handleExport,
    backToForm,
  };
}
