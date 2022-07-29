<script setup>
import { computed } from "vue";
import { Head, useForm, usePage } from "@inertiajs/inertia-vue3";
import GuestLayout from "@/Layouts/Guest.vue";
import FormControl from "@/Components/FormControl.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Button from "@/Components/Button.vue";

defineProps({
    status: String,
});

const formErrors = computed(() => usePage().props.value.errors);

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <form @submit.prevent="submit">
            <div class="space-y-4">
                <p>
                    Forgot your password? No problem. Just let us know your
                    email address and we will email you a password reset link
                    that will allow you to choose a new one.
                </p>

                <p v-if="status" class="text-success">
                    {{ status }}
                </p>

                <!-- email -->
                <div>
                    <FormControl>
                        <Label for="email" text="Email"></Label>

                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="input-bordered"
                            required
                        />

                        <Label
                            v-if="formErrors?.email"
                            :altText="formErrors?.email"
                            class="text-error"
                        ></Label>
                    </FormControl>
                </div>

                <div class="flex items-center justify-end">
                    <Button
                        class="normal-case btn-secondary"
                        :class="{ 'loading btn-disabled': form.processing }"
                        :disabled="form.processing"
                    >
                        Email Password Reset Link
                    </Button>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
