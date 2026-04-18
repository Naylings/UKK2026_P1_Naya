
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


    const formVisible = ref(false);
    const detailVisible = ref(false);
    const creditVisible = ref(false);
    const isEditMode = ref(false);


    const form = ref<Partial<CategoryForm>>({
        name: null,
        description: null,
    });

    const creditForm = ref({
        credit_score: 0,
    });


    const selectedCategory = ref<Category | null>(null);
    const selectedCategoryId = ref<number | null>(null);


    const filters = ref({
        search: "",
    });


    const dialogTitle = computed(() =>
        isEditMode.value ? "Edit Category" : "Tambah Category Baru",
    );

    const submitButtonLabel = computed(() =>
        isEditMode.value ? "Update" : "Buat Category",
    );


    

    async function onPageChange(event: any) {
        const page = event.page;
        const perPage = event.rows ?? categoryStore.perPage;
        await loadCategories({ page, per_page: perPage });
    }

    
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

    
    function openCreateDialog() {
        isEditMode.value = false;
        resetForm();
        formVisible.value = true;
    }

    
    function openEditDialog(category: Category) {
        isEditMode.value = true;
        selectedCategory.value = category;

        form.value = {
            name: category.name,
            description: category.description,
        };

        formVisible.value = true;
    }

    
    async function submitForm() {
        if (!form.value) return;

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

    
    function resetForm() {
        form.value = {
            name: null,
            description: null,
        };
        selectedCategory.value = null;
    }

    
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
