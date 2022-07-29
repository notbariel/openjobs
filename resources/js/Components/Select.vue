<script setup>
import { onMounted, ref } from "vue";

defineProps({
    modelValue: String | Number,
    items: Array | Object,
    firstItemLabel: String,
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});
</script>

<template>
    <select
        ref="input"
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="select"
    >
        <option v-if="firstItemLabel" value="" disabled selected>
            {{ firstItemLabel }}
        </option>

        <option
            v-for="(item, key) in items"
            :value="key"
            v-text="item"
        ></option>
    </select>
</template>
