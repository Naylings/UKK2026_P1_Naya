import { computed } from "vue";
import { useToolUnitStore } from "@/stores/toolunit";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import type {
    ToolUnit,
    CreateToolUnitPayload,
    RecordConditionPayload,
} from "@/types/toolunit";

export function useToolUnits(toolIdRef: any) {
    const unitStore = useToolUnitStore();
    const toast = useToast();
    const confirm = useConfirm();

    const units = computed(() => unitStore.toolUnits);
    const loading = computed(() => unitStore.loading);

    const currentPage = computed(() => unitStore.currentPage);
    const lastPage = computed(() => unitStore.lastPage);
    const total = computed(() => unitStore.total);
    const perPage = computed(() => unitStore.perPage);

    async function fetchUnits(page = 1) {
        if (!toolIdRef.value) return;

        const success = await unitStore.fetchUnits({
            tool_id: toolIdRef.value,
            page,
            per_page: perPage.value,
        });

        if (!success) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: unitStore.error,
                life: 3000,
            });
        }
    }

    function createUnit(payload: CreateToolUnitPayload) {
        confirm.require({
            message: "Yakin buat unit?",
            accept: async () => {
                const success = await unitStore.createUnit(payload);
                if (success) {
                    toast.add({ severity: "success", summary: "Berhasil" });
                    await fetchUnits();
                }
            },
        });
    }

    function deleteUnit(unit: ToolUnit) {
        if (unit.has_loans) {
            toast.add({
                severity: "error",
                summary: "Gagal",
                detail: "Unit punya history peminjaman",
            });
            return;
        }

        confirm.require({
            message: `Hapus unit ${unit.code}?`,
            accept: async () => {
                const success = await unitStore.deleteUnit(unit.code);
                if (success) await fetchUnits();
            },
        });
    }
    const conditionHistory = computed(() => unitStore.conditionHistory);
    async function loadConditionHistory(code: string) {
        const success = await unitStore.fetchConditionHistory(code);

        if (!success) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: unitStore.error,
            });
        }
    }
    async function recordCondition(
        code: string,
        payload: RecordConditionPayload,
    ) {
        const success = await unitStore.recordCondition(code, payload);

        if (!success) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: unitStore.error,
            });
        }
    }

    return {
        units,
        loading,
        currentPage,
        lastPage,
        total,
        perPage,
        conditionHistory,
        fetchUnits,
        createUnit,
        deleteUnit,
        recordCondition,
        loadConditionHistory,
    };
}
