<script setup lang="ts">
import { ref, computed, watch } from "vue";
import type { User } from "@/types/user";

interface Props {
  users: User[];
  loading?: boolean;
  currentPage?: number;
  lastPage?: number;
  total?: number;
  perPage?: number;
  filters?: { role: string; search: string };
}

interface Emits {
  (e: "create"): void;
  (e: "view", user: User): void;
  (e: "edit", user: User): void;
  (e: "delete", user: User): void;
  (e: "update-credit", user: User): void;
  (e: "clear-filter"): void;
  (e: "update:filters", filters: { role: string; search: string }): void;
  (e: "page-change", event: { page: number; rows: number }): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 10,
  filters: () => ({ role: "", search: "" }),
});

const emit = defineEmits<Emits>();

const selectedRow = ref<User | null>(null);
const op = ref();
const localFilters = computed({
  get: () => props.filters,
  set: (val) => emit("update:filters", val),
});
const localCurrentPage = ref(props.currentPage);
const roles = ["Admin", "Employee", "User"];
let timeout: any = null;

watch(
  () => props.currentPage,
  (val) => (localCurrentPage.value = val),
);

watch(
  () => localFilters.value,
  (val) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => emit("update:filters", val), 500);
  },
  { deep: true },
);

const toggleMenu = (event: Event, user: User) => {
  selectedRow.value = user;
  op.value.toggle(event);
};

const handlePageChange = (event: any) => {
  emit("page-change", { page: event.page + 1, rows: event.rows });
};
</script>

<template>
  <div class="card">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
      <div class="flex flex-1 gap-2 min-w-75">
        <InputText
          v-model="localFilters.search"
          placeholder="Cari email, nama, atau NIK..."
          class="flex-1"
          clearable
        />
        <Select
          v-model="localFilters.role"
          :options="roles"
          placeholder="Semua Role"
          class="w-40"
          showclear
        />
        <Button
          v-if="localFilters.search || localFilters.role"
          icon="pi pi-times"
          text
          severity="secondary"
          class="self-center"
          @click="emit('clear-filter')"
          title="Hapus filter"
        />
      </div>

      <Button
        icon="pi pi-plus"
        label="Tambah User"
        class="mt-2 sm:mt-0"
        @click="emit('create')"
      />
    </div>

    <DataTable
      lazy
      :value="users"
      :loading="props.loading"
      paginator
      :first="(props.currentPage - 1) * props.perPage"
      :rows="props.perPage"
      :rowsPerPageOptions="[5, 10, 20, 50]"
      :totalRecords="props.total"
      tableStyle="min-width: 50rem"
      @page="handlePageChange"
      @update:rows="
        emit('page-change', { page: props.currentPage, rows: $event })
      "
    >
      <Column field="email" header="Email" style="width: 20%" />
      <Column field="details.name" header="Nama" style="width: 20%" />
      <Column field="details.nik" header="NIK" style="width: 20%" />
      <Column field="role" header="Role" style="width: 12%">
        <template #body="{ data }">
          <Tag
            :value="data.role"
            :severity="
              data.role === 'Admin'
                ? 'danger'
                : data.role === 'Employee'
                  ? 'info'
                  : 'success'
            "
          />
        </template>
      </Column>
      <Column field="credit_score" header="Credit" style="width: 12%">
        <template #body="{ data }">
          <span v-if="data.role === 'User'">{{ data.credit_score }} / 100</span>
          <span v-else class="text-gray-400 italic"> - </span>
        </template>
      </Column>
      <Column header="Actions" style="width: 10%" body-class="text-center">
        <template #body="{ data }">
          <Button
            icon="pi pi-ellipsis-v"
            text
            rounded
            @click="toggleMenu($event, data)"
          />
        </template>
      </Column>
    </DataTable>

    <Popover ref="op" append-to="body">
      <div class="flex flex-col gap-2 min-w-48">
        <Button
          label="Lihat Detail"
          icon="pi pi-eye"
          text
          class="justify-start"
          @click="selectedRow && (emit('view', selectedRow), op.hide())"
        />
        <Button
          label="Edit"
          icon="pi pi-pencil"
          text
          severity="warning"
          class="justify-start"
          @click="selectedRow && (emit('edit', selectedRow), op.hide())"
        />
        <Button
          v-if="selectedRow?.role === 'User'"
          label="Update Credit"
          icon="pi pi-dollar"
          text
          severity="success"
          class="justify-start"
          @click="
            selectedRow && (emit('update-credit', selectedRow), op.hide())
          "
        />
        <Divider />
        <Button
          label="Hapus"
          icon="pi pi-trash"
          text
          severity="danger"
          class="justify-start"
          @click="selectedRow && (emit('delete', selectedRow), op.hide())"
        />
      </div>
    </Popover>
  </div>
</template>
