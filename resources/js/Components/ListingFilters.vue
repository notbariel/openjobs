<script setup>
import { ref, computed, toRefs, watch, inject } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { abbreviateNumber } from "js-abbreviation-number";
import { throttle, join, pickBy, keys, split } from "lodash";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import { CurrencyDollarIcon, TagIcon, XIcon } from "@heroicons/vue/solid";

const emitter = inject("mitt");

const props = defineProps({
    filters: Object,
    tags: Object,
    salaryRange: Object,
    toRoute: String,
});

const { filters, tags, salaryRange, toRoute } = toRefs(props);

const hasFilters = computed(() => keys(pickBy(filters.value)).length > 0);

const selectedTags = ref(
    filters.value.tags ? split(filters.value.tags, ",") : []
);

const selectTag = (slug) => {
    if (selectedTags.value.includes(slug)) {
        selectedTags.value.splice(selectedTags.value.indexOf(slug), 1);
        return;
    }

    selectedTags.value.push(slug);
};

const selectedSalary = ref(filters.value.min_salary ?? 0);

const hideClosed = ref(filters.value.hide_closed ?? false);

const activeFilters = computed(() => {
    return {
        tags: join(selectedTags.value, ","),
        min_salary: parseInt(selectedSalary.value),
        hide_closed: hideClosed.value,
    };
});

watch(
    activeFilters,
    throttle((value, oldValue) => {
        Inertia.get(toRoute.value, pickBy(value), {
            preserveScroll: true,
            // preserveState: true,
            only: ["filters", "listings"],
        });
    }, 400)
);

const clearFilters = () => {
    selectedTags.value = [];
    selectedSalary.value = 0;
    hideClosed.value = false;
};

emitter.on("selectTag", selectTag);
emitter.on("clearFilters", clearFilters);
</script>

<template>
    <!-- Clear Filters -->
    <button
        v-if="hasFilters"
        @click="clearFilters"
        class="normal-case btn btn-sm btn-error"
    >
        <XIcon class="w-4 h-4 mr-2"></XIcon>
        <span>Clear filters</span>
    </button>

    <!-- Tags -->
    <Popover class="dropdown dropdown-end">
        <PopoverButton class="normal-case btn btn-primary btn-sm">
            <span>Filter by tags</span>
            <TagIcon class="w-4 h-4 ml-2"></TagIcon>
        </PopoverButton>

        <PopoverPanel
            class="mt-1 overflow-hidden rounded-lg shadow dropdown-content bg-base-100 text-base-content"
        >
            <ul
                class="p-2 overflow-y-auto text-sm max-h-80 w-52 menu menu-compact"
            >
                <li class="menu-title">
                    <span>Tags</span>
                </li>
                <li v-for="tag in tags.data">
                    <label>
                        <input
                            @change="selectTag(tag.slug)"
                            type="checkbox"
                            class="checkbox checkbox-primary checkbox-sm"
                            :checked="selectedTags.includes(tag.slug)"
                            :value="tag.slug"
                        />
                        <span>{{ tag.name }}</span>
                    </label>
                </li>
            </ul>
        </PopoverPanel>
    </Popover>

    <!-- Salary -->
    <Popover class="dropdown dropdown-end">
        <PopoverButton class="normal-case btn btn-primary btn-sm">
            <span>Filter by salary</span>
            <CurrencyDollarIcon class="w-4 h-4 ml-2"></CurrencyDollarIcon>
        </PopoverButton>

        <PopoverPanel
            class="mt-1 overflow-hidden rounded-lg shadow dropdown-content bg-base-100 text-base-content"
        >
            <div class="p-2 text-sm w-60">
                <div class="form-control">
                    <label class="label">
                        <div class="label-text">Min. salary</div>
                        <span>{{
                            `$${abbreviateNumber(selectedSalary, 0)}/year`
                        }}</span>
                    </label>
                    <input
                        v-model="selectedSalary"
                        type="range"
                        :min="salaryRange.min"
                        :max="salaryRange.max"
                        step="10000"
                        class="range range-primary range-sm"
                    />
                </div>
            </div>
        </PopoverPanel>
    </Popover>

    <!-- Hide Closed Listings -->
    <div class="form-control">
        <label class="gap-1 cursor-pointer label">
            <input
                type="checkbox"
                v-model="hideClosed"
                class="checkbox checkbox-primary checkbox-sm"
            />
            <span class="label-text">Hide closed</span>
        </label>
    </div>
</template>
