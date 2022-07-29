<script setup>
import { ref, onMounted, onUnmounted, toRefs } from "vue";
import { debounce } from "lodash";

const props = defineProps({
    loadMoreAction: Function,
    loadingText: {
        type: String,
        default: "Loading more items...",
    },
    threshold: {
        type: Number,
        default: 200,
    },
});

const { loadMoreAction, loadingText, threshold } = toRefs(props);

const isLoading = ref(false);

const scrollEvent = debounce(async (e) => {
    let bottomPixels =
        document.documentElement.offsetHeight -
        document.documentElement.scrollTop -
        window.innerHeight;

    if (bottomPixels < threshold.value && !isLoading.value) {
        isLoading.value = true;
        await loadMoreAction.value().finally(() => (isLoading.value = false));
    }
}, 100);

onMounted(() => {
    window.addEventListener("scroll", scrollEvent);
});

onUnmounted(() => {
    window.removeEventListener("scroll", scrollEvent);
});
</script>

<template>
    <slot :loadingText="loadingText" :isLoading="isLoading" />
</template>
