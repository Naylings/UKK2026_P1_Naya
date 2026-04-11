// ─────────────────────────────────────────────
// pages/admin/Category/composables/useCategoryManagement.ts
// Logic untuk category management page
// ─────────────────────────────────────────────

import { ref, computed } from "vue";
import { useCategoryStore } from "@/stores/category";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import type {
    Category,
    CreateCategoryPayload,
    UpdateCategoryPayload,
} from "@/types/category";

type CategoryForm = Omit<CreateCategoryPayload, "birth_date"> & {
    birth_date: Date | null;
};

export function useCategoryManagement() {
    const categoryStore = useCategoryStore();
    const toast = useToast();
    const confirm = useConfirm();

    // ── Dialog state ──────────────────────────────────────────────────────────

    const formVisible = ref(false);
    const detailVisible = ref(false);
    const creditVisible = ref(false);
    const isEditMode = ref(false);

    // ── Form data ─────────────────────────────────────────────────────────────

    const form = ref<Partial<CategoryForm>>({
        name: null,
        description: null,
    });

    const creditForm = ref({
        credit_score: 0,
    });

    // ── Selected category ─────────────────────────────────────────────────────

    const selectedCategory = ref<Category | null>(null);
    const selectedCategoryId = ref<number | null>(null);

    // ── Filters ───────────────────────────────────────────────────────────────

    const filters = ref({
        search: "",
    });

    // ── Computed ──────────────────────────────────────────────────────────────

    const dialogTitle = computed(() =>
        isEditMode.value ? "Edit Category" : "Tambah Category Baru",
    );

    const submitButtonLabel = computed(() =>
        isEditMode.value ? "Update" : "Buat Category",
    );

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Pagination handler
     */

    async function onPageChange(event: any) {
        const page = event.page;
        const perPage = event.rows ?? categoryStore.perPage;
        await loadCategories({ page, per_page: perPage });
    }

    /**
     * Muat semua categories
     */
    async function loadCategories(params?: {
        page?: number;
        per_page?: number;
    }) {
        const success = await categoryStore.fetchCategories({
            page: params?.page ?? categoryStore.currentPage,
            per_page: params?.per_page ?? categoryStore.perPage,
            search: filters.value.search || undefined,
        });

        if (!success) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: categoryStore.error,
                life: 3000,
            });
        }
    }

    /**
     * Buka dialog create
     */
    function openCreateDialog() {
        isEditMode.value = false;
        resetForm();
        formVisible.value = true;
    }

    /**
     * Buka dialog edit
     */
    function openEditDialog(category: Category) {
        isEditMode.value = true;
        selectedCategory.value = category;

        form.value = {
            name: category.name,
            description: category.description,
        };

        formVisible.value = true;
    }

    /**
     * Submit form (create atau update)
     */
    async function submitForm() {
        if (!form.value) return;

        // Tentukan pesan konfirmasi
        const action = isEditMode.value ? "update" : "buat";
        const categoryName = form.value.name || "";
        const confirmMessage = `Apakah Anda yakin ingin ${action} category "${categoryName}"?`;

        confirm.require({
            message: confirmMessage,
            header: "Konfirmasi",
            icon: "pi pi-exclamation-triangle",
            accept: async () => {
                if (isEditMode.value && selectedCategory.value) {
                    const payload: UpdateCategoryPayload = {
                        id: selectedCategory.value.id,
                        name: form.value.name,
                        description: form.value.description,
                    };

                    const success = await categoryStore.updateCategory(
                        selectedCategory.value.id,
                        payload,
                    );

                    if (success) {
                        toast.add({
                            severity: "success",
                            summary: "Berhasil",
                            detail: categoryStore.successMessage,
                            life: 3000,
                        });
                        formVisible.value = false;
                        resetForm();
                    } else {
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: categoryStore.error,
                            life: 3000,
                        });
                    }
                } else {
                    const payload: CreateCategoryPayload = {
                        name: form.value.name!,
                        description: form.value.description!,
                    };

                    const success = await categoryStore.createCategory(payload);

                    if (success) {
                        toast.add({
                            severity: "success",
                            summary: "Berhasil",
                            detail: categoryStore.successMessage,
                            life: 3000,
                        });
                        formVisible.value = false;
                        resetForm();
                    } else {
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: categoryStore.error,
                            life: 3000,
                        });
                    }
                }
            },
        });
    }

    /**
     * Konfirmasi delete
     */
    function confirmDelete(category: Category) {
        confirm.require({
            message: `Apakah Anda yakin ingin menghapus category "${category.name}"?`,
            header: "Konfirmasi Hapus",
            icon: "pi pi-exclamation-triangle",
            accept: async () => {
                const success = await categoryStore.deleteCategory(category.id);

                if (success) {
                    toast.add({
                        severity: "success",
                        summary: "Berhasil",
                        detail: categoryStore.successMessage,
                        life: 3000,
                    });
                } else {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: categoryStore.error,
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
            description: null,
        };
        selectedCategory.value = null;
    }

    /**
     * Clear filter
     */
    function clearFilter() {
        filters.value = {
            search: "",
        };
        loadCategories({ page: 1, per_page: categoryStore.perPage });
    }

    return {
        onPageChange,
        categoryStore,
        formVisible,
        detailVisible,
        creditVisible,
        isEditMode,
        form,
        creditForm,
        selectedCategory,
        selectedCategoryId,
        filters,
        dialogTitle,
        submitButtonLabel,
        loadCategories,
        openCreateDialog,
        openEditDialog,
        submitForm,
        confirmDelete,
        resetForm,
        clearFilter,
    };
}
