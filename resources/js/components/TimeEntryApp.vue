<template>
    <main class="min-h-screen bg-slate-100 text-slate-900">
        <div class="mx-auto max-w-7xl px-6 py-8">
            <header class="flex flex-col gap-6 border-b border-slate-200 pb-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">
                        Mason Take-Home Project
                    </p>

                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">
                        Employee Time Entries
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                        Create validated employee time entries and review submitted history by company.
                    </p>
                </div>

                <CompanyFilter
                    v-model="selectedCompanyId"
                    :companies="companies"
                />
            </header>

            <section
                v-if="loadError"
                class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ loadError }}
            </section>

            <section
                v-else
                class="mt-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <nav
                        aria-label="Tabs"
                        class="inline-flex rounded-xl border border-slate-200 bg-white p-1 shadow-sm"
                    >
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-sm font-medium transition"
                            :class="activeTab === 'new'
                                ? 'bg-slate-900 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                            @click="activeTab = 'new'"
                        >
                            New Entries
                        </button>

                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-sm font-medium transition"
                            :class="activeTab === 'history'
                                ? 'bg-slate-900 text-white shadow-sm'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                            @click="activeTab = 'history'"
                        >
                            History
                        </button>
                    </nav>

                    <p class="text-sm text-slate-500">
                        <span v-if="isLoading">Loading companies...</span>
                        <span v-else>{{ companies.length }} companies available</span>
                    </p>
                </div>

                <div class="mt-6">
                    <NewEntriesTab
                        v-if="activeTab === 'new'"
                        :selected-company-id="selectedCompanyId"
                        :selected-company-name="selectedCompanyName"
                        :companies="companies"
                        @entries-created="handleEntriesCreated"
                    />

                    <HistoryTab
                        v-else
                        :selected-company-id="selectedCompanyId"
                        :selected-company-name="selectedCompanyName"
                        :refresh-token="historyRefreshToken"
                    />
                </div>
            </section>
        </div>
    </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import CompanyFilter from './CompanyFilter.vue';
import HistoryTab from './HistoryTab.vue';
import NewEntriesTab from './NewEntriesTab.vue';

const companies = ref([]);
const selectedCompanyId = ref('');
const activeTab = ref('new');
const isLoading = ref(false);
const loadError = ref('');
const historyRefreshToken = ref(0);

const selectedCompanyName = computed(() => {
    if (!selectedCompanyId.value) {
        return '';
    }

    const selectedCompany = companies.value.find((company) => {
        return String(company.id) === String(selectedCompanyId.value);
    });

    return selectedCompany?.name ?? '';
});

async function loadCompanies() {
    isLoading.value = true;
    loadError.value = '';

    try {
        const response = await fetch('/api/companies', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load companies.');
        }

        const payload = await response.json();

        companies.value = payload.data ?? [];
    } catch (error) {
        loadError.value = error.message || 'Something went wrong while loading companies.';
    } finally {
        isLoading.value = false;
    }
}

function handleEntriesCreated() {
    historyRefreshToken.value += 1;
}

onMounted(() => {
    loadCompanies();
});
</script>