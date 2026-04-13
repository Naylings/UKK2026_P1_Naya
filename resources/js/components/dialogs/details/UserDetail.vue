<script setup lang="ts">
import type { User } from "@/types/user";
import { useFormatter } from "@/utils/useFormatter";


const { formatDate } = useFormatter();
interface Props {
    visible: boolean;
    user: User | null;
}

interface Emits {
    (e: "update:visible", value: boolean): void;
    (e: "edit", user: User): void;
    (e: "delete", user: User): void;
    (e: "update-credit", user: User): void;
}

defineProps<Props>();
defineEmits<Emits>();
</script>

<template>
    <Dialog
        :visible="visible"
        :header="`Detail User - ${user?.details?.name || user?.email}`"
        :modal="true"
        @update:visible="$emit('update:visible', $event)"
        class="w-full md:w-1/2"
    >
        <div v-if="user" class="space-y-6">
            <!-- Account Info -->
            <div>
                <h3 class="font-semibold mb-3">Informasi Akun</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >Email:</span
                        >
                        <span class="font-medium">{{ user.email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >Role:</span
                        >
                        <Tag
                            :value="user.role"
                            :severity="
                                user.role === 'Admin'
                                    ? 'danger'
                                    : user.role === 'Employee'
                                      ? 'info'
                                      : 'success'
                            "
                        />
                    </div>
                    <div
                        v-if="user?.role === 'User'"
                        class="flex justify-between"
                    >
                        <span class="text-surface-600 dark:text-surface-400"
                            >Credit:</span
                        >
                        <span class="font-medium">{{ user.credit_score }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >Status:</span
                        >
                        <span
                            :class="
                                user.is_restricted
                                    ? 'text-red-600'
                                    : 'text-green-600'
                            "
                            class="font-medium"
                        >
                            {{ user.is_restricted ? "Dibatasi" : "Aktif" }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Personal Info -->
            <div v-if="user.details">
                <h3 class="font-semibold mb-3">Informasi Pribadi</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >NIK:</span
                        >
                        <span class="font-medium">{{ user.details.nik }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >Nama:</span
                        >
                        <span class="font-medium">{{ user.details.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >Telepon:</span
                        >
                        <span class="font-medium">{{
                            user.details.no_hp
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >Alamat:</span
                        >
                        <span class="font-medium">{{
                            user.details.address
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-surface-600 dark:text-surface-400"
                            >Tgl Lahir:</span
                        >
                        <span class="font-medium">{{
                            formatDate(user.details.birth_date)
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="text-xs text-surface-500">
                <div>
                    Dibuat:
                    {{ new Date(user.created_at).toLocaleString("id-ID") }}
                </div>
                <div>
                    Diupdate:
                    {{ new Date(user.updated_at).toLocaleString("id-ID") }}
                </div>
            </div>
        </div>
        <template #footer>
            <Button
                icon="pi pi-pencil"
                label="Edit"
                severity="warning"
                @click="$emit('edit', user!)"
            />
            <!-- Hanya tampilkan Update Credit jika role User -->
            <Button
                v-if="user?.role === 'User'"
                icon="pi pi-dollar"
                label="Update Credit"
                severity="success"
                @click="$emit('update-credit', user!)"
            />
            <Button
                icon="pi pi-trash"
                label="Hapus"
                severity="danger"
                @click="$emit('delete', user!)"
            />
        </template>
    </Dialog>
</template>
