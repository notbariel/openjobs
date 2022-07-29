<script>
import AppLayout from "@/Layouts/App.vue";

export default {
    layout: AppLayout,
};
</script>

<script setup>
import { ref, computed, watch } from "vue";
import { Head, useForm, usePage } from "@inertiajs/inertia-vue3";
import FormControl from "@/Components/FormControl.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Button from "@/Components/Button.vue";
import Textarea from "@/Components/Textarea.vue";
import { RefreshIcon, UploadIcon } from "@heroicons/vue/solid";
var VueScrollTo = require("vue-scrollto");

const formErrors = computed(() => usePage().props.value.errors);

const form = useForm({
    company_name: "",
    company_description: "",
    company_email: "",
    company_url: "",
    company_logo: "",
    company_invoice_address: "",
    company_invoice_notes: "",
});

const submitForm = async () => {
    form.post(route("company.store"), {
        preserveScroll: (page) => Object.keys(page.props.errors).length,
        onError: (err) => {
            // scroll to first input with error
            let formKeys = Object.keys(form.data());

            for (let i = 0; i < formKeys.length; i++) {
                if (err.hasOwnProperty(formKeys[i])) {
                    VueScrollTo.scrollTo(
                        document.getElementById(formKeys[i]),
                        200,
                        { offset: -40 }
                    );
                    break;
                }
            }
        },
    });
};

const logoRef = ref();
const logoSrc = ref();

const companyLogo = computed(() => form.company_logo);

watch(companyLogo, (value) => {
    if (!value) {
        logoSrc.value = null;
        return;
    }

    let reader = new FileReader();
    reader.readAsArrayBuffer(value);
    reader.onload = (e) => {
        let blob = new Blob([e.target.result], {
            type: "image/png",
        });
        logoSrc.value = URL.createObjectURL(blob);
    };
    reader.onerror = (e) => {
        logoSrc.value = null;
    };
});
</script>

<template>
    <Head title="Create company" />

    <!-- Hero Header -->
    <header class="py-8 hero">
        <div class="flex-col max-w-3xl text-center hero-content">
            <h1 class="text-5xl">Create New Company</h1>
        </div>
    </header>

    <div class="max-w-4xl py-10 mx-auto">
        <form @submit.prevent="submitForm">
            <div class="space-y-8">
                <!-- Company Details -->
                <div class="w-full shadow-xl card bg-base-100">
                    <div class="card-body">
                        <h3 class="mb-2 text-xl font-bold">
                            Company Information
                        </h3>

                        <div class="space-y-4">
                            <!-- company_name -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="company_name"
                                        text="Company Name"
                                    ></Label>

                                    <Input
                                        id="company_name"
                                        v-model="form.company_name"
                                        type="text"
                                        class="input-bordered"
                                        required
                                    />

                                    <Label
                                        v-if="formErrors?.company_name"
                                        :altText="formErrors?.company_name"
                                        class="text-error"
                                    ></Label>
                                </FormControl>
                            </div>

                            <!-- company_description -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="company_description"
                                        text="Company Description"
                                    ></Label>

                                    <Textarea
                                        id="company_description"
                                        v-model="form.company_description"
                                        class="textarea-bordered"
                                    />

                                    <Label
                                        v-if="formErrors?.company_description"
                                        :altText="
                                            formErrors?.company_description
                                        "
                                        class="text-error"
                                    ></Label>
                                </FormControl>
                            </div>

                            <div
                                class="flex flex-col gap-4 md:items-start md:flex-row"
                            >
                                <!-- company_email -->
                                <div class="flex-1">
                                    <FormControl>
                                        <Label
                                            for="company_email"
                                            text="Company Email"
                                        ></Label>

                                        <Input
                                            id="company_email"
                                            v-model="form.company_email"
                                            type="email"
                                            class="input-bordered"
                                            required
                                        />

                                        <Label
                                            v-if="formErrors?.company_email"
                                            :altText="formErrors?.company_email"
                                            class="text-error"
                                        ></Label>
                                    </FormControl>
                                </div>

                                <!-- company_url -->
                                <div class="flex-1">
                                    <FormControl>
                                        <Label
                                            for="company_url"
                                            text="Company URL"
                                        ></Label>

                                        <Input
                                            id="company_url"
                                            v-model="form.company_url"
                                            type="url"
                                            class="input-bordered"
                                            required
                                        />

                                        <Label
                                            v-if="formErrors?.company_url"
                                            :altText="formErrors?.company_url"
                                            class="text-error"
                                        ></Label>
                                    </FormControl>
                                </div>
                            </div>

                            <!-- company_invoice_address -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="company_invoice_address"
                                        text="Invoice Address"
                                    ></Label>

                                    <Input
                                        id="company_invoice_address"
                                        v-model="form.company_invoice_address"
                                        type="text"
                                        class="input-bordered"
                                        required
                                    />

                                    <Label
                                        v-if="
                                            formErrors?.company_invoice_address
                                        "
                                        :altText="
                                            formErrors?.company_invoice_address
                                        "
                                        class="text-error"
                                    ></Label>
                                </FormControl>
                            </div>

                            <!-- company_invoice_notes -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="company_invoice_notes"
                                        text="Invoice Notes"
                                    ></Label>

                                    <Textarea
                                        id="company_invoice_notes"
                                        v-model="form.company_invoice_notes"
                                        class="textarea-bordered"
                                    />

                                    <Label
                                        v-if="formErrors?.company_invoice_notes"
                                        :altText="
                                            formErrors?.company_invoice_notes
                                        "
                                        class="text-error"
                                    ></Label>
                                </FormControl>
                            </div>

                            <!-- company_logo -->
                            <div>
                                <FormControl>
                                    <Label text="Company Logo"></Label>

                                    <div
                                        class="flex flex-col items-start gap-2"
                                    >
                                        <div v-if="logoSrc" class="max-w-xs">
                                            <img
                                                :src="logoSrc"
                                                alt="logo preview"
                                            />
                                        </div>

                                        <input
                                            id="company_logo"
                                            type="file"
                                            @change="
                                                (e) =>
                                                    (form.company_logo =
                                                        e.target.files[0])
                                            "
                                            class="sr-only"
                                            ref="logoRef"
                                            tabindex="-1"
                                        />

                                        <p
                                            v-if="form.company_logo"
                                            class="text-sm opacity-70"
                                        >
                                            {{ form.company_logo.name }}
                                        </p>

                                        <div class="flex items-center gap-2">
                                            <button
                                                @click.prevent="logoRef.click()"
                                                type="button"
                                                class="gap-2 normal-case btn btn-primary"
                                            >
                                                <UploadIcon
                                                    class="w-5 h-5"
                                                ></UploadIcon>
                                                <span>Upload image</span>
                                            </button>

                                            <button
                                                v-if="form.company_logo"
                                                @click="
                                                    form.company_logo = null
                                                "
                                                type="button"
                                                class="inline-flex normal-case btn btn-ghost btn-square"
                                            >
                                                <RefreshIcon
                                                    class="w-5 h-5"
                                                ></RefreshIcon>
                                                <span class="sr-only"
                                                    >Remove</span
                                                >
                                            </button>
                                        </div>
                                    </div>

                                    <Label
                                        v-if="formErrors?.company_logo"
                                        :altText="formErrors?.company_logo"
                                        class="text-error"
                                    ></Label>
                                </FormControl>
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="submit"
                                    class="flex-1 mt-4 normal-case btn btn-lg btn-secondary"
                                    :class="{
                                        'btn-disabled loading': form.processing,
                                    }"
                                    :disabled="form.processing"
                                >
                                    Create company
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
