<script setup>
import { Link } from "@inertiajs/inertia-vue3";
import { useDark } from "@vueuse/core";
import { CubeIcon } from "@heroicons/vue/solid";

useDark({
    selector: "html",
    attribute: "data-theme",
    valueDark: "dark",
    valueLight: "light",
});
</script>

<template>
    <div
        class="flex flex-col items-center justify-center min-h-screen space-y-6 bg-base-200 text-base-content"
    >
        <div>
            <Link
                href="/"
                class="px-2 text-4xl font-bold lowercase hover:bg-transparent btn btn-lg btn-ghost text-primary"
            >
                <CubeIcon class="mr-1 w-14 h-14"></CubeIcon>
                <span>OpenJobs</span>
            </Link>
        </div>

        <div class="w-full max-w-md shadow-xl card bg-base-100">
            <div class="card-body">
                <slot />
            </div>
        </div>
    </div>
</template>
