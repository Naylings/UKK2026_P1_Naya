<script setup lang="ts">
import { onBeforeMount } from "vue";
import { useLoanList } from "./composables/useLoanList";


const {
    loans,
    loading,
    filters,
    meta,
    loadMyLoans,
    onPageChange,
    clearFilter,
} = useLoanList();

const statusOptions = [
    { label: "Semua Status", value: "" },
    { label: "Pending", value: "pending" },
    { label: "Active", value: "active" },
    { label: "Rejected", value: "rejected" },
    { label: "Closed", value: "closed" },
];



onBeforeMount(() => {
    loadMyLoans();
    console.log("Filters on mount:", filters.value);  
    console.log("Meta on mount:", meta.value);
    console.log("Loans on mount:", loans.value);
});
</script>

<template>
    <UsersLoanTable
        :loans="loans"
        :loading="loading"
        :meta="meta"
        :filters="filters"
        :status-options="statusOptions"
        @update:filters="(val) => {
            filters = val;
            loadMyLoans();
        }"
        @page-change="onPageChange"
        @search="(val) => {
            filters.search = val;
            filters.page = 1;
            loadMyLoans();
        }"
        @reset="clearFilter"
    />
</template>