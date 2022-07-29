<script setup>
import { computed } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/inertia-vue3";
import GuestLayout from "@/Layouts/Guest.vue";
import FormControl from "@/Components/FormControl.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Button from "@/Components/Button.vue";

const formErrors = computed(() => usePage().props.value.errors);

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div class="space-y-4">
                <!-- name -->
                <div>
                    <FormControl>
                        <Label for="name" text="Name"></Label>

                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="input-bordered"
                            required
                        />

                        <Label
                            v-if="formErrors?.name"
                            :altText="formErrors?.name"
                            class="text-error"
                        ></Label>
                    </FormControl>
                </div>

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

                <div class="flex items-center justify-end gap-2">
                    <Link
                        :href="route('login')"
                        class="normal-case btn btn-sm btn-link"
                    >
                        Already registered?
                    </Link>

                    <Button
                        class="normal-case btn-secondary"
                        :class="{ 'loading btn-disabled': form.processing }"
                        :disabled="form.processing"
                    >
                        Register
                    </Button>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
