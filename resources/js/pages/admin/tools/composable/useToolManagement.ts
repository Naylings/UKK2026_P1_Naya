
import { ref, computed } from "vue";
import { useToolStore } from "@/stores/tool";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import type { Tool, BundleComponentPayload, ItemType } from "@/types/tool";

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


  const formVisible = ref(false);
  const isEditMode = ref(false);
  const editWarningAcknowledged = ref(false);


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

  function handleFormUpdate(newForm: ToolFormData) {
    form.value = newForm;
  }


  const photoFile = ref<File | null>(null);
  const photoPreview = ref<string | null>(null);

  function handlePhotoSelected(file: File | null) {
    photoFile.value = file;
    photoPreview.value = file ? URL.createObjectURL(file) : null;
  }

  function removePhoto() {
    photoFile.value = null;
    photoPreview.value = null;
    form.value.photo_path = null;
  }


  const bundleComponentForm = ref<BundleComponentPayload>({
    name: "",
    price: 0,
    qty: 1,
    description: null,
    photo_path: null,
  });

  const showBundleComponentModal = ref(false);
  const editingComponentIndex = ref<number | null>(null);


  const selectedTool = ref<Tool | null>(null);


  const filters = ref({
    category: "",
    search: "",
  });


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


  async function onPageChange(event: any) {
    const page = event.page;
    const perPage = event.rows ?? toolStore.perPage;
    await loadTools({ page, per_page: perPage });
  }

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

  function openCreateDialog() {
    isEditMode.value = false;
    editWarningAcknowledged.value = false;
    resetForm();
    formVisible.value = true;
  }

  function openEditDialog(tool: Tool) {
    selectedTool.value = tool;

    if (tool.has_units) {
      confirm.require({
        message: `Alat ini memiliki ${tool.units_count} unit fisik. Perubahan hanya berlaku untuk penggunaan ke depan. Lanjutkan?`,
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

  function proceedEditDialog(tool: Tool) {
    isEditMode.value = true;
    selectedTool.value = tool;

    photoFile.value = null;
    photoPreview.value = tool.photo_path ?? null;

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
          }))
        : null,
    };

    formVisible.value = true;
  }

  function handleItemTypeChange(newType: ItemType) {
    form.value.item_type = newType;
    if (newType !== "bundle") {
      form.value.bundle_components = null;
    } else if (!form.value.bundle_components) {
      form.value.bundle_components = [];
    }
  }

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
      };
    }
    showBundleComponentModal.value = true;
  }

  function saveBundleComponent() {
    if (!form.value.bundle_components) {
      form.value.bundle_components = [];
    }
    if (editingComponentIndex.value !== null) {
      form.value.bundle_components[editingComponentIndex.value] = {
        ...bundleComponentForm.value,
      };
    } else {
      form.value.bundle_components.push({ ...bundleComponentForm.value });
    }
    showBundleComponentModal.value = false;
    editingComponentIndex.value = null;
  }

  function removeBundleComponent(index: number) {
    form.value.bundle_components?.splice(index, 1);
  }

  function buildFormData(isUpdate = false): FormData {
    const fd = new FormData();
    fd.append("name", form.value.name!);
    fd.append("category_id", String(form.value.category_id!));
    fd.append("item_type", form.value.item_type!);
    fd.append("price", String(form.value.price!));
    fd.append("min_credit_score", String(form.value.min_credit_score ?? 0));
    fd.append("code_slug", form.value.code_slug!);
    if (form.value.description)
      fd.append("description", form.value.description);
    if (photoFile.value) fd.append("photo", photoFile.value);
    if (isUpdate && !photoFile.value && form.value.photo_path) {
      fd.append("photo_path", form.value.photo_path);
    }
    if (form.value.bundle_components?.length) {
      fd.append(
        "bundle_components",
        JSON.stringify(form.value.bundle_components),
      );
    }
    return fd;
  }

  async function submitForm() {
    if (!form.value) return;

    if (!form.value.name || !form.value.category_id || !form.value.item_type) {
      toast.add({
        severity: "warn",
        summary: "Validasi",
        detail: "Nama, kategori, dan tipe item wajib diisi.",
        life: 3000,
      });
      return;
    }

    if (
      form.value.item_type === "bundle" &&
      (!form.value.bundle_components ||
        form.value.bundle_components.length === 0)
    ) {
      toast.add({
        severity: "warn",
        summary: "Validasi",
        detail: "Bundle harus memiliki minimal satu komponen.",
        life: 3000,
      });
      return;
    }

    if (!form.value.price || form.value.price <= 0) {
      toast.add({
        severity: "warn",
        summary: "Validasi",
        detail: "Harga harus diisi dan lebih dari 0.",
        life: 3000,
      });
      return;
    }

    const action = isEditMode.value ? "update" : "buat";
    const additionalNote =
      isEditMode.value && selectedTool.value?.has_units
        ? "\n\nAlat ini memiliki unit fisik. Perubahan tidak akan mempengaruhi unit yang sudah ada."
        : "";

    confirm.require({
      message: `Apakah Anda yakin ingin ${action} tool "${form.value.name}"?${additionalNote}`,
      header: "Konfirmasi",
      icon: "pi pi-exclamation-triangle",
      accept: async () => {
        let success: boolean;

        if (isEditMode.value && selectedTool.value) {
          const payload = buildFormData(true);
          success = await toolStore.updateTool(selectedTool.value.id, payload);
        } else {
          success = await toolStore.createTool(buildFormData());
        }

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
      },
    });
  }

  function confirmDelete(tool: Tool) {
    if (!tool.can_delete) {
      const reasons: string[] = [];
      if (tool.has_units) reasons.push("masih memiliki unit fisik");
      if (tool.has_loans) reasons.push("masih memiliki peminjaman");
      if (tool.has_bundles) reasons.push("masih menjadi bagian dari bundle");
      toast.add({
        severity: "warn",
        summary: "Tidak Dapat Dihapus",
        detail: `Tool "${tool.name}" ${reasons.join(", ")}.`,
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
    photoFile.value = null;
    photoPreview.value = null;
    selectedTool.value = null;
    editWarningAcknowledged.value = false;
    bundleComponentForm.value = {
      name: "",
      price: 0,
      qty: 1,
      description: null,
      photo_path: null,
    };
    editingComponentIndex.value = null;
  }

  function clearFilter() {
    filters.value = { category: "", search: "" };
    loadTools({ page: 1, per_page: toolStore.perPage });
  }

  return {
    formVisible,
    isEditMode,
    form,
    selectedTool,
    filters,
    bundleComponentForm,
    showBundleComponentModal,
    editingComponentIndex,
    photoFile,
    photoPreview,
    dialogTitle,
    submitButtonLabel,
    isBundle,
    bundleComponentsCount,
    toolStore,
    onPageChange,
    loadTools,
    openCreateDialog,
    openEditDialog,
    submitForm,
    confirmDelete,
    resetForm,
    clearFilter,
    handleItemTypeChange,
    openBundleComponentModal,
    saveBundleComponent,
    removeBundleComponent,
    handlePhotoSelected,
    removePhoto,
    handleFormUpdate,
  };
}
