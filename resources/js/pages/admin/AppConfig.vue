<script setup lang="ts">
import { onBeforeMount, ref, reactive } from "vue";
import { useAppConfigStore } from "@/stores/appconfig";
import { appConfigService } from "@/services/appConfigService";
import type { UpdateAppConfigPayload } from "@/types/appconfig";
import { useToast } from "primevue/usetoast";

const toast = useToast();
const appConfigStore = useAppConfigStore();

// ── State ──────────────────────────────────────────────────────────────

const form = reactive<UpdateAppConfigPayload>({
  name: "",
  late_point: 0,
  broken_point: 0,
  lost_point: 0,
  late_fine: 0,
  broken_fine: 0,
  lost_fine: 0,
});

const validationErrors = ref<string[]>([]);

// ── Lifecycle ──────────────────────────────────────────────────────────

onBeforeMount(async () => {
  await appConfigStore.fetchConfig();

  if (appConfigStore.config) {
    Object.assign(form, appConfigStore.config);
  }
});

// ── Handlers ───────────────────────────────────────────────────────────

async function handleSubmit() {
  // Clear previous errors
  validationErrors.value = [];

  // Validate
  validationErrors.value = appConfigService.validatePayload(form);
  if (validationErrors.value.length > 0) {
    toast.add({
      severity: "error",
      summary: "Validasi Gagal",
      detail: validationErrors.value.join(", "),
      life: 3000,
    });
    return;
  }

  // Submit
  const success = await appConfigStore.updateConfig(form);

  if (success) {
    toast.add({
      severity: "success",
      summary: "Berhasil",
      detail: appConfigStore.successMessage || "Konfigurasi berhasil diperbarui",
      life: 3000,
    });

    // Refresh halaman setelah 1 detik
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  } else {
    toast.add({
      severity: "error",
      summary: "Gagal",
      detail: appConfigStore.error || "Gagal memperbarui konfigurasi",
      life: 3000,
    });
  }
}

function handleReset() {
  if (appConfigStore.config) {
    Object.assign(form, appConfigStore.config);
  }
  validationErrors.value = [];
}
</script>

<template>
  <div class="card">
    <div class="font-semibold text-xl mb-6">Konfigurasi Aplikasi</div>

    <!-- Alert: Validation Errors -->
    <Message
      v-if="validationErrors.length > 0"
      severity="error"
      class="mb-4 w-full"
      icon="pi pi-exclamation-triangle"
      :closable="true"
      @close="validationErrors = []"
    >
      <ul class="list-disc ml-4">
        <li v-for="(err, idx) in validationErrors" :key="idx">
          {{ err }}
        </li>
      </ul>
    </Message>

    <!-- Form -->
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Name: Nama Aplikasi/Instansi (Topbar) -->
      <div class="field">
        <label for="name" class="font-semibold block mb-2">
          Nama Aplikasi/Instansi
          <span class="text-red-500">*</span>
        </label>
        <p class="text-sm text-surface-600 mb-3">
          Nama ini akan ditampilkan di topbar (disamping logo)
        </p>
        <InputText
          id="name"
          v-model="form.name"
          placeholder="Contoh: Sistem Manajemen Alat"
          class="w-full"
          :disabled="appConfigStore.loading"
        />
      </div>

      <!-- Points Section -->
      <Divider align="left">
        <b>Poin Penalty</b>
      </Divider>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Late Point -->
        <div class="field">
          <label for="late_point" class="font-semibold block mb-2">
            Poin Keterlambatan
            <span class="text-red-500">*</span>
          </label>
          <InputNumber
            id="late_point"
            v-model="form.late_point"
            :use-grouping="false"
            placeholder="0"
            :disabled="appConfigStore.loading"
            class="w-full"
          />
        </div>

        <!-- Broken Point -->
        <div class="field">
          <label for="broken_point" class="font-semibold block mb-2">
            Poin Kerusakan
            <span class="text-red-500">*</span>
          </label>
          <InputNumber
            id="broken_point"
            v-model="form.broken_point"
            :use-grouping="false"
            placeholder="0"
            :disabled="appConfigStore.loading"
            class="w-full"
          />
        </div>

        <!-- Lost Point -->
        <div class="field">
          <label for="lost_point" class="font-semibold block mb-2">
            Poin Kehilangan
            <span class="text-red-500">*</span>
          </label>
          <InputNumber
            id="lost_point"
            v-model="form.lost_point"
            :use-grouping="false"
            placeholder="0"
            :disabled="appConfigStore.loading"
            class="w-full"
          />
        </div>
      </div>

      <!-- Fines Section -->
      <Divider align="left">
        <b>Denda (%)</b>
      </Divider>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Late Fine -->
        <div class="field">
          <label for="late_fine" class="font-semibold block mb-2">
            Denda Keterlambatan (%)
            <span class="text-red-500">*</span>
          </label>
          <InputNumber
            id="late_fine"
            v-model="form.late_fine"
            :use-grouping="false"
            :min="0"
            :max="100"
            placeholder="0"
            suffix="%"
            :disabled="appConfigStore.loading"
            class="w-full"
          />
        </div>

        <!-- Broken Fine -->
        <div class="field">
          <label for="broken_fine" class="font-semibold block mb-2">
            Denda Kerusakan (%)
            <span class="text-red-500">*</span>
          </label>
          <InputNumber
            id="broken_fine"
            v-model="form.broken_fine"
            :use-grouping="false"
            :min="0"
            :max="100"
            placeholder="0"
            suffix="%"
            :disabled="appConfigStore.loading"
            class="w-full"
          />
        </div>

        <!-- Lost Fine -->
        <div class="field">
          <label for="lost_fine" class="font-semibold block mb-2">
            Denda Kehilangan (%)
            <span class="text-red-500">*</span>
          </label>
          <InputNumber
            id="lost_fine"
            v-model="form.lost_fine"
            :use-grouping="false"
            :min="0"
            :max="100"
            placeholder="0"
            suffix="%"
            :disabled="appConfigStore.loading"
            class="w-full"
          />
        </div>
      </div>

      <!-- Last Updated -->
      <div v-if="appConfigStore.config?.updated_at" class="text-sm text-surface-500 italic">
        Terakhir diubah: {{ new Date(appConfigStore.config.updated_at).toLocaleString('id-ID') }}
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-3 pt-4">
        <Button
          type="submit"
          label="Simpan"
          icon="pi pi-save"
          :loading="appConfigStore.loading"
          severity="success"
        />
        <Button
          type="button"
          label="Reset"
          icon="pi pi-refresh"
          @click="handleReset"
          severity="secondary"
          :disabled="appConfigStore.loading"
        />
      </div>
    </form>
  </div>
</template>
