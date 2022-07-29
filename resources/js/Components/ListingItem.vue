<script setup>
import { ref, toRefs, computed, onMounted, inject } from "vue";
import { useClipboard } from "@vueuse/core";
import { abbreviateNumber } from "js-abbreviation-number";
import * as dayjs from "dayjs";
import * as relativeTime from "dayjs/plugin/relativeTime";
import * as updateLocale from "dayjs/plugin/updateLocale";
import { Link, usePage } from "@inertiajs/inertia-vue3";
import { CursorClickIcon } from "@heroicons/vue/outline";
import {
    ExternalLinkIcon,
    ExclamationCircleIcon,
    PencilAltIcon,
    TrashIcon,
} from "@heroicons/vue/solid";

dayjs.extend(relativeTime);
dayjs.extend(updateLocale);

dayjs.updateLocale("en", {
    relativeTime: {
        future: "in %s",
        past: "%s ago",
        s: "%ds",
        m: "1m",
        mm: "%dm",
        h: "1h",
        hh: "%dh",
        d: "1d",
        dd: "%dd",
        M: "1mo",
        MM: "%dmo",
        y: "1y",
        yy: "%dy",
    },
});

const { copy, copied, isSupported } = useClipboard();

const emitter = inject("mitt");

const props = defineProps({
    listing: Object,
    singleItem: {
        type: Boolean,
        default: false,
    },
    filteredTags: {
        type: Array,
        default: [],
    },
});

const { listing, singleItem, filteredTags } = toRefs(props);

const userOwnsListing = computed(() => {
    let pageProps = usePage().props.value;
    return pageProps.auth.user
        ? pageProps.owned.listings.includes(listing.value.id)
        : false;
});

const hasLogo = computed(() => listing.value.company.logo !== null);

const paidAt = computed(() => {
    return listing.value.paid_at
        ? dayjs().from(dayjs(listing.value.paid_at), true)
        : null;
});

const salaryRange = computed(() => {
    return {
        min: abbreviateNumber(listing.value.min_annual_salary, 0),
        max: abbreviateNumber(listing.value.max_annual_salary, 0),
    };
});

const clicksCount = computed(() =>
    abbreviateNumber(listing.value.clicks_count, 2)
);

const isShowingDescription = ref(false);

const toggleDescription = () => {
    if (singleItem.value) {
        if (!isShowingDescription.value) {
            isShowingDescription.value = true;
        }

        return;
    }

    isShowingDescription.value = !isShowingDescription.value;
};

const renderApplyButton = computed(() => listing.value.is_closed !== true);

onMounted(() => {
    if (singleItem.value) {
        isShowingDescription.value = true;
    }
});
</script>

