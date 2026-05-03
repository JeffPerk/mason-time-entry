<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    History
                </h2>

                <p class="mt-1 text-sm text-slate-600">
                    View previously submitted time entries.
                </p>
            </div>

            <div
                v-if="selectedCompanyName"
                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
            >
                Filtered by {{ selectedCompanyName }}
            </div>

            <div
                v-else
                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
            >
                Showing all companies
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-600">
            The history table will go here after the entry form is working.
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