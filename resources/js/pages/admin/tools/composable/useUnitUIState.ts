import { ref } from "vue";
import type { ToolUnit } from "@/types/toolunit";

export function useUnitUIState() {
  const showUnitFormModal = ref(false);
  const showDetailModal = ref(false);
  const showRecordConditionModal = ref(false);

  const selectedDetailUnit = ref<ToolUnit | null>(null);
  const selectedConditionUnit = ref<ToolUnit | null>(null);

  const loans = ref([]);
  const loansLoading = ref(false);

  function openCreateModal() {
    showUnitFormModal.value = true;
  }

  function openDetail(unit: ToolUnit) {
    selectedDetailUnit.value = unit;
    showDetailModal.value = true;
  }

  function openRecordCondition(unit: ToolUnit) {
    selectedConditionUnit.value = unit;
    showRecordConditionModal.value = true;
  }

  return {
    showUnitFormModal,
    showDetailModal,
    showRecordConditionModal,
    selectedDetailUnit,
    selectedConditionUnit,
    loans,
    loansLoading,
    openCreateModal,
    openDetail,
    openRecordCondition,
  };
}