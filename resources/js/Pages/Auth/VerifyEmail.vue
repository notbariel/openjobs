<script setup>
import { computed } from "vue";
import { Head, useForm } from "@inertiajs/inertia-vue3";
import GuestLayout from "@/Layouts/Guest.vue";
import Button from "@/Components/Button.vue";

const props = defineProps({
    status: String,
});

const form = useForm();

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <form @submit.prevent="submit">
            <div class="space-y-4">
                <p class="mb-4 text-sm text-gray-600">
                    Thanks for signing up! Before getting started, could you
                    verify your email address by clicking on the link we just
                    emailed to you? If you didn't receive the email, we will
                    gladly send you another.
                </p>

                <p class="text-success" v-if="verificationLinkSent">
                    A new verification link has been sent to the email address
                    you provided during registration.
                </p>

                <div class="flex items-center justify-between gap-2">
                    <Button
                        class="normal-case btn-secondary"
                        :class="{ 'loading btn-disabled': form.processing }"
                        :disabled="form.processing"
                    >
                        Resend Verification Email
                    </Button>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="normal-case btn btn-sm btn-link"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
