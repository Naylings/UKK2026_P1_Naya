<script setup lang="ts">
import type { BundleComponentPayload } from "@/types/tool";

interface Props {
  items: BundleComponentPayload[];
  loading?: boolean;
}

interface Emits {
  (e: "add"): void;
  (e: "edit", index: number): void;
  (e: "remove", index: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();
</script>

<template>
<div class="border rounded-xl p-3">
  <div class="flex justify-between items-center mb-3">
    <div>
      <p class="text-sm font-medium">Komponen Bundle</p>
      <p class="text-xs text-surface-400">
        {{ props.items.length }} item
      </p>
    </div>

    <Button
      label="Tambah"
      icon="pi pi-plus"
      size="small"
      @click="emit('add')"
    />
  </div>

  <DataTable
    :value="props.items"
    class="text-sm"
    stripedRows
  >
    <Column field="name" header="Nama" />

    <Column header="Qty" style="width: 5rem" body-class="text-center">
      <template #body="{ data }">
        <Tag :value="String(data.qty)" severity="secondary" />
      </template>
    </Column>

    <Column header="Harga">
      <template #body="{ data }">
        <span class="font-medium">
          {{
            new Intl.NumberFormat("id-ID", {
              style: "currency",
              currency: "IDR",
              minimumFractionDigits: 0,
            }).format(data.price)
          }}
        </span>
      </template>
    </Column>

    <Column header="" style="width: 6rem" body-class="text-center">
      <template #body="{ index }">
        <div class="flex justify-center gap-1">
          <Button
            icon="pi pi-pencil"
            text
            size="small"
            @click="emit('edit', index)"
          />
          <Button
            icon="pi pi-trash"
            text
            severity="danger"
            size="small"
            @click="emit('remove', index)"
          />
        </div>
      </template>
    </Column>

    <template #empty>
      <div class="text-center py-6 text-surface-400">
        <i class="pi pi-box text-2xl mb-2 block" />
        <span class="text-sm">Belum ada komponen</span>
      </div>
    </template>
  </DataTable>
</div>
</template>