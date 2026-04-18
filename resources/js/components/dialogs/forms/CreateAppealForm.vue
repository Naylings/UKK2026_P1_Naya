<script setup lang="ts">
interface Props {
  visible: boolean;
  reason: string;
  loading?: boolean;
  isValid?: boolean;
}

interface Emits {
  (e: "update:visible", value: boolean): void;
  (e: "update:reason", value: string): void;
  (e: "submit"): void;
}

withDefaults(defineProps<Props>(), {
  loading: false,
  isValid: false,
});

defineEmits<Emits>();
</script>

<template>
  <Dialog
    :visible="visible"
    header="Ajukan Banding"
    modal
    class="w-full md:w-1/3"
    @update:visible="$emit('update:visible', $event)"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-2">
          Alasan Banding
        </label>

        <Textarea
          :modelValue="reason"
          @update:modelValue="$emit('update:reason', $event)"
          rows="5"
          autoResize
          placeholder="Jelaskan alasan banding..."
          class="w-full"
        />

        <div class="text-xs text-surface-500 mt-1">
          Jelaskan secara jelas dan spesifik agar mudah direview
        </div>
      </div>
    </div>

    <template #footer>
      <Button
        label="Batal"
        severity="secondary"
        @click="$emit('update:visible', false)"
        :loading="loading"
      />
      <Button
        label="Kirim"
        @click="$emit('submit')"
        :disabled="!isValid"
        :loading="loading"
      />
    </template>
  </Dialog>
</template>