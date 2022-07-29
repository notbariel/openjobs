<script>
import AppLayout from "@/Layouts/App.vue";

export default {
    layout: AppLayout,
};
</script>

<script setup>
import { inject, computed, ref, toRefs } from "vue";
import { Head } from "@inertiajs/inertia-vue3";
import { join, pickBy, split } from "lodash";
import ListingItem from "@/Components/ListingItem.vue";
import ListingFilters from "@/Components/ListingFilters.vue";
import InfiniteScroll from "@/Components/InfiniteScroll.vue";
import { EmojiSadIcon, DownloadIcon } from "@heroicons/vue/outline";

const emitter = inject("mitt");

const props = defineProps({
    filters: Object,
    listings: Object,
    tags: Object,
    salaryRange: Object,
});

const { filters, tags, salaryRange } = toRefs(props);

const listings = ref(props.listings);

const selectedTags = ref(
    filters.value.tags ? split(filters.value.tags, ",") : []
);

const selectedSalary = ref(filters.value.min_salary ?? 0);

const hideClosed = ref(filters.value.hide_closed ?? false);

const activeFilters = computed(() => {
    return {
        tags: join(selectedTags.value, ","),
        min_salary: parseInt(selectedSalary.value),
        hide_closed: hideClosed.value,
    };
});

const loadMoreListings = async () => {
    if (!listings.value.links.next) {
        return Promise.resolve();
    }

    return axios
        .get(listings.value.links.next, {
            params: pickBy(activeFilters.value),
        })
        .then((res) => {
            listings.value = {
                ...res.data,
                data: [...listings.value.data, ...res.data.data],
            };
        });
};
</script>

<template>
    <Head title="Listings" />

    <!-- Hero Header -->
    <header class="py-8 hero">
        <div class="flex-col max-w-3xl text-center hero-content">
            <h1 class="text-5xl">Job Listings</h1>
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
                :toRoute="route('listing.index')"
            ></ListingFilters>
        </div>

        <!-- Listings Table -->
        <div class="w-full overflow-x-auto">
            <table v-if="listings.data.length > 0" class="table w-full">
                <tbody>
                    <InfiniteScroll
                        :loadMoreAction="loadMoreListings"
                        v-slot="{ isLoading, loadingText }"
                    >
                        <ListingItem
                            v-for="listing in listings.data"
                            :key="listing.id"
                            :listing="listing"
                            :filteredTags="
                                filters.tags ? split(filters.tags, ',') : []
                            "
                        ></ListingItem>

                        <tr v-if="isLoading">
                            <td
                                colspan="99"
                                class="py-10 bg-info text-info-content"
                            >
                                <div
                                    class="flex items-center justify-center gap-3"
                                >
                                    <DownloadIcon
                                        class="w-6 h-6 animate-bounce"
                                    ></DownloadIcon>
                                    <span>
                                        {{ loadingText }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </InfiniteScroll>
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
