<script setup>
import { computed } from "vue";
import { Head, useForm, usePage } from "@inertiajs/inertia-vue3";
import GuestLayout from "@/Layouts/Guest.vue";
import FormControl from "@/Components/FormControl.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Button from "@/Components/Button.vue";

const formErrors = computed(() => usePage().props.value.errors);

const form = useForm({
    password: "",
});

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <form @submit.prevent="submit">
            <div class="space-y-4">
                <p>
                    This is a secure area of the application. Please confirm
                    your password before continuing.
                </p>

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

                <div class="flex justify-end">
                    <Button
                        class="normal-case btn-secondary"
                        :class="{ 'loading btn-disabled': form.processing }"
                        :disabled="form.processing"
                    >
                        Confirm
                    </Button>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
