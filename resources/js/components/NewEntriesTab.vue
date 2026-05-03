<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    New Entries
                </h2>

                <p class="mt-1 text-sm text-slate-600">
                    Enter one or more employee time entries.
                </p>
            </div>

            <div
                v-if="selectedCompanyName"
                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
            >
                {{ selectedCompanyName }}
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
            The editable time entry table will go here next.
        </div>
    </section>
</template>

<script setup>
defineProps({
    selectedCompanyId: {
        type: String,
        required: true,
    },
    selectedCompanyName: {
        type: String,
        default: '',
    },
});
</script>