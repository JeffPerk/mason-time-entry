<template>
    <label class="flex items-center gap-3">
        <span
            class="text-sm font-semibold"
            :class="variant === 'dark' ? 'text-violet-100' : 'text-slate-700'"
        >
            Company
        </span>

        <select
            :value="modelValue"
            class="min-w-64 rounded-xl border px-3 py-2 text-sm shadow-sm outline-none transition focus:ring-2"
            :class="variant === 'dark'
                ? 'border-white/10 bg-white/95 text-slate-950 focus:border-violet-300 focus:ring-violet-300/30'
                : 'border-slate-300 bg-white text-slate-900 focus:border-slate-500 focus:ring-slate-200'"
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
    variant: {
        type: String,
        default: 'light',
    },
});

defineEmits(['update:modelValue']);
</script>