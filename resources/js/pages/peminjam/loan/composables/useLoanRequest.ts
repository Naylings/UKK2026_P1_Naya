import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import { useRouter } from "vue-router";
import { loanRequestService } from "@/services/loanService";
import { toolService } from "@/services/toolService";
import type { AvailableToolUnit } from "@/types/loan";

export function useLoanRequest() {

  const router = useRouter();
  const toolId = ref<number | null>(null);

  const loanDate = ref<Date | null>(new Date());
  const dueDate = ref<Date | null>(null);

  const reason = ref<string>("");

  const availableUnits = ref<AvailableToolUnit[]>([]);
  const selectedUnit = ref<AvailableToolUnit | null>(null);

  const loading = ref(false);
  const searching = ref(false);
  const error = ref<string | null>(null);

  const tool = ref<any>(null);

  const toast = useToast();


  function formatDate(date: Date | null): string {
    if (!date) return "";
    return date.toISOString().split("T")[0];
  }

  function showToast(
    severity: "success" | "error" | "warn",
    summary: string,
    detail: string,
  ) {
    toast.add({
      severity,
      summary,
      detail,
      life: 3000,
    });
  }


  function setToolId(id: number) {
    toolId.value = id;
  }

  async function searchAvailableUnits() {
    if (!toolId.value || !dueDate.value) return;

    searching.value = true;
    error.value = null;
    selectedUnit.value = null;

    try {
      const res = await loanRequestService.getAvailableUnits({
        tool_id: toolId.value,
        loan_date: formatDate(loanDate.value),
        due_date: formatDate(dueDate.value),
      });

      availableUnits.value = res;

      if (availableUnits.value.length === 0) {
        showToast(
          "warn",
          "Unit Tidak Ditemukan",
          "Tidak ada unit yang tersedia untuk periode peminjaman yang dipilih.",
        );
      }
    } catch (err: any) {
      error.value = err;
      showToast("error", "Error", err);
    } finally {
      searching.value = false;
    }
  }

  function selectUnit(unit: AvailableToolUnit) {
    selectedUnit.value = unit;
  }

  function clearSearch() {
    availableUnits.value = [];
    selectedUnit.value = null;
  }

  async function submitLoan() {
    if (loading.value || !toolId.value || !selectedUnit.value || !dueDate.value)
      return;

    loading.value = true;
    error.value = null;

    try {
      await loanRequestService.submitLoan({
        tool_id: toolId.value,
        unit_code: selectedUnit.value.code,
        loan_date: formatDate(loanDate.value),
        due_date: formatDate(dueDate.value),
        purpose: reason.value,
      });

      showToast(
        "success",
        "Berhasil",
        `Peminjaman ${selectedUnit.value.code} berhasil diajukan`,
      );

      clearSearch();
      reason.value = "";
      dueDate.value = null;
      loanDate.value = new Date();
      selectedUnit.value = null;

      await router.push({ name: "peminjam-loans" });
    } catch (err: any) {
      error.value = err;
      showToast("error", "Gagal", err);
    } finally {
      loading.value = false;
    }
  }

  async function loadTool(id: number) {
    try {
      tool.value = await toolService.getById(id);
    } catch {
      error.value = "Gagal memuat data alat";
      showToast("error", "Error", "Gagal memuat data alat");
    }
  }


  return {
    toolId,
    loanDate,
    dueDate,
    reason,
    availableUnits,
    selectedUnit,
    loading,
    searching,
    error,
    tool,

    setToolId,
    searchAvailableUnits,
    selectUnit,
    clearSearch,
    submitLoan,
    loadTool,
  };
}
