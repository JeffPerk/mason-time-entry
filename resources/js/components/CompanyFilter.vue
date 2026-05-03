<template>
    <label class="flex items-center gap-3">
        <span class="text-sm font-medium text-slate-700">
            Company
        </span>

        <select
            :value="modelValue"
            class="min-w-64 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option value="">All</option>

            <option
                v-for="company in companies"
                :key="company.id"
                :value="company.id"
            >
                {{ company.name }}
            </option>
        </select>
    </label>
</template>

<script setup>
defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    companies: {
        type: Array,
        required: true,
    },
});

defineEmits(['update:modelValue']);
</script>