<template>
    <!-- Item Row -->
    <tr
        @click="toggleDescription"
        class="group"
        :class="{
            'cursor-pointer': !singleItem,
        }"
    >
        <!-- Company/Listing Info -->
        <td
            class="whitespace-normal"
            :class="{
                'bg-warning text-warning-content': listing.is_highlighted,
            }"
        >
            <div class="flex items-center gap-3">
                <div class="avatar">
                    <div class="w-12 h-12 mask mask-circle">
                        <img
                            v-if="hasLogo"
                            :src="`${listing.company.logo}?w=300&h=300&fit=crop`"
                            :alt="listing.company.name"
                        />
                        <div
                            v-else
                            class="flex items-center justify-center w-full h-full overflow-hidden text-2xl font-bold border rounded-full opacity-60 border-neutral bg-neutral text-neutral-content"
                        >
                            {{ listing.company.initials }}
                        </div>
                    </div>
                </div>

                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-1 font-bold">
                        <Link
                            @click.stop
                            :href="route('listing.show', listing.id)"
                            >{{ listing.position }}</Link
                        >
                        <span
                            v-if="listing.is_closed"
                            class="badge-sm badge badge-error"
                        >
                            Closed
                        </span>
                    </div>

                    <div class="text-sm">
                        <span>{{ listing.company.name }}</span>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-2 mt-1 text-sm font-bold"
                    >
                        <div class="w-auto gap-1 badge shrink-0 badge-ghost">
                            <span class="text-xs">🌏</span>
                            <span>{{ listing.location }}</span>
                        </div>

                        <div class="w-auto gap-1 badge shrink-0 badge-ghost">
                            <span class="text-xs">💰</span>
                            <span>{{
                                `$${salaryRange.min} - $${salaryRange.max}`
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </td>

        <!-- Listing Tags -->
        <td
            class="hidden md:table-cell"
            :class="{
                'bg-warning text-warning-content': listing.is_highlighted,
            }"
        >
            <ul class="flex flex-wrap items-center gap-2">
                <li v-for="tag in listing.tags" :key="tag.id">
                    <div
                        v-if="!singleItem"
                        class="tooltip"
                        :data-tip="
                            !filteredTags.includes(tag.slug)
                                ? 'Add to filters'
                                : 'Remove from filters'
                        "
                    >
                        <button
                            type="button"
                            @click.stop="emitter.emit('selectTag', tag.slug)"
                            class="btn btn-xs"
                            :class="{
                                'btn-outline': !filteredTags.includes(tag.slug),
                            }"
                        >
                            {{ tag.name }}
                        </button>
                    </div>

                    <button
                        v-else
                        type="button"
                        class="btn btn-xs no-animation"
                        :class="{
                            'btn-outline': !filteredTags.includes(tag.slug),
                        }"
                    >
                        {{ tag.name }}
                    </button>
                </li>
            </ul>
        </td>

        <!-- Pin Status/Paid at Time -->
        <td
            class="hidden w-px lg:table-cell"
            :class="{
                'bg-warning text-warning-content': listing.is_highlighted,
            }"
        >
            <div class="flex items-center gap-2 text-sm">
                <span v-if="listing.is_pinned">📌</span>
                <span>{{ paidAt }}</span>
            </div>
        </td>

        <!-- Apply Button -->
        <th
            class="w-px"
            :class="{
                'bg-warning text-warning-content': listing.is_highlighted,
            }"
        >
            <div v-if="!userOwnsListing">
                <a
                    v-if="renderApplyButton"
                    @click.stop
                    :href="route('listing.apply', listing.id)"
                    class="normal-case btn-secondary btn btn-block group-hover:visible"
                    :class="[isShowingDescription ? 'visible' : 'lg:invisible']"
                >
                    Apply
                </a>
            </div>

            <div v-else>
                <Link
                    @click.stop
                    :href="route('listing.edit', listing.id)"
                    class="normal-case btn btn-accent btn-block flex-nowrap"
                >
                    <PencilAltIcon class="w-4 h-4 mr-1.5 -ml-2"></PencilAltIcon>
                    <span>Edit</span>
                </Link>
            </div>
        </th>
    </tr>

    <!-- Description Row -->
    <tr v-if="isShowingDescription">
        <td colspan="99" class="whitespace-normal">
            <div
                class="flex flex-col max-w-screen-lg gap-12 px-6 py-6 mx-auto lg:px-2 lg:flex-row"
            >
                <!-- Description -->
                <div class="lg:w-3/5">
                    <div
                        v-if="listing.is_closed"
                        class="mb-4 shadow-lg alert alert-error"
                    >
                        <div>
                            <ExclamationCircleIcon
                                class="w-6 h-6"
                            ></ExclamationCircleIcon>

                            <span
                                >This listing is closed. Please do not
                                apply.</span
                            >
                        </div>
                    </div>

                    <div class="prose max-w-none">
                        <h1 class="font-normal leading-normal not-prose">
                            {{ listing.company.name }} is hiring a
                            <strong>{{ listing.position }}</strong>
                        </h1>

                        <div v-html="listing.description"></div>

                        <h3>Salary and compensation</h3>
                        <p>
                            {{
                                `$${salaryRange.min} - $${salaryRange.max}/year`
                            }}
                        </p>

                        <h3>Location</h3>
                        <p>
                            {{ listing.location }}
                        </p>
                    </div>
                </div>

                <!-- Company Card -->
                <div class="lg:w-2/5">
                    <div class="w-full max-w-lg mx-auto card bg-base-200">
                        <div class="items-center text-center card-body">
                            <div class="avatar">
                                <div class="w-52 h-52 mask mask-circle">
                                    <Link
                                        :href="
                                            route(
                                                'company.show',
                                                listing.company.id
                                            )
                                        "
                                    >
                                        <img
                                            v-if="hasLogo"
                                            :src="`${listing.company.logo}?w=600&h=600&fit=crop`"
                                            :alt="listing.company.name"
                                        />
                                        <div
                                            v-else
                                            class="flex items-center justify-center w-full h-full overflow-hidden font-bold border rounded-full text-7xl opacity-60 border-neutral bg-neutral text-neutral-content"
                                        >
                                            {{ listing.company.initials }}
                                        </div>
                                    </Link>
                                </div>
                            </div>

                            <h2 class="mt-2 text-2xl card-title">
                                <Link
                                    :href="
                                        route(
                                            'company.show',
                                            listing.company.id
                                        )
                                    "
                                >
                                    {{ listing.company.name }}
                                </Link>
                            </h2>

                            <div>
                                <a
                                    :href="listing.company.url"
                                    class="flex items-center link"
                                    target="_blank"
                                >
                                    <span>{{ listing.company.host }}</span>

                                    <ExternalLinkIcon
                                        class="w-4 h-4 ml-1"
                                    ></ExternalLinkIcon>
                                </a>
                            </div>

                            <div class="bg-transparent stats">
                                <div class="stat">
                                    <div
                                        class="relative stat-value text-primary"
                                    >
                                        <div
                                            class="absolute inset-y-0 flex items-center -left-6"
                                        >
                                            <CursorClickIcon
                                                class="w-6 h-6"
                                            ></CursorClickIcon>
                                        </div>
                                        <span>{{ clicksCount }}</span>
                                    </div>
                                    <div class="stat-desc">Total clicks</div>
                                </div>
                            </div>

                            <div
                                v-if="renderApplyButton"
                                class="mb-2 card-actions"
                            >
                                <a
                                    :href="route('listing.apply', listing.id)"
                                    class="normal-case btn btn-secondary"
                                >
                                    Apply now
                                </a>
                            </div>

                            <div class="form-control">
                                <label class="justify-center font-bold label">
                                    <span class="label-text"
                                        >Share this listing:</span
                                    >
                                </label>
                                <div :class="{ 'input-group': isSupported }">
                                    <input
                                        type="text"
                                        readonly
                                        class="w-full min-w-0 input input-bordered"
                                        :value="
                                            route('listing.show', listing.id)
                                        "
                                    />
                                    <button
                                        v-if="isSupported"
                                        @click="
                                            copy(
                                                route(
                                                    'listing.show',
                                                    listing.id
                                                )
                                            )
                                        "
                                        type="button"
                                        class="normal-case btn"
                                        :class="{ 'btn-success': copied }"
                                    >
                                        {{ copied ? `Copied!` : `Copy` }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
</template>
