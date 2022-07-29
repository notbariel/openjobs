<script setup>
import { computed } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/inertia-vue3";
import GuestLayout from "@/Layouts/Guest.vue";
import FormControl from "@/Components/FormControl.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Checkbox from "@/Components/Checkbox.vue";
import Button from "@/Components/Button.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const formErrors = computed(() => usePage().props.value.errors);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <form @submit.prevent="submit">
            <div class="space-y-4">
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

                <!-- password -->
                <div>
                    <FormControl>
                        <Label for="password" text="Password"></Label>

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

                <!-- remember -->
                <div>
                    <FormControl class="items-start">
                        <Label text="Remember me" class="gap-3 cursor-pointer">
                            <Checkbox
                                name="remember"
                                v-model:checked="form.remember"
                                class="checkbox-primary"
                            ></Checkbox>
                        </Label>
                    </FormControl>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="normal-case btn btn-sm btn-link"
                    >
                        Forgot your password?
                    </Link>

                    <Button
                        class="normal-case btn-secondary"
                        :class="{ 'loading btn-disabled': form.processing }"
                        :disabled="form.processing"
                    >
                        Log in
                    </Button>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
