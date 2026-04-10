<script setup lang="ts">
import { onBeforeMount } from "vue";
import { useCategoryManagement } from "./composable/useCategoryrManagement";



const {
    categoryStore,
    formVisible,
    isEditMode,
    form,
    filters,
    dialogTitle,
    submitButtonLabel,
    loadCategories,
    openCreateDialog,
    openEditDialog,
    onPageChange,
    submitForm,
    confirmDelete,
    resetForm,
    clearFilter,
} = useCategoryManagement();

onBeforeMount(() => {
    loadCategories();
});
</script>

<template>
    <div class="card">
        <div class="font-semibold text-xl mb-4">Data Kategori</div>

        <!-- Form Dialog -->
        <CategoryForm
            v-model:visible="formVisible"
            :loading="categoryStore.loading"
            :is-edit-mode="isEditMode"
            :form="form"
            :dialog-title="dialogTitle"
            :submit-button-label="submitButtonLabel"
            @submit="submitForm"
            @cancel="resetForm"
        />

        <!-- Table -->
        <CategoriesTable
            @page-change="onPageChange"
            :categories="categoryStore.categories"
            :loading="categoryStore.loading"
            :current-page="categoryStore.currentPage"
            :last-page="categoryStore.lastPage"
            :total="categoryStore.total"
            :per-page="categoryStore.perPage"
            :filters="filters"
            @update:filters="
                (newFilters) => {
                    filters = newFilters;
                    loadCategories({ page: 1, per_page: categoryStore.perPage });
                }
            "
            @create="openCreateDialog"
            @edit="openEditDialog"
            @delete="confirmDelete"
            @clear-filter="clearFilter"
        />
    </div>
</template>
