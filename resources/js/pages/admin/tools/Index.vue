<script setup lang="ts">
import { onBeforeMount } from "vue";
import { useToolManagement } from "./composable/useToolManagement";

const {
    toolStore,
    formVisible,
    detailVisible,
    isEditMode,
    form,
    selectedTool,
    filters,
    dialogTitle,
    submitButtonLabel,
    isBundle,
    bundleComponentsCount,
    bundleComponentForm,
    showBundleComponentModal,
    photoPreview,
    loadTools,
    openCreateDialog,
    openEditDialog,
    onPageChange,
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
    handlePhotoSelected,
    removePhoto,
    handleFormUpdate,
} = useToolManagement();

onBeforeMount(() => {
    loadTools();
});
</script>

<template>
    <div class="card">
        <div class="font-semibold text-xl mb-4">Data Tools</div>

        <ToolsTable
            :tools="toolStore.tools"
            :loading="toolStore.loading"
            :current-page="toolStore.currentPage"
            :last-page="toolStore.lastPage"
            :total="toolStore.total"
            :per-page="toolStore.perPage"
            :filters="filters"
            @page-change="onPageChange"
            @update:filters="
                (newFilters) => {
                    filters = newFilters;
                    loadTools({ page: 1, per_page: toolStore.perPage });
                }
            "
            @create="openCreateDialog"
            @view="openDetailDialog"
            @edit="openEditDialog"
            @delete="confirmDelete"
            @clear-filter="clearFilter"
        />

        <ToolForm
            v-model:visible="formVisible"
            :loading="toolStore.loading"
            :is-edit-mode="isEditMode"
            :form="form"
            :dialog-title="dialogTitle"
            :submit-button-label="submitButtonLabel"
            :is-bundle="isBundle"
            :bundle-components-count="bundleComponentsCount"
            :bundle-component-form="bundleComponentForm"
            :show-bundle-component-modal="showBundleComponentModal"
            :photo-preview="photoPreview"
            @update:form="handleFormUpdate"
            @submit="submitForm"
            @cancel="resetForm"
            @item-type-change="handleItemTypeChange"
            @open-bundle-component-modal="openBundleComponentModal"
            @save-bundle-component="saveBundleComponent"
            @remove-bundle-component="removeBundleComponent"
            @photo-selected="handlePhotoSelected"
            @remove-photo="removePhoto"
        />

        <ToolDetail
            v-model:visible="detailVisible"
            :tool="selectedTool"
            @edit="(tool) => handleDetailAction('edit', tool)"
            @delete="(tool) => handleDetailAction('delete', tool)"
        />
    </div>
</template>