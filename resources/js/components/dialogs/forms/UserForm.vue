<script setup lang="ts">
import type { CreateUserPayload } from '@/types/user';

interface UserFormType {
  email?: string;
  password?: string;
  role?: 'Admin' | 'Employee' | 'User';
  nik?: string;
  name?: string;
  no_hp?: string;
  address?: string;
  birth_date?: Date | null;
}

interface Props {
  visible: boolean;
  loading?: boolean;
  isEditMode?: boolean;
  form: UserFormType;
  dialogTitle?: string;
  submitButtonLabel?: string;
}

interface Emits {
  (e: 'update:visible', value: boolean): void;
  (e: 'submit'): void;
  (e: 'cancel'): void;
}

withDefaults(defineProps<Props>(), {
  loading: false,
  isEditMode: false,
  dialogTitle: 'Form User',
  submitButtonLabel: 'Simpan',
});

defineEmits<Emits>();



const roles = ['Admin', 'Employee', 'User'];
</script>

<template>
  <Dialog :visible="visible" :header="dialogTitle" :modal="true" @update:visible="$emit('update:visible', $event)"
    class="w-full md:w-1/2">
    <div class="space-y-4">
      <!-- Email -->
      <div>
        <label class="block text-sm font-medium mb-2">Email</label>
        <InputText v-model="form.email" type="email" :disabled="isEditMode" placeholder="user@example.com"
          class="w-full" />
      </div>

      <!-- Password (only on create) -->
      <div v-if="!isEditMode">
        <label class="block text-sm font-medium mb-2">Password</label>
        <Password v-model="form.password" placeholder="Masukkan password" :feedback="false" class="w-full"
          toggle-mask />
      </div>

      <!-- Role -->
      <div>
        <label class="block text-sm font-medium mb-2">Role</label>
        <Dropdown v-model="form.role" :options="roles" placeholder="Pilih role" class="w-full" />
      </div>

      <!-- Detail Section -->
      <Divider align="left">
        <span class="text-sm font-medium">Detail User</span>
      </Divider>

      <!-- NIK -->
      <div>
        <label class="block text-sm font-medium mb-2">NIK</label>
        <InputText v-model="form.nik" placeholder="Nomor identitas" class="w-full" />
      </div>

      <!-- Name -->
      <div>
        <label class="block text-sm font-medium mb-2">Nama Lengkap</label>
        <InputText v-model="form.name" placeholder="Nama lengkap" class="w-full" />
      </div>

      <!-- Phone -->
      <div>
        <label class="block text-sm font-medium mb-2">No. Telepon</label>
        <InputText v-model="form.no_hp" placeholder="08xx xxxx xxxx" class="w-full" />
      </div>

      <!-- Address -->
      <div>
        <label class="block text-sm font-medium mb-2">Alamat</label>
        <Textarea v-model="form.address" placeholder="Alamat lengkap" rows="3" class="w-full" />
      </div>

      <!-- Birth Date -->
      <div>
        <label class="block text-sm font-medium mb-2">Tanggal Lahir</label>
        <Calendar v-model="form.birth_date" date-format="yy-mm-dd" placeholder="YYYY-MM-DD" class="w-full" />
      </div>
    </div>

    <template #footer>
      <Button label="Batal" severity="secondary" @click="$emit('cancel')" :loading="loading" />
      <Button :label="submitButtonLabel" @click="$emit('submit')" :loading="loading" />
    </template>
  </Dialog>
</template>
