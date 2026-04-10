<script setup lang="ts">
import { onBeforeMount } from "vue";
import { useUserManagement } from "./composable/useUserManagement";


const {
    userStore,
    formVisible,
    detailVisible,
    creditVisible,
    isEditMode,
    form,
    creditForm,
    selectedUser,
    filters,
    dialogTitle,
    submitButtonLabel,
    loadUsers,
    openCreateDialog,
    openEditDialog,
    onPageChange,
    submitForm,
    openDetailDialog,
    openCreditDialog,
    submitCreditForm,
    confirmDelete,
    resetForm,
    clearFilter,
    handleDetailAction,
} = useUserManagement();

onBeforeMount(() => {
    loadUsers();
});
</script>

<template>
    <div class="card">
        <div class="font-semibold text-xl mb-4">Data Users</div>

        <!-- Form Dialog -->
        <UserForm
            v-model:visible="formVisible"
            :loading="userStore.loading"
            :is-edit-mode="isEditMode"
            :form="form"
            :dialog-title="dialogTitle"
            :submit-button-label="submitButtonLabel"
            @submit="submitForm"
            @cancel="resetForm"
        />

        <!-- Detail Dialog -->
        <UserDetail
            v-model:visible="detailVisible"
            :user="selectedUser"
            @edit="(user) => handleDetailAction('edit', user)"
            @update-credit="(user) => handleDetailAction('update-credit', user)"
            @delete="(user) => handleDetailAction('delete', user)"
        />
        <!-- Update Credit Dialog -->
        <UpdateCredit
            v-model:visible="creditVisible"
            :form="creditForm"
            :loading="userStore.loading"
            @submit="submitCreditForm"
        />

        <!-- Table -->
        <UsersTable
            @page-change="onPageChange"
            :users="userStore.users"
            :loading="userStore.loading"
            :current-page="userStore.currentPage"
            :last-page="userStore.lastPage"
            :total="userStore.total"
            :per-page="userStore.perPage"
            :filters="filters"
            @update:filters="
                (newFilters) => {
                    filters = newFilters;
                    loadUsers({ page: 1, per_page: userStore.perPage });
                }
            "
            @create="openCreateDialog"
            @view="openDetailDialog"
            @edit="openEditDialog"
            @delete="confirmDelete"
            @update-credit="openCreditDialog"
            @clear-filter="clearFilter"
        />
    </div>
</template>
