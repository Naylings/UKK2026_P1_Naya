// ─────────────────────────────────────────────
// pages/admin/tools/composables/useToolManagement.ts
// Logic untuk tool management page dengan bundle support
// ─────────────────────────────────────────────

import { ref, computed } from "vue";
import { useToolStore } from "@/stores/tool";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import type {
  Tool,
  CreateToolPayload,
  UpdateToolPayload,
  BundleComponentPayload,
  ItemType,
} from "@/types/tool";

// ─────────────────────────────────────────────
// Form Type Definition
// ─────────────────────────────────────────────

export interface ToolFormData {
  name: string | null;
  category_id: number | null;
  item_type: ItemType | null;
  price: number | null;
  min_credit_score: number | null;
  description: string | null;
  code_slug: string | null;
  photo_path: string | null;
  bundle_components: BundleComponentPayload[] | null;
}

export function useToolManagement() {
  const toolStore = useToolStore();
  const toast = useToast();
  const confirm = useConfirm();

  // ── Dialog state ──────────────────────────────────────────────────────────

  const formVisible = ref(false);
  const detailVisible = ref(false);
  const isEditMode = ref(false);
  const editWarningAcknowledged = ref(false);

  // ── Form data ─────────────────────────────────────────────────────────────

  const form = ref<ToolFormData>({
    name: null,
    category_id: null,
    item_type: null,
    price: null,
    min_credit_score: null,
    description: null,
    code_slug: null,
    photo_path: null,
    bundle_components: null,
  });

  // ── Bundle component form ─────────────────────────────────────────────────

  const bundleComponentForm = ref<BundleComponentPayload>({
    name: "",
    price: 0,
    qty: 1,
    description: null,
    photo_path: null,
    category_id: null,
    min_credit_score: null,
    code_slug: null,
  });

  const showBundleComponentModal = ref(false);
  const editingComponentIndex = ref<number | null>(null);

  // ── Selected tool ─────────────────────────────────────────────────────────

  const selectedTool = ref<Tool | null>(null);

  // ── Filters ───────────────────────────────────────────────────────────────

  const filters = ref({
    category: "",
    search: "",
  });

  // ── Computed ──────────────────────────────────────────────────────────────

  const dialogTitle = computed(() =>
    isEditMode.value ? "Edit Tool" : "Tambah Tool Baru",
  );

  const submitButtonLabel = computed(() =>
    isEditMode.value ? "Update" : "Buat Tool",
  );

  const isBundle = computed(() => form.value.item_type === "bundle");

  const bundleComponentsCount = computed(
    () => form.value.bundle_components?.length ?? 0,
  );

  // ── Actions ───────────────────────────────────────────────────────────────

  /**
   * Pagination handler
   */
  async function onPageChange(event: any) {
    const page = event.page;
    const perPage = event.rows ?? toolStore.perPage;
    await loadTools({ page, per_page: perPage });
  }

  /**
   * Load semua tools
   */
  async function loadTools(params?: { page?: number; per_page?: number }) {
    const success = await toolStore.fetchTools({
      page: params?.page ?? toolStore.currentPage,
      per_page: params?.per_page ?? toolStore.perPage,
      search: filters.value.search || undefined,
      category: filters.value.category || undefined,
    });

    if (!success) {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: toolStore.error,
        life: 3000,
      });
    }
  }

  /**
   * Buka dialog create
   */
  function openCreateDialog() {
    isEditMode.value = false;
    editWarningAcknowledged.value = false;
    resetForm();
    formVisible.value = true;
  }

  /**
   * Buka dialog edit dengan warning jika tool punya units
   */
  function openEditDialog(tool: Tool) {
    selectedTool.value = tool;

    // Jika tool punya units, tampilkan warning terlebih dahulu
    if (tool.has_units) {
      confirm.require({
        message: `⚠️ Alat ini memiliki <strong>${tool.units_count} unit fisik</strong>.
                         Perubahan template hanya berlaku untuk penggunaan ke depan dan tidak akan
                         mempengaruhi unit yang sudah ada. Lanjutkan?`,
        header: "Peringatan Edit Tool dengan Unit",
        icon: "pi pi-exclamation-triangle",
        accept: () => {
          editWarningAcknowledged.value = true;
          proceedEditDialog(tool);
        },
        reject: () => {
          selectedTool.value = null;
        },
      });
    } else {
      proceedEditDialog(tool);
    }
  }

  /**
   * Internal: proceed dengan open edit dialog
   */
  function proceedEditDialog(tool: Tool) {
    isEditMode.value = true;
    selectedTool.value = tool;

    form.value = {
      name: tool.name,
      category_id: tool.category_id,
      item_type: tool.item_type,
      price: tool.price,
      min_credit_score: tool.min_credit_score,
      description: tool.description,
      code_slug: tool.code_slug,
      photo_path: tool.photo_path,
      bundle_components: tool.bundle_components
        ? tool.bundle_components.map((bc) => ({
            name: bc.tool?.name ?? "",
            price: bc.tool?.price ?? 0,
            qty: bc.qty,
            description: bc.tool?.description ?? null,
            photo_path: bc.tool?.photo_path ?? null,
            category_id: bc.tool?.category_id ?? null,
            min_credit_score: bc.tool?.min_credit_score ?? null,
            code_slug: bc.tool?.code_slug ?? null,
          }))
        : null,
    };

    formVisible.value = true;
  }

  /**
   * Handle item_type change
   */
  function handleItemTypeChange(newType: ItemType) {
    form.value.item_type = newType;

    // Jika berubah dari bundle ke non-bundle, clear bundle components
    if (newType !== "bundle") {
      form.value.bundle_components = null;
    } else if (newType === "bundle" && !form.value.bundle_components) {
      form.value.bundle_components = [];
    }
  }

  /**
   * Open modal untuk tambah/edit bundle component
   */
  function openBundleComponentModal(index?: number) {
    if (index !== undefined) {
      editingComponentIndex.value = index;
      bundleComponentForm.value = { ...form.value.bundle_components![index] };
    } else {
      editingComponentIndex.value = null;
      bundleComponentForm.value = {
        name: "",
        price: 0,
        qty: 1,
        description: null,
        photo_path: null,
        category_id: null,
        min_credit_score: null,
        code_slug: null,
      };
    }
    showBundleComponentModal.value = true;
  }

  /**
   * Save bundle component (add atau update)
   */
  function saveBundleComponent() {
    if (!form.value.bundle_components) {
      form.value.bundle_components = [];
    }

    if (editingComponentIndex.value !== null) {
      // Update existing
      form.value.bundle_components[editingComponentIndex.value] = {
        ...bundleComponentForm.value,
      };
    } else {
      // Add new
      form.value.bundle_components.push({
        ...bundleComponentForm.value,
      });
    }

    showBundleComponentModal.value = false;
    editingComponentIndex.value = null;
  }

  /**
   * Remove bundle component
   */
  function removeBundleComponent(index: number) {
    if (form.value.bundle_components) {
      form.value.bundle_components.splice(index, 1);
    }
  }

  /**
   * Submit form (create atau update)
   */
  async function submitForm() {
    if (!form.value) return;

    // Validasi basic
    if (!form.value.name || !form.value.category_id || !form.value.item_type) {
      toast.add({
        severity: "warn",
        summary: "Validasi",
        detail: "Nama, kategori, dan tipe item wajib diisi.",
        life: 3000,
      });
      return;
    }

    // Validasi bundle components jika item_type = bundle
    if (form.value.item_type === "bundle") {
      if (
        !form.value.bundle_components ||
        form.value.bundle_components.length === 0
      ) {
        toast.add({
          severity: "warn",
          summary: "Validasi",
          detail: "Bundle harus memiliki minimal satu komponen.",
          life: 3000,
        });
        return;
      }
    }

    const action = isEditMode.value ? "update" : "buat";
    const toolName = form.value.name || "";
    const confirmMessage = `Apakah Anda yakin ingin ${action} tool "${toolName}"?`;

    // Tambah warning jika edit tool yang punya units
    const additionalMessage =
      isEditMode.value && selectedTool.value?.has_units
        ? "\n\n⚠️ Alat ini memiliki unit fisik. Perubahan tidak akan mempengaruhi unit yang sudah ada."
        : "";

    confirm.require({
      message: confirmMessage + additionalMessage,
      header: "Konfirmasi",
      icon: "pi pi-exclamation-triangle",
      accept: async () => {
        if (isEditMode.value && selectedTool.value) {
          const payload: UpdateToolPayload = {
            id: selectedTool.value.id,
            category_id: form.value.category_id,
            name: form.value.name,
            item_type: form.value.item_type,
            price: form.value.price,
            min_credit_score: form.value.min_credit_score,
            description: form.value.description,
            code_slug: form.value.code_slug,
            photo_path: form.value.photo_path,
            bundle_components: form.value.bundle_components,
          };

          const success = await toolStore.updateTool(
            selectedTool.value.id,
            payload,
          );

          if (success) {
            toast.add({
              severity: "success",
              summary: "Berhasil",
              detail: toolStore.successMessage,
              life: 3000,
            });
            formVisible.value = false;
            resetForm();
          } else {
            toast.add({
              severity: "error",
              summary: "Error",
              detail: toolStore.error,
              life: 3000,
            });
          }
        } else {
          const payload: CreateToolPayload = {
            name: form.value.name!,
            category_id: form.value.category_id!,
            item_type: form.value.item_type!,
            price: form.value.price!,
            min_credit_score: form.value.min_credit_score!,
            description: form.value.description!,
            code_slug: form.value.code_slug!,
            photo_path: form.value.photo_path!,
            bundle_components: form.value.bundle_components,
          };

          const success = await toolStore.createTool(payload);

          if (success) {
            toast.add({
              severity: "success",
              summary: "Berhasil",
              detail: toolStore.successMessage,
              life: 3000,
            });
            formVisible.value = false;
            resetForm();
          } else {
            toast.add({
              severity: "error",
              summary: "Error",
              detail: toolStore.error,
              life: 3000,
            });
          }
        }
      },
    });
  }

  /**
   * Buka dialog detail tool
   */
  function openDetailDialog(tool: Tool) {
    selectedTool.value = tool;
    detailVisible.value = true;
  }

  /**
   * Handle detail dialog action
   */
  function handleDetailAction(action: "edit" | "delete", tool: Tool) {
    detailVisible.value = false;
    if (action === "edit") openEditDialog(tool);
    if (action === "delete") confirmDelete(tool);
  }

  /**
   * Konfirmasi delete
   */
  function confirmDelete(tool: Tool) {
    // Jika tool tidak bisa dihapus, tampilkan error message
    if (!tool.can_delete) {
      let reason = "";
      if (tool.has_units) reason = "masih memiliki unit fisik";
      if (tool.has_loans)
        reason = reason
          ? reason + " dan peminjaman"
          : "masih memiliki peminjaman";
      if (tool.has_bundles)
        reason = reason
          ? reason + " dan bundle"
          : "masih menjadi bagian dari bundle";

      toast.add({
        severity: "warn",
        summary: "Tidak Dapat Dihapus",
        detail: `Tool "${tool.name}" ${reason}.`,
        life: 4000,
      });
      return;
    }

    confirm.require({
      message: `Apakah Anda yakin ingin menghapus tool "${tool.name}"?`,
      header: "Konfirmasi Hapus",
      icon: "pi pi-exclamation-triangle",
      accept: async () => {
        const success = await toolStore.deleteTool(tool.id);

        if (success) {
          toast.add({
            severity: "success",
            summary: "Berhasil",
            detail: toolStore.successMessage,
            life: 3000,
          });
        } else {
          toast.add({
            severity: "error",
            summary: "Error",
            detail: toolStore.error,
            life: 3000,
          });
        }
      },
    });
  }

  /**
   * Reset form
   */
  function resetForm() {
    form.value = {
      name: null,
      category_id: null,
      item_type: null,
      price: null,
      min_credit_score: null,
      description: null,
      code_slug: null,
      photo_path: null,
      bundle_components: null,
    };
    selectedTool.value = null;
    editWarningAcknowledged.value = false;
    bundleComponentForm.value = {
      name: "",
      price: 0,
      qty: 1,
      description: null,
      photo_path: null,
      category_id: null,
      min_credit_score: null,
      code_slug: null,
    };
    editingComponentIndex.value = null;
  }

  /**
   * Clear filter
   */
  function clearFilter() {
    filters.value = {
      category: "",
      search: "",
    };
    loadTools({ page: 1, per_page: toolStore.perPage });
  }

  return {
    // State
    formVisible,
    detailVisible,
    isEditMode,
    form,
    selectedTool,
    filters,
    bundleComponentForm,
    showBundleComponentModal,
    editingComponentIndex,

    // Computed
    dialogTitle,
    submitButtonLabel,
    isBundle,
    bundleComponentsCount,

    // Store reference
    toolStore,

    // Actions
    onPageChange,
    loadTools,
    openCreateDialog,
    openEditDialog,
    submitForm,
    openDetailDialog,
    confirmDelete,
    resetForm,
    clearFilter,
    handleDetailAction,
    handleItemTypeChange,
    openBundleComponentModal,
    saveBundleComponent,
    removeBundleComponent,
  };
}
