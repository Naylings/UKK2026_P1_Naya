<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import type { User } from '@/types/user';

const op = ref();
const selectedRow = ref<User | null>(null);

const toggleMenu = (event: Event, user: User) => {
  selectedRow.value = user;
  op.value.toggle(event);
};

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
  emit('page-change', { page: event.page + 1, rows: event.rows });
};



let timeout: any;

watch(
  () => localFilters.value,
  (val) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      emit('update:filters', val);
    }, 500); // debounce 500ms
  },
  { deep: true }
);
</script>

<template>
  <div>
    <!-- Header with Create Button -->
    <div class="flex justify-between items-center mb-4 gap-2 flex-wrap">
      <div class="flex gap-2 flex-1">
        <InputText v-model="localFilters.search" placeholder="Cari email, nama, atau NIK..." class="flex-1" />
        <Dropdown v-model="localFilters.role" :options="roles" placeholder="Semua Role" class="w-40" show-clear />
        <Button v-if="localFilters.search || localFilters.role" icon="pi pi-times" severity="secondary" text
          @click="emit('clear-filter')" />
      </div>
      <Button icon="pi pi-plus" label="Tambah User" @click="emit('create')" />
    </div>

    <!-- Table -->
    <DataTable :value="users" :loading="loading" paginator :rows="perPage" :total-records="total"
      :rows-per-page-options="[5, 10, 20, 50]" :first="(currentPage - 1) * perPage"
      paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
      class="p-datatable-striped" @page="handlePageChange">
      <Column field="email" header="Email" style="width: 20%" />
      <Column field="details.name" header="Nama" style="width: 20%" />
      <Column field="details.nik" header="NIK" style="width: 20%" />
      <Column field="role" header="Role" style="width: 12%">
        <template #body="{ data }">
          <Tag :value="data.role"
            :severity="data.role === 'Admin' ? 'danger' : data.role === 'Employee' ? 'info' : 'success'" />
        </template>
      </Column>

      <Column field="credit_score" header="Credit" style="width: 12%">
        <template #body="{ data }">
          <span v-if="data.role === 'User'">
            {{ data.credit_score }} / 100
          </span>
          <span v-else class="text-gray-400 italic">
            -
          </span>
        </template>
      </Column>


      <Column header="Actions" style="width: 10%" body-class="text-center">
        <template #body="{ data }">
          <Button icon="pi pi-ellipsis-v" text rounded @click="toggleMenu($event, data)" />
        </template>
      </Column>
    </DataTable>
    <OverlayPanel ref="op">
      <div class="flex flex-col gap-2 min-w-45">

        <Button label="Lihat Detail" icon="pi pi-eye" text severity="secondary" class="justify-start"
          @click="selectedRow && emit('view', selectedRow); op.hide()" />

        <Button label="Edit" icon="pi pi-pencil" text severity="warning" class="justify-start"
          @click="selectedRow && emit('edit', selectedRow); op.hide()" />

        <Button v-if="selectedRow?.role === 'User'" label="Update Credit" icon="pi pi-dollar" text severity="success"
          class="justify-start" @click="selectedRow && emit('update-credit', selectedRow); op.hide()" />

        <Divider />

        <Button label="Hapus" icon="pi pi-trash" text severity="danger" class="justify-start"
          @click="selectedRow && emit('delete', selectedRow); op.hide()" />

      </div>
    </OverlayPanel>
  </div>
</template>
