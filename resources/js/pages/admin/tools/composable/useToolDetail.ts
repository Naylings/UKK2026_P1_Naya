import { computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToolStore } from "@/stores/tool";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";

export function useToolDetail() {
  const route = useRoute();
  const router = useRouter();
  const toolStore = useToolStore();
  const toast = useToast();
  const confirm = useConfirm();

  const tool = computed(() => toolStore.currentTool);
  const loading = computed(() => toolStore.loading);
  const isBundle = computed(() => tool.value?.item_type === "bundle");
  const canDelete = computed(() => tool.value?.can_delete ?? false);

  async function init(fetchUnits?: () => Promise<void>) {
    const toolId = Number(route.params.id);

    if (!toolId || isNaN(toolId)) {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: "ID tool tidak valid.",
        life: 3000,
      });
      router.push({ name: "tool management" });
      return;
    }

    const success = await toolStore.fetchToolById(toolId);

    if (!success) {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: toolStore.error || "Tool tidak ditemukan.",
        life: 3000,
      });
      setTimeout(() => router.push({ name: "tool management" }), 1500);
      return;
    }

    await fetchUnits();
  }

  function confirmDelete() {
    if (!tool.value) return;

    if (!canDelete.value) {
      const reasons: string[] = [];
      if (tool.value.has_units) reasons.push("masih memiliki unit fisik");
      if (tool.value.has_loans) reasons.push("masih memiliki peminjaman");
      if (tool.value.has_bundles)
        reasons.push("masih menjadi bagian dari bundle");

      toast.add({
        severity: "warn",
        summary: "Tidak Dapat Dihapus",
        detail: `Tool "${tool.value.name}" ${reasons.join(", ")}.`,
        life: 4000,
      });
      return;
    }

    confirm.require({
      message: `Apakah Anda yakin ingin menghapus tool "${tool.value.name}"?`,
      header: "Konfirmasi Hapus",
      accept: async () => {
        const success = await toolStore.deleteTool(tool.value!.id);

        if (success) {
          toast.add({
            severity: "success",
            summary: "Berhasil",
            detail: toolStore.successMessage,
            life: 2000,
          });
          setTimeout(() => router.push({ name: "tool management" }), 2000);
        }
      },
    });
  }

  function goBack() {
    router.push({ name: "tool management" });
  }

  return {
    tool,
    loading,
    isBundle,
    canDelete,
    init,
    confirmDelete,
    goBack,
  };
}