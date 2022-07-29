<script setup>
defineProps({
    text: String,
    altText: String,
    labelFirst: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <label class="label">
        <slot v-if="!labelFirst"></slot>
        <span v-if="text" class="label-text text-inherit">{{ text }}</span>
        <span v-if="altText" class="label-text-alt text-inherit">{{
            altText
        }}</span>
        <slot v-if="labelFirst"></slot>
    </label>
</template>
