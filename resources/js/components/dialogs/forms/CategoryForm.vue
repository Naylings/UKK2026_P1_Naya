<script setup lang="ts">
interface CategoryFormType {
    name?: string;
    description?: string;
}

interface Props {
    visible: boolean;
    loading?: boolean;
    isEditMode?: boolean;
    form: CategoryFormType;
    dialogTitle?: string;
    submitButtonLabel?: string;
}

interface Emits {
    (e: "update:visible", value: boolean): void;
    (e: "submit"): void;
}

withDefaults(defineProps<Props>(), {
    loading: false,
    isEditMode: false,
    dialogTitle: "Form User",
    submitButtonLabel: "Simpan",
});

defineEmits<Emits>();

const roles = ["Admin", "Employee", "User"];
</script>

<template>
    <Dialog
        :visible="visible"
        :header="dialogTitle"
        :modal="true"
        @update:visible="$emit('update:visible', $event)"
        class="w-full md:w-1/2"
    >
        <div class="space-y-4">
            <!-- Name -->
            <div>
                <label class="block text-sm font-medium mb-2">Nama</label>
                <InputText
                    v-model="form.name"
                    placeholder="Nama kategori"
                    class="w-full"
                />
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium mb-2">Deskripsi</label>
                <Textarea
                    v-model="form.description"
                    placeholder="Deskripsi kategori"
                    rows="3"
                    class="w-full"
                />
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
                :label="submitButtonLabel"
                @click="$emit('submit')"
                :loading="loading"
            />
        </template>
    </Dialog>
</template>
