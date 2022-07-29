<script setup>
import { toRefs, computed } from "vue";
import { Link, usePage } from "@inertiajs/inertia-vue3";
import { ExternalLinkIcon, PencilAltIcon } from "@heroicons/vue/solid";

const props = defineProps({
    company: Object,
});

const { company } = toRefs(props);

const hasLogo = computed(() => company.value.logo !== null);

const userOwnsCompany = computed(() => {
    let pageProps = usePage().props.value;
    return pageProps.auth.user
        ? pageProps.owned.companies.includes(company.value.id)
        : false;
});
</script>

<template>
    <!-- Item Row -->
    <tr class="group">
        <!-- Company/Listing Info -->
        <td class="whitespace-normal">
            <div class="flex items-center gap-3">
                <div class="avatar">
                    <div class="w-12 h-12 mask mask-circle">
                        <img
                            v-if="hasLogo"
                            :src="`${company.logo}?w=300&h=300&fit=crop`"
                            :alt="company.name"
                        />
                        <div
                            v-else
                            class="flex items-center justify-center w-full h-full overflow-hidden text-2xl font-bold border rounded-full opacity-60 border-neutral bg-neutral text-neutral-content"
                        >
                            {{ company.initials }}
                        </div>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-1 font-bold">
                        <Link :href="route('company.show', company.id)">{{
                            company.name
                        }}</Link>
                    </div>

                    <div class="text-sm">
                        <a
                            :href="company.url"
                            class="inline-flex items-center link"
                            target="_blank"
                        >
                            <span>{{ company.host }}</span>

                            <ExternalLinkIcon
                                class="w-4 h-4 ml-1"
                            ></ExternalLinkIcon>
                        </a>
                    </div>
                </div>
            </div>
        </td>

        <!-- Visit Profile -->
        <td class="w-px">
            <div class="flex items-center justify-end gap-2">
                <Link
                    :href="route('company.show', company.id)"
                    class="normal-case btn btn-secondary"
                >
                    View
                </Link>

                <Link
                    v-if="userOwnsCompany"
                    :href="route('company.edit', company.id)"
                    class="normal-case btn btn-accent flex-nowrap"
                >
                    <PencilAltIcon class="w-4 h-4 mr-1.5 -ml-2"></PencilAltIcon>
                    <span>Edit</span>
                </Link>
            </div>
        </td>
    </tr>
</template>
