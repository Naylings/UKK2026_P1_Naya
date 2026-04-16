<template>
  <Dialog v-model:visible="modelShowDetail" modal header="Informasi Pelanggaran" class="w-[750px]">
    <div v-if="selected" class="space-y-6">
      <div class="grid grid-cols-3 gap-4">
        <div class="p-4 bg-red-50 border border-red-100 rounded-xl text-center">
          <p class="text-xs text-red-500 uppercase font-bold mb-1">Total Denda</p>
          <p class="text-xl font-black text-red-700">Rp {{ selected.fine?.toLocaleString() }}</p>
        </div>
        <div class="p-4 bg-gray-50 border rounded-xl text-center">
          <p class="text-xs text-gray-500 uppercase font-bold mb-1">Skor Penalti</p>
          <p class="text-xl font-black text-gray-700">{{ selected.total_score }}</p>
        </div>
        <div class="p-4 bg-gray-50 border rounded-xl text-center">
          <p class="text-xs text-gray-500 uppercase font-bold mb-1">Status</p>
          <Tag :value="selected.status" :severity="selected.status === 'settled' ? 'success' : 'warn'" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="p-4 border rounded-lg">
          <p class="text-xs text-gray-400 mb-2 font-bold uppercase tracking-wider">Peminjam</p>
          <div class="space-y-1 text-sm">
            <p><span class="text-gray-500">Nama:</span> {{ selected.user?.details?.name }}</p>
            <p><span class="text-gray-500">Unit:</span> {{ selected.loan?.unit?.code }}</p>
          </div>
        </div>
        <div class="p-4 border rounded-lg">
          <p class="text-xs text-gray-400 mb-2 font-bold uppercase tracking-wider">Alat</p>
          <div class="space-y-1 text-sm">
            <p><span class="text-gray-500">Nama:</span> {{ selected.loan?.tool?.name }}</p>
            <p><span class="text-gray-500">Kategori:</span> {{ selected.type }}</p>
          </div>
        </div>
      </div>

      <div class="p-4 border rounded-lg bg-yellow-50/50">
        <p class="text-xs text-yellow-600 font-bold uppercase mb-2">Deskripsi Pelanggaran</p>
        <p class="text-sm leading-relaxed text-gray-700">{{ selected.description || "Tidak ada deskripsi." }}</p>
      </div>

      <div v-if="selected.settlement" class="p-4 border-2 border-dashed border-green-200 rounded-lg bg-green-50">
        <p class="text-sm font-bold text-green-700 mb-2 inline-flex items-center gap-2">
          <i class="pi pi-check-circle"></i> Data Penyelesaian (Settlement)
        </p>
        <div class="grid grid-cols-2 gap-2 text-xs">
          <p><span class="text-green-600 font-medium">Petugas:</span> {{ selected.settlement.employee?.details?.name }}</p>
          <p><span class="text-green-600 font-medium">Waktu:</span> {{ new Date(selected.settlement.settled_at).toLocaleString() }}</p>
          <p class="col-span-2 mt-1"><span class="text-green-600 font-medium">Ket:</span> {{ selected.settlement.description }}</p>
        </div>
      </div>

      <div v-else-if="selected.status === 'active'" class="flex justify-center pt-2">
        <Button label="Proses Pelunasan Sekarang" icon="pi pi-money-bill" severity="success" class="w-full py-3" @click="$emit('settle', selected)" />
      </div>
    </div>
  </Dialog>

  <Dialog v-model:visible="modelShowSettle" modal header="Konfirmasi Pelunasan" class="w-[500px]">
    <div class="space-y-4">
      <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
        <p class="text-sm text-blue-800">Anda akan menandai pelanggaran <strong>{{ selected?.user?.details?.name }}</strong> sebagai lunas.</p>
      </div>
      
      <div class="flex flex-col gap-2">
        <label class="text-sm font-bold">Keterangan Settlement <span class="text-red-500">*</span></label>
        <Textarea 
          v-model="modelDescription" 
          rows="4" 
          placeholder="Contoh: Denda dibayar tunai / Alat sudah diganti..." 
          class="w-full"
        />
      </div>
    </div>
    <template #footer>
      <Button label="Batal" text severity="secondary" @click="modelShowSettle = false" />
      <Button label="Simpan Pelunasan" icon="pi pi-check" :loading="loading" @click="$emit('submit')" :disabled="!modelDescription" />
    </template>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  showDetail: boolean;
  showSettle: boolean;
  description: string;
  selected: any;
  loading: boolean;
}>();

const emit = defineEmits(['update:showDetail', 'update:showSettle', 'update:description', 'settle', 'submit']);

const modelShowDetail = computed({
  get: () => props.showDetail,
  set: (val) => emit('update:showDetail', val)
});

const modelShowSettle = computed({
  get: () => props.showSettle,
  set: (val) => emit('update:showSettle', val)
});

const modelDescription = computed({
  get: () => props.description,
  set: (val) => emit('update:description', val)
});
</script>