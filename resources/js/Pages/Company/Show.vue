<script>
import AppLayout from "@/Layouts/App.vue";

export default {
    layout: AppLayout,
};
</script>

<script setup>
import { computed, inject, toRefs } from "vue";
import { Head } from "@inertiajs/inertia-vue3";
import { split } from "lodash";
import ListingItem from "@/Components/ListingItem.vue";
import ListingFilters from "@/Components/ListingFilters.vue";
import { EmojiSadIcon, ExternalLinkIcon } from "@heroicons/vue/outline";

const emitter = inject("mitt");

const props = defineProps({
    company: Object,
    filters: Object,
    listings: Object,
    tags: Object,
    salaryRange: Object,
});

const { company, filters, listings, tags, salaryRange } = toRefs(props);

const hasLogo = computed(() => company.value.data.logo !== null);
</script>

<template>
    <Head :title="company.data.name" />

    <!-- Hero Header -->
    <header class="py-8 hero">
        <div class="flex-col max-w-3xl text-center md:text-left hero-content">
            <div class="flex flex-col items-center gap-4 md:flex-row">
                <div class="self-center avatar">
                    <div class="w-44 h-44 mask mask-circle">
                        <img
                            v-if="hasLogo"
                            :src="`${company.data.logo}?w=600&h=600&fit=crop`"
                            :alt="company.data.name"
                        />
                        <div
                            v-else
                            class="flex items-center justify-center w-full h-full overflow-hidden text-6xl font-bold border rounded-full opacity-60 border-neutral bg-neutral text-neutral-content"
                        >
                            {{ company.data.initials }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <h1 class="text-5xl">{{ company.data.name }}</h1>
                    <p class="opacity-70">
                        {{ company.data.description }}
                    </p>
                    <p>
                        <a
                            :href="company.data.url"
                            class="inline-flex items-center link"
                            target="_blank"
                        >
                            <span>{{ company.data.host }}</span>

                            <ExternalLinkIcon
                                class="w-4 h-4 ml-1"
                            ></ExternalLinkIcon>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </header>

    <div>
        <!-- Filter -->
        <div
            class="flex flex-col flex-wrap items-center justify-end gap-3 mb-3 sm:flex-row"
        >
            <ListingFilters
                :filters="filters"
                :tags="tags"
                :salaryRange="salaryRange"
                :toRoute="route('company.show', company.data.id)"
            ></ListingFilters>
        </div>

        <!-- Listings Table -->
        <div class="w-full overflow-x-auto">
            <table v-if="listings.data.length > 0" class="table w-full">
                <tbody>
                    <ListingItem
                        v-for="listing in listings.data"
                        :key="listing.id"
                        :listing="listing"
                        :filteredTags="
                            filters.tags ? split(filters.tags, ',') : []
                        "
                    ></ListingItem>
                </tbody>
            </table>

            <div v-else class="my-12">
                <div
                    class="w-full max-w-2xl mx-auto card bg-base-300 text-base-content"
                >
                    <figure class="px-10 pt-10">
                        <EmojiSadIcon class="w-24 h-24"></EmojiSadIcon>
                    </figure>
                    <div class="items-center text-center card-body">
                        <h2 class="card-title">There's nothing here.</h2>
                        <p>
                            Try
                            <span
                                @click="emitter.emit('clearFilters')"
                                class="link"
                                >clearing your filters</span
                            >.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
