<script>
import AppLayout from "@/Layouts/App.vue";

export default {
    layout: AppLayout,
};
</script>

<script setup>
import { ref, computed, toRefs, watch } from "vue";
import { Head, useForm, usePage } from "@inertiajs/inertia-vue3";
import { uniq, join } from "lodash";
import FormControl from "@/Components/FormControl.vue";
import Label from "@/Components/Label.vue";
import Input from "@/Components/Input.vue";
import Toggle from "@/Components/Toggle.vue";
import Button from "@/Components/Button.vue";
import Textarea from "@/Components/Textarea.vue";
import Select from "@/Components/Select.vue";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import { RefreshIcon, UploadIcon, XIcon } from "@heroicons/vue/solid";
var VueScrollTo = require("vue-scrollto");

const props = defineProps({
    listing: Object,
    employmentTypes: Array,
    salaryRange: Object,
    companies: Object,
});

const { listing, employmentTypes, salaryRange, companies } = toRefs(props);

const hasCompanies = computed(() => companies.value?.data?.length > 0);

const companiesSelectItems = computed(() => {
    let items = companies.value.data;
    let newItems = {};

    for (let i = 0; i < items.length; i++) {
        newItems[items[i].id] = items[i].name;
    }

    return newItems;
});

const formErrors = computed(() => usePage().props.value.errors);

const form = useForm({
    // company
    company_id: listing.value.data.company.id,
    company_name: "",
    company_description: "",
    company_email: "",
    company_url: "",
    company_logo: "",
    company_invoice_address: "",
    company_invoice_notes: "",

    // job
    employment_type: listing.value.data.employment_type,
    position: listing.value.data.position,
    location: listing.value.data.location,
    description: listing.value.data.description,
    min_annual_salary: listing.value.data.min_annual_salary,
    max_annual_salary: listing.value.data.max_annual_salary,
    apply_url: listing.value.data.apply_url,
    tags: listing.value.data.tags
        ? join(
              listing.value.data.tags.map((item) => item.name),
              ", "
          )
        : "",
    is_closed: listing.value.data.is_closed,
});

const tags = computed(() => {
    return !form.tags
        ? []
        : uniq(
              form.tags
                  .split(",")
                  .map((item) => item.trim())
                  .filter((item) => item)
          );
});

const removeTag = (index = null) => {
    let localTags = tags.value;

    if (index != null) {
        localTags.splice(index, 1);
    }

    form.tags = localTags.join(", ");
};

