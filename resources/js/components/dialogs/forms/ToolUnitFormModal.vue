<script setup lang="ts">
import { ref, computed, watch } from "vue";
import type {
  CreateToolUnitPayload,
  ToolUnit,
  ConditionType,
} from "@/types/toolunit";

// ── Props & Emits ─────────────────────────────────────────────────────────

interface Props {
  visible: boolean;
  toolId?: number;
  loading?: boolean;
}

interface Emits {
  (e: "update:visible", value: boolean): void;
  (e: "submit", payload: CreateToolUnitPayload): void;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
});

const emit = defineEmits<Emits>();

// ── State ─────────────────────────────────────────────────────────────────

const quantity = ref<number>(1);
const condition = ref<ConditionType>("good");
const notes = ref<string>("");
const errors = ref<Record<string, string>>({});

// ── Computed ────────────────────────────────────────────────────────────

const dialogTitle = computed(() => "Tambah Unit Baru");

const isFormValid = computed(() => {
  return quantity.value > 0 && quantity.value <= 999;
});

const submitButtonLabel = computed(() => "Buat Unit");

// ── Watchers ──────────────────────────────────────────────────────────────

watch(
  () => props.visible,
  (newVal) => {
    if (newVal) {
      resetForm();
    }
  },
);

// ── Methods ───────────────────────────────────────────────────────────────

function resetForm() {
  quantity.value = 1;
  condition.value = "good";
  notes.value = "";
  errors.value = {};
}

function validateForm(): boolean {
  errors.value = {};

  if (quantity.value < 1) {
    errors.value.quantity = "Jumlah harus minimal 1";
  }
  if (quantity.value > 999) {
    errors.value.quantity = "Jumlah terlalu banyak (max 999)";
  }

  return Object.keys(errors.value).length === 0;
}

function handleSubmit() {
  if (!validateForm()) {
    return;
  }

  const payload: CreateToolUnitPayload = {
    tool_id: props.toolId!,
    quantity: quantity.value,
    condition: condition.value,
    notes: notes.value || undefined,
  };
  emit("submit", payload);
}

function handleClose() {
  emit("update:visible", false);
}

function getConditionLabel(condition: string): string {
  const map: Record<string, string> = {
    good: "Baik",
    broken: "Rusak",
    maintenance: "Maintenance",
  };
  return map[condition] || condition;
}

function getConditionSeverity(condition: string): string {
  const map: Record<string, string> = {
    good: "success",
    broken: "danger",
    maintenance: "warning",
  };
  return map[condition] || "secondary";
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString("id-ID", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

// ── Options ───────────────────────────────────────────────────────────────

const conditionOptions = [
  { label: "Baik", value: "good" },
  { label: "Rusak", value: "broken" },
  { label: "Maintenance", value: "maintenance" },
];
</script>

<template>
  <Dialog
    :visible="visible"
    :header="dialogTitle"
    modal
    :closable="!loading"
    class="w-full sm:w-96"
    @update:visible="handleClose"
  >
    <div class="space-y-4">
      <!-- Quantity -->
      <div class="field">
        <label for="quantity" class="text-sm font-semibold block mb-2">
          Jumlah Unit
          <span class="text-red-500">*</span>
        </label>
        <InputNumber
          id="quantity"
          v-model="quantity"
          :min="1"
          :max="999"
          :disabled="loading"
          class="w-full"
          input-class="w-full"
          @keyup.enter="handleSubmit"
        />
        <div v-if="errors.quantity" class="text-red-500 text-xs mt-1">
          {{ errors.quantity }}
        </div>
      </div>

      <!-- Kondisi Awal -->
      <div class="field">
        <label for="condition" class="text-sm font-semibold block mb-2">
          Kondisi Awal
        </label>
        <Select
          id="condition"
          v-model="condition"
          :options="conditionOptions"
          option-label="label"
          option-value="value"
          :disabled="loading"
          class="w-full"
        />
        <div class="text-xs text-surface-500 mt-1">
          Kondisi unit saat pertama kali dibuat
        </div>
      </div>

      <!-- Catatan (Optional) -->
      <div class="field">
        <label for="notes" class="text-sm font-semibold block mb-2">
          Catatan (Opsional)
        </label>
        <Textarea
          id="notes"
          v-model="notes"
          placeholder="Tambahkan catatan tentang unit (misal: kondisi fisik khusus, fitur rusak, dll)..."
          :disabled="loading"
          rows="3"
          class="w-full"
          @keyup.enter.meta="handleSubmit"
        />
        <div class="text-xs text-surface-500 mt-1">
          {{ notes.length }}/500 karakter
        </div>
      </div>

      <!-- Info -->
      <Message
        severity="info"
        icon="pi pi-info-circle"
        class="text-xs w-full"
        :closable="false"
      >
        Kode unit akan otomatis dibuat oleh sistem berdasarkan tool
      </Message>
    </div>

    <!-- Footer -->
    <template #footer>
      <Button
        label="Batal"
        icon="pi pi-times"
        text
        :disabled="loading"
        @click="handleClose"
      />
      <Button
        :label="submitButtonLabel"
        icon="pi pi-check"
        :loading="loading"
        :disabled="!isFormValid || loading"
        @click="handleSubmit"
      />
    </template>
  </Dialog>
</template>
