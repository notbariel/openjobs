<script>
import AppLayout from "@/Layouts/App.vue";

export default {
    layout: AppLayout,
};
</script>

<script setup>
import { computed, toRefs } from "vue";
import { Head } from "@inertiajs/inertia-vue3";
import ListingItem from "@/Components/ListingItem.vue";

const props = defineProps({
    listing: Object,
});

const { listing } = toRefs(props);

const pageTitle = computed(() => {
    return `${listing.value.data.company.name} is hiring a ${listing.value.data.position}`;
});
</script>

<template>
    <Head :title="pageTitle" />

    <!-- Hero Header -->
    <header class="py-8 hero">
        <div class="flex-col max-w-3xl text-center hero-content">
            <h1 class="text-5xl">Job Listing</h1>
        </div>
    </header>

    <!-- Single Listing Table -->
    <div>
        <div class="w-full overflow-x-auto">
            <table class="table w-full">
                <tbody>
                    <ListingItem
                        :listing="listing.data"
                        :singleItem="true"
                    ></ListingItem>
                </tbody>
            </table>
        </div>
    </div>
</template>
