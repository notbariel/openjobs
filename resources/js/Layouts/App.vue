<script setup>
import AppHeader from "@/Layouts/Partials/AppHeader.vue";
import AppFooter from "@/Layouts/Partials/AppFooter.vue";
</script>

<template>
    <div class="min-h-screen bg-base-200 text-base-content">
        <!-- Header -->
        <AppHeader></AppHeader>

        <!-- Content -->
        <main>
            <div>
                <div class="max-w-6xl px-4 py-6 mx-auto">
                    <slot />
                </div>
            </div>
        </main>

        <!-- Footer -->
        <AppFooter></AppFooter>
    </div>
</template>
