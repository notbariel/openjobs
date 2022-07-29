<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/inertia-vue3";
import { useDark, useToggle } from "@vueuse/core";
import { PlusIcon, CubeIcon, MenuIcon } from "@heroicons/vue/outline";
import {
    UserCircleIcon,
    MenuAlt1Icon,
    FlagIcon,
    ChevronDownIcon,
    LogoutIcon,
    LoginIcon,
    SunIcon,
    MoonIcon,
    XIcon,
} from "@heroicons/vue/solid";

const user = computed(() => usePage().props.value.auth.user);
const userIsLoggedIn = computed(() => user.value !== null);

const mobileMenuIsOpen = ref(false);

const isDarkMode = useDark({
    selector: "html",
    attribute: "data-theme",
    valueDark: "dark",
    valueLight: "light",
});

const toggleDarkMode = useToggle(isDarkMode);
</script>
<template>
    <header class="relative shadow bg-base-100 text-base-content">
        <!-- Main Menu -->
        <nav class="navbar">
            <div class="gap-2 navbar-start">
                <Link
                    :href="route('home')"
                    class="px-2 text-2xl font-bold lowercase hover:bg-transparent btn btn-ghost text-primary"
                >
                    <CubeIcon class="w-10 h-10 mr-1"></CubeIcon>
                    <span>OpenJobs</span>
                </Link>

                <ul
                    class="hidden gap-2 p-0 menu menu-horizontal md:inline-flex"
                >
                    <li>
                        <Link :href="route('listing.index')">
                            <MenuAlt1Icon class="w-4 h-4"></MenuAlt1Icon>
                            <span>Listings</span>
                        </Link>
                    </li>

                    <li>
                        <Link :href="route('company.index')">
                            <FlagIcon class="w-4 h-4"></FlagIcon>
                            <span>Companies</span>
                        </Link>
                    </li>
                </ul>
            </div>

            <div class="gap-2 navbar-end">
                <button
                    @click="toggleDarkMode()"
                    type="button"
                    class="normal-case btn btn-ghost btn-circle"
                >
                    <div class="swap" :class="{ 'swap-active': !isDarkMode }">
                        <div class="swap-on">
                            <MoonIcon class="w-6 h-6"></MoonIcon>
                        </div>
                        <div class="swap-off">
                            <SunIcon class="w-6 h-6"></SunIcon>
                        </div>
                    </div>
                    <span class="sr-only">Toggle dark mode</span>
                </button>

                <div class="hidden gap-2 lg:inline-flex">
                    <Link
                        :href="route('listing.create')"
                        class="normal-case btn btn-secondary"
                    >
                        <PlusIcon class="w-4 h-4 mr-1.5 -ml-2"></PlusIcon>
                        <span>Post a job</span>
                    </Link>

                    <template v-if="userIsLoggedIn">
                        <div class="dropdown dropdown-end">
                            <label
                                tabindex="0"
                                class="normal-case btn btn-outline"
                            >
                                <span>My Properties</span>
                                <ChevronDownIcon
                                    class="w-4 h-4 ml-1.5 -mr-2"
                                ></ChevronDownIcon>
                            </label>
                            <ul
                                tabindex="0"
                                class="p-2 mt-1 rounded-lg shadow dropdown-content menu bg-base-100 w-44"
                            >
                                <li>
                                    <Link :href="route('listing.indexOwned')">
                                        <MenuAlt1Icon
                                            class="w-4 h-4"
                                        ></MenuAlt1Icon>
                                        <span>Listings</span>
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="route('company.indexOwned')">
                                        <FlagIcon class="w-4 h-4"></FlagIcon>
                                        <span>Companies</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <div class="dropdown dropdown-end">
                            <label
                                tabindex="0"
                                class="btn btn-ghost btn-circle avatar"
                            >
                                <div class="w-10 rounded-full">
                                    <UserCircleIcon
                                        class="w-10 h-10"
                                    ></UserCircleIcon>
                                    <span class="sr-only">{{ user.name }}</span>
                                </div>
                            </label>
                            <ul
                                tabindex="0"
                                class="p-2 mt-1 rounded-lg shadow menu dropdown-content bg-base-100 w-44"
                            >
                                <li class="truncate menu-title">
                                    <span>{{ user.name }}</span>
                                </li>
                                <li>
                                    <Link
                                        as="button"
                                        method="post"
                                        :href="route('logout')"
                                    >
                                        <LogoutIcon
                                            class="w-4 h-4"
                                        ></LogoutIcon>
                                        <span>Sign out</span>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </template>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="normal-case btn btn-outline"
                        >
                            <LoginIcon class="w-4 h-4 mr-1.5"></LoginIcon>
                            <span>Log in</span>
                        </Link>
                    </template>
                </div>

                <div class="lg:hidden">
                    <button
                        @click="mobileMenuIsOpen = !mobileMenuIsOpen"
                        type="button"
                        class="normal-case btn btn-outline"
                    >
                        <MenuIcon
                            class="w-6 h-6"
                            v-if="!mobileMenuIsOpen"
                        ></MenuIcon>
                        <XIcon class="w-6 h-6" v-else></XIcon>
                        <span class="sr-only">Toggle menu</span>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <nav v-if="mobileMenuIsOpen" class="w-full menu lg:hidden">
            <ul>
                <li>
                    <Link
                        :href="route('listing.index')"
                        :class="{ active: route().current('listing.index') }"
                    >
                        <MenuAlt1Icon class="w-4 h-4"></MenuAlt1Icon>
                        Listings
                    </Link>
                </li>

                <li>
                    <Link
                        :href="route('company.index')"
                        :class="{ active: route().current('company.index') }"
                    >
                        <FlagIcon class="w-4 h-4"></FlagIcon>
                        Companies
                    </Link>
                </li>

                <li>
                    <Link
                        :href="route('listing.create')"
                        :class="{ active: route().current('listing.create') }"
                    >
                        <PlusIcon class="w-4 h-4"></PlusIcon>
                        Post a job
                    </Link>
                </li>

                <template v-if="userIsLoggedIn">
                    <li class="menu-title">
                        <span>My Properties</span>
                    </li>
                    <li>
                        <Link
                            :href="route('listing.indexOwned')"
                            :class="{
                                active: route().current('listing.indexOwned'),
                            }"
                        >
                            <MenuAlt1Icon class="w-4 h-4"></MenuAlt1Icon>
                            My Listings
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('company.indexOwned')"
                            :class="{
                                active: route().current('company.indexOwned'),
                            }"
                        >
                            <FlagIcon class="w-4 h-4"></FlagIcon>
                            My Companies
                        </Link>
                    </li>
                    <li class="menu-title">
                        <span>{{ user.name }}</span>
                    </li>
                    <li>
                        <Link as="button" method="post" :href="route('logout')">
                            <LogoutIcon class="w-4 h-4"></LogoutIcon>
                            Sign out
                        </Link>
                    </li>
                </template>

                <template v-else>
                    <li>
                        <Link :href="route('login')">
                            <LoginIcon class="w-4 h-4"></LoginIcon>
                            Log in
                        </Link>
                    </li>
                </template>
            </ul>
        </nav>
    </header>
</template>
