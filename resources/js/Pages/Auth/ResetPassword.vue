<script setup>
import { computed } from "vue";
import { Head, useForm, usePage } from "@inertiajs/inertia-vue3";
import GuestLayout from "@/Layouts/Guest.vue";
import FormControl from "@/Components/FormControl.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Button from "@/Components/Button.vue";

const props = defineProps({
    email: String,
    token: String,
});

const formErrors = computed(() => usePage().props.value.errors);

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.update"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <form @submit.prevent="submit">
            <div class="space-y-4">
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

                <!-- password -->
                <div>
                    <FormControl>
                        <Label for="password" text="New Password"></Label>

                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="input-bordered"
                            required
                        />

                        <Label
                            v-if="formErrors?.password"
                            :altText="formErrors?.password"
                            class="text-error"
                        ></Label>
                    </FormControl>
                </div>

                <!-- password_confirmation -->
                <div>
                    <FormControl>
                        <Label
                            for="password_confirmation"
                            text="Confirm Password"
                        ></Label>

                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="input-bordered"
                            required
                        />
                    </FormControl>
                </div>

                <div class="flex items-center justify-end">
                    <Button
                        class="normal-case btn-secondary"
                        :class="{ 'loading btn-disabled': form.processing }"
                        :disabled="form.processing"
                    >
                        Reset Password
                    </Button>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
