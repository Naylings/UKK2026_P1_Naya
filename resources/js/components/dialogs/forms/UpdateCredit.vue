<script setup lang="ts">
interface Props {
  visible: boolean;
  form: { credit_score: number };
  loading?: boolean;
}

interface Emits {
  (e: 'update:visible', value: boolean): void;
  (e: 'submit'): void;
}

withDefaults(defineProps<Props>(), {
  loading: false,
});

defineEmits<Emits>();
</script>

<template>
  <Dialog
    :visible="visible"
    header="Update Credit User"
    :modal="true"
    @update:visible="$emit('update:visible', $event)"
    class="w-full md:w-1/3"
  >
    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-2">Credit Amount</label>
        <InputNumber
          v-model="form.credit_score"
          :min="0"
          :max="999999"
          placeholder="Masukkan jumlah credit"
          class="w-full"
        />
        <div class="text-xs text-surface-500 mt-1">
          Min: 0 | Max:100
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
        label="Update"
        @click="$emit('submit')"
        :loading="loading"
      />
    </template>
  </Dialog>
</template>
