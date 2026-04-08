<script setup lang="ts">
import { computed, watch } from 'vue';
import type { User } from '@/types/user';

interface Props {
  users: User[];
  loading?: boolean;
  currentPage?: number;
  lastPage?: number;
  total?: number;
  perPage?: number;
  filters?: {
    role: string;
    search: string;
  };
}

interface Emits {
  (e: 'create'): void;
  (e: 'view', user: User): void;
  (e: 'edit', user: User): void;
  (e: 'delete', user: User): void;
  (e: 'update-credit', user: User): void;
  (e: 'clear-filter'): void;
  (e: 'update:filters', filters: { role: string; search: string }): void;
  (e: 'page-change', event: { page: number; rows: number }): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 10,
  filters: () => ({ role: '', search: '' }),
});

const emit = defineEmits<Emits>();

const roles = ['Admin', 'Employee', 'User'];

const localFilters = computed({
  get: () => props.filters,
  set: (value) => emit('update:filters', value),
});

const handlePageChange = (event: any) => {
  emit('page-change', { page: event.page, rows: event.rows });
};



watch(
  () => ({ total: props.total, perPage: props.perPage, currentPage: props.currentPage, lastPage: props.lastPage }),
  (newVal) => {
  },
);
</script>

<template>
  <div>
      {{ users }}
    <!-- Header with Create Button -->
    <div class="flex justify-between items-center mb-4 gap-2 flex-wrap">
      <div class="flex gap-2 flex-1">
        <InputText
          v-model="localFilters.search"
          placeholder="Cari email, nama, atau NIK..."
          class="flex-1"
        />
        <Dropdown
          v-model="localFilters.role"
          :options="roles"
          placeholder="Semua Role"
          class="w-40"
          show-clear
        />
        <Button
          v-if="localFilters.search || localFilters.role"
          icon="pi pi-times"
          severity="secondary"
          text
          @click="emit('clear-filter')"
        />
      </div>
      <Button
        icon="pi pi-plus"
        label="Tambah User"
        @click="emit('create')"
      />
    </div>

    <!-- Table -->
    <DataTable
      :value="users"
      :loading="loading"
      paginator
      :rows="perPage"
      :total-records="total"
      :rows-per-page-options="[5, 10, 20, 50]"
      :first="(currentPage - 1) * perPage"
      paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
      class="p-datatable-striped"
      @page="handlePageChange"
    >
      <Column field="email" header="Email" style="width: 20%" />
      <Column
        field="details.name"
        header="Nama"
        style="width: 20%"
      /><Column
        field="details.nik"
        header="NIK"
        style="width: 20%"
      />
      <Column field="role" header="Role" style="width: 12%">
        <template #body="{ data }">
          <Tag
            :value="data.role"
            :severity="data.role === 'Admin' ? 'danger' : data.role === 'Employee' ? 'info' : 'success'"
          />
        </template>
      </Column><Column 
        field="credit_score" 
        header="Credit" 
        style="width: 12%"
      >
        <template #body="slotProps">
          {{ slotProps.data.credit_score }} / 100
        </template>
      </Column>

      
      <Column header="Actions" style="width: 24%" body-class="text-center">
        <template #body="{ data }">
          <Button
            icon="pi pi-eye"
            severity="info"
            text
            rounded
            @click="emit('view', data)"
            v-tooltip="'Lihat Detail'"
          />
          <Button
            icon="pi pi-pencil"
            severity="warning"
            text
            rounded
            @click="emit('edit', data)"
            v-tooltip="'Edit'"
          />
          <Button
            icon="pi pi-dollar"
            severity="success"
            text
            rounded
            @click="emit('update-credit', data)"
            v-tooltip="'Update Credit'"
          />
          <Button
            icon="pi pi-trash"
            severity="danger"
            text
            rounded
            @click="emit('delete', data)"
            v-tooltip="'Hapus'"
          />
        </template>
      </Column>
    </DataTable>
  </div>
</template>