const submitForm = () => {
    form.transform((data) => ({
        ...data,
        _method: "put", // spoof
    })).post(route("listing.update", listing.value.data.id), {
        preserveScroll: (page) => Object.keys(page.props.errors).length,
        onError: (err) => {
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

const hasSelectedExistingCompany = computed(() => form.company_id !== "");

const selectCompany = (e) => {
    // clear company details
    if (!e.target.value) {
        form.company_id = null;
        form.company_name = "";
        form.company_description = "";
        form.company_email = "";
        form.company_url = "";
        form.company_logo = "";
        form.company_invoice_address = "";
        form.company_invoice_notes = "";
        return;
    }

    form.company_id = e.target.value;
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
    <Head title="Post a Job" />

    <!-- Hero Header -->
    <header class="py-8 hero">
        <div class="flex-col max-w-3xl text-center hero-content">
            <h1 class="text-5xl">Edit Job Listing</h1>
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

                        <!-- Company Selection -->
                        <div v-if="hasCompanies">
                            <FormControl>
                                <Label
                                    for="company_id"
                                    text="Use existing company"
                                ></Label>

                                <div class="flex items-center gap-2">
                                    <Select
                                        id="company_id"
                                        @change="selectCompany"
                                        v-model="form.company_id"
                                        class="flex-1 select-bordered"
                                        firstItemLabel="Select company"
                                        :items="companiesSelectItems"
                                    >
                                    </Select>

                                    <button
                                        v-if="hasSelectedExistingCompany"
                                        @click="form.company_id = ''"
                                        type="button"
                                        class="inline-flex normal-case btn btn-ghost btn-square"
                                    >
                                        <RefreshIcon
                                            class="w-5 h-5"
                                        ></RefreshIcon>
                                        <span class="sr-only"
                                            >Clear selection</span
                                        >
                                    </button>
                                </div>
                            </FormControl>
                        </div>

                        <div
                            v-if="!hasSelectedExistingCompany && hasCompanies"
                            class="divider"
                        >
                            Or create a new one
                        </div>

                        <!-- Company Form -->
                        <div
                            v-if="!hasSelectedExistingCompany"
                            class="space-y-4"
                        >
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
                                                @click="form.company_logo = ''"
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
                        </div>
                    </div>
                </div>

                <!-- Job Details -->
                <div class="w-full shadow-xl card bg-base-100">
                    <div class="card-body">
                        <h3 class="mb-2 text-xl font-bold">Job Details</h3>

                        <div class="space-y-4">
                            <div
                                class="flex flex-col gap-4 md:items-start md:flex-row"
                            >
                                <!-- employment_type -->
                                <div class="flex-1">
                                    <FormControl>
                                        <Label
                                            for="employment_type"
                                            text="Employment Type"
                                        ></Label>

                                        <Select
                                            id="employment_type"
                                            v-model="form.employment_type"
                                            class="select-bordered"
                                            firstItemLabel="Select type"
                                            :items="employmentTypes"
                                        >
                                        </Select>

                                        <Label
                                            v-if="formErrors?.employment_type"
                                            :altText="
                                                formErrors?.employment_type
                                            "
                                            class="text-error"
                                        ></Label>
                                    </FormControl>
                                </div>

                                <!-- position -->
                                <div class="flex-1">
                                    <FormControl>
                                        <Label
                                            for="position"
                                            text="Position"
                                        ></Label>

                                        <Input
                                            id="position"
                                            v-model="form.position"
                                            type="text"
                                            class="input-bordered"
                                            required
                                        />

                                        <Label
                                            v-if="formErrors?.position"
                                            :altText="formErrors?.position"
                                            class="text-error"
                                        ></Label>
                                    </FormControl>
                                </div>
                            </div>

                            <!-- location -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="location"
                                        text="Location"
                                    ></Label>

                                    <Input
                                        id="location"
                                        v-model="form.location"
                                        type="text"
                                        class="input-bordered"
                                        required
                                    />

                                    <Label
                                        v-if="formErrors?.location"
                                        :altText="formErrors?.location"
                                        class="text-error"
                                    ></Label>
                                </FormControl>
                            </div>

                            <div
                                class="flex flex-col gap-4 md:items-start md:flex-row"
                            >
                                <!-- min_annual_salary -->
                                <div class="flex-1">
                                    <FormControl>
                                        <Label
                                            for="min_annual_salary"
                                            text="Minimum Salary per year"
                                        ></Label>

                                        <Select
                                            id="min_annual_salary"
                                            v-model="form.min_annual_salary"
                                            class="select-bordered"
                                            firstItemLabel="Select salary"
                                            :items="salaryRange"
                                            required
                                        >
                                        </Select>

                                        <Label
                                            v-if="formErrors?.min_annual_salary"
                                            :altText="
                                                formErrors?.min_annual_salary
                                            "
                                            class="text-error"
                                        ></Label>
                                    </FormControl>
                                </div>

                                <!-- max_annual_salary -->
                                <div class="flex-1">
                                    <FormControl>
                                        <Label
                                            for="max_annual_salary"
                                            text="Maximum Salary per year"
                                        ></Label>

                                        <Select
                                            id="max_annual_salary"
                                            v-model="form.max_annual_salary"
                                            class="select-bordered"
                                            firstItemLabel="Select salary"
                                            :items="salaryRange"
                                            required
                                        >
                                        </Select>

                                        <Label
                                            v-if="formErrors?.max_annual_salary"
                                            :altText="
                                                formErrors?.max_annual_salary
                                            "
                                            class="text-error"
                                        ></Label>
                                    </FormControl>
                                </div>
                            </div>

                            <!-- description -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="description"
                                        text="Description"
                                    ></Label>

                                    <QuillEditor
                                        v-model:content="form.description"
                                        theme="snow"
                                        contentType="html"
                                        class="h-64 sm:h-96"
                                    ></QuillEditor>
                                </FormControl>
                            </div>

                            <!-- apply_url -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="apply_url"
                                        text="Apply URL"
                                    ></Label>

                                    <Input
                                        id="apply_url"
                                        v-model="form.apply_url"
                                        type="url"
                                        class="input-bordered"
                                        required
                                    />

                                    <Label
                                        v-if="formErrors?.apply_url"
                                        :altText="formErrors?.apply_url"
                                        class="text-error"
                                    ></Label>
                                </FormControl>
                            </div>

                            <!-- tags -->
                            <div>
                                <FormControl>
                                    <Label
                                        for="tags"
                                        text="Tags (Separated by comma)"
                                    ></Label>

                                    <Input
                                        id="tags"
                                        v-model="form.tags"
                                        type="text"
                                        class="input-bordered"
                                        required
                                    />

                                    <Label
                                        v-if="formErrors?.tags"
                                        :altText="formErrors?.tags"
                                        class="text-error"
                                    ></Label>

                                    <div
                                        v-if="tags"
                                        class="flex flex-wrap gap-2 mt-2"
                                    >
                                        <div
                                            v-for="(tag, index) in tags"
                                            class="inline-flex items-center gap-1 badge badge-lg"
                                        >
                                            {{ tag }}

                                            <button
                                                @click="removeTag(index)"
                                                type="button"
                                            >
                                                <XIcon class="w-3 h-3"></XIcon>
                                                <span class="sr-only"
                                                    >Remove tag</span
                                                >
                                            </button>
                                        </div>
                                    </div>
                                </FormControl>
                            </div>

                            <!-- is_highlighted -->
                            <div>
                                <FormControl class="items-start">
                                    <Label
                                        text="Close listing"
                                        class="gap-3 cursor-pointer"
                                    >
                                        <Toggle
                                            name="is_closed"
                                            v-model:checked="form.is_closed"
                                            class="toggle-primary"
                                        ></Toggle>
                                    </Label>
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
                                    Save changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
