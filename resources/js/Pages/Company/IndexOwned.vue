<script>
import AppLayout from "@/Layouts/App.vue";

export default {
    layout: AppLayout,
};
</script>

<script setup>
import { ref, toRefs } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head, Link } from "@inertiajs/inertia-vue3";
import { pickBy, throttle } from "lodash";
import CompanyItem from "@/Components/CompanyItem.vue";
import InfiniteScroll from "@/Components/InfiniteScroll.vue";
import {
    EmojiSadIcon,
    SearchIcon,
    DownloadIcon,
    PlusIcon,
} from "@heroicons/vue/solid";

const props = defineProps({
    q: String,
    companies: Object,
});

const { q } = toRefs(props);

const companies = ref(props.companies);

const searchTerm = ref(q.value);

const searchCompany = () =>
    throttle(() => {
        Inertia.get(
            route("company.indexOwned"),
            pickBy({ q: searchTerm.value }),
            {
                preserveScroll: true,
                // preserveState: true,
            }
        );
    }, 200);

const loadMoreCompanies = async () => {
    if (!companies.value.links.next) {
        return Promise.resolve();
    }

    return axios
        .get(companies.value.links.next, {
            params: pickBy({ q: searchTerm.value }),
        })
        .then((res) => {
            companies.value = {
                ...res.data,
                data: [...companies.value.data, ...res.data.data],
            };
        });
};
</script>

<template>
    <Head title="Companies" />

    <!-- Hero Header -->
    <header class="py-8 hero">
        <div class="flex-col max-w-3xl text-center hero-content">
            <h1 class="text-5xl">My Companies</h1>
        </div>
    </header>

    <div>
        <!-- Search Form -->
        <div
            class="flex flex-col flex-wrap items-center justify-end gap-3 mb-3 sm:flex-row"
        >
            <Link
                :href="route('company.create')"
                class="normal-case sm:mr-auto btn btn-sm btn-secondary"
            >
                <PlusIcon class="w-4 h-4 mr-1.5 -ml-1"></PlusIcon>
                New company
            </Link>

            <form @submit.prevent="searchCompany">
                <div class="form-control">
                    <div class="input-group input-group-sm">
                        <input
                            type="text"
                            placeholder="Search…"
                            class="input input-sm input-bordered"
                            v-model="searchTerm"
                        />
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm btn-square"
                        >
                            <SearchIcon class="w-5 h-5"></SearchIcon>
                            <span class="sr-only">Search</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Companies Table -->
        <div class="w-full overflow-x-auto">
            <table v-if="companies.data.length > 0" class="table w-full">
                <tbody>
                    <InfiniteScroll
                        :loadMoreAction="loadMoreCompanies"
                        v-slot="{ isLoading, loadingText }"
                    >
                        <CompanyItem
                            v-for="company in companies.data"
                            :key="company.id"
                            :company="company"
                        ></CompanyItem>

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
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
