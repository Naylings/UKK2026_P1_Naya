<script setup>
import { ref } from 'vue';

const name = ref('');
const email = ref('');
const password = ref('');
const checked = ref(false);
</script>

<template>
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div
                style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)"
            >
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="text-center mb-8">
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">
                            Create Account
                        </div>
                        <span class="text-muted-color font-medium">
                            Sign up to get started
                        </span>
                    </div>

                    <div>
                        <!-- Name -->
                        <label class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2">
                            Name
                        </label>
                        <InputText
                            type="text"
                            placeholder="Full name"
                            class="w-full md:w-[30rem] mb-6"
                            v-model="name"
                        />

                        <!-- Email -->
                        <label class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2">
                            Email
                        </label>
                        <InputText
                            type="text"
                            placeholder="Email address"
                            class="w-full md:w-[30rem] mb-6"
                            v-model="email"
                        />

                        <!-- Password -->
                        <label class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2">
                            Password
                        </label>
                        <Password
                            v-model="password"
                            placeholder="Password"
                            :toggleMask="true"
                            class="mb-6"
                            fluid
                            :feedback="false"
                        />

                        <!-- Agreement -->
                        <div class="flex items-center mb-8">
                            <Checkbox v-model="checked" binary class="mr-2" />
                            <label>
                                I agree to the terms & conditions
                            </label>
                        </div>

                        <!-- Register -->
                        <Button label="Register" class="w-full"  as="router-link" to="/"/>

                        <!-- Back to login -->
                        <Button
                            label="Already have an account?"
                            severity="secondary"
                            text
                            class="w-full"
                            as="router-link"
                            to="/auth/login"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
