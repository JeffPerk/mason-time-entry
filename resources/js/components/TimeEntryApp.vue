<template>
    <main class="min-h-screen bg-slate-950 text-slate-900">
        <div class="bg-[radial-gradient(circle_at_top_right,_rgba(124,58,237,0.28),_transparent_34%),linear-gradient(135deg,_#020617_0%,_#111827_45%,_#312e81_100%)]">
            <div class="mx-auto max-w-7xl px-6 py-8">
                <header class="flex flex-col gap-6 pb-8 md:flex-row md:items-end md:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-violet-100 shadow-sm backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-violet-300"></span>
                            Mason Take-Home Project
                        </div>

                        <h1 class="mt-4 text-3xl font-bold tracking-tight text-white md:text-4xl">
                            Employee Time Entries
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                            Create validated employee time entries, review submitted history, and move quickly through repeated entry workflows.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 shadow-xl backdrop-blur">
                        <CompanyFilter
                            v-model="selectedCompanyId"
                            :companies="companies"
                            variant="dark"
                        />
                    </div>
                </header>

                <section
                    v-if="loadError"
                    class="rounded-xl border border-red-300/40 bg-red-500/10 px-4 py-3 text-sm text-red-100"
                >
                    {{ loadError }}
                </section>

                <section
                    v-else
                    class="space-y-6"
                >
                    <section class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <SummaryCard
                            label="Total entries"
                            :value="summary.total_entries"
                            helper="Submitted rows"
                        />

                        <SummaryCard
                            label="Total hours"
                            :value="formatHours(summary.total_hours)"
                            helper="Visible filter scope"
                        />

                        <SummaryCard
                            label="Employees"
                            :value="summary.employee_count"
                            helper="Represented in history"
                        />

                        <SummaryCard
                            label="Projects"
                            :value="summary.project_count"
                            :helper="summary.latest_entry_date ? `Latest: ${formatDate(summary.latest_entry_date)}` : 'No entries yet'"
                        />
                    </section>

                    <section class="rounded-3xl border border-slate-200 bg-slate-100 p-4 shadow-2xl shadow-violet-950/20 md:p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <nav
                                aria-label="Tabs"
                                class="inline-flex rounded-2xl border border-slate-200 bg-white p-1 shadow-sm"
                            >
                                <button
                                    type="button"
                                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="activeTab === 'new'
                                        ? 'bg-violet-700 text-white shadow-sm'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'"
                                    @click="activeTab = 'new'"
                                >
                                    New Entries
                                </button>

                                <button
                                    type="button"
                                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                                    :class="activeTab === 'history'
                                        ? 'bg-violet-700 text-white shadow-sm'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'"
                                    @click="activeTab = 'history'"
                                >
                                    History
                                </button>
                            </nav>

                            <div class="flex flex-col gap-2 text-sm text-slate-500 md:items-end">
                                <span v-if="isLoading">Loading companies...</span>
                                <span v-else>{{ companies.length }} companies available</span>

                                <span class="text-xs">
                                    {{ selectedCompanyName ? `Filtered by ${selectedCompanyName}` : 'Viewing all companies' }}
                                </span>
                            </div>
                        </div>

                        <KeyboardShortcutHelp
                            class="mt-5"
                            @add-row="shortcutSignal += 1"
                            @duplicate-row="duplicateSignal += 1"
                            @submit="submitSignal += 1"
                            @clear-messages="clearSignal += 1"
                        />

                        <div class="mt-6">
                            <NewEntriesTab
                                v-if="activeTab === 'new'"
                                :selected-company-id="selectedCompanyId"
                                :selected-company-name="selectedCompanyName"
                                :companies="companies"
                                :add-row-signal="shortcutSignal"
                                :duplicate-row-signal="duplicateSignal"
                                :submit-signal="submitSignal"
                                :clear-signal="clearSignal"
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
                </section>
            </div>
        </div>
    </main>
</template>

<script setup>
import { computed, h, onMounted, onUnmounted, ref, watch } from 'vue';
import CompanyFilter from './CompanyFilter.vue';
import HistoryTab from './HistoryTab.vue';
import NewEntriesTab from './NewEntriesTab.vue';

const companies = ref([]);
const selectedCompanyId = ref('');
const activeTab = ref('new');
const isLoading = ref(false);
const loadError = ref('');
const historyRefreshToken = ref(0);

const shortcutSignal = ref(0);
const duplicateSignal = ref(0);
const submitSignal = ref(0);
const clearSignal = ref(0);

const summary = ref({
    total_entries: 0,
    total_hours: 0,
    employee_count: 0,
    project_count: 0,
    latest_entry_date: null,
});

const selectedCompanyName = computed(() => {
    if (!selectedCompanyId.value) {
        return '';
    }

    const selectedCompany = companies.value.find((company) => {
        return String(company.id) === String(selectedCompanyId.value);
    });

    return selectedCompany?.name ?? '';
});

const SummaryCard = {
    props: {
        label: {
            type: String,
            required: true,
        },
        value: {
            type: [String, Number],
            required: true,
        },
        helper: {
            type: String,
            default: '',
        },
    },
    setup(props) {
        return () => h('div', {
            class: 'rounded-2xl border border-white/10 bg-white/10 p-5 text-white shadow-xl backdrop-blur',
        }, [
            h('p', { class: 'text-xs font-semibold uppercase tracking-wide text-violet-100' }, props.label),
            h('p', { class: 'mt-3 text-3xl font-bold tracking-tight' }, String(props.value ?? 0)),
            h('p', { class: 'mt-1 text-xs text-slate-300' }, props.helper),
        ]);
    },
};

const KeyboardShortcutHelp = {
    emits: ['add-row', 'duplicate-row', 'submit', 'clear-messages'],
    setup(_, { emit }) {
        function handleKeydown(event) {
            const modifier = event.metaKey || event.ctrlKey;

            if (!modifier) {
                if (event.key === 'Escape') {
                    emit('clear-messages');
                }

                return;
            }

            const key = event.key.toLowerCase();

            if (key === 'enter') {
                event.preventDefault();
                emit('submit');
                return;
            }

            if (event.shiftKey && key === 'a') {
                event.preventDefault();
                emit('add-row');
                return;
            }

            if (event.shiftKey && key === 'd') {
                event.preventDefault();
                emit('duplicate-row');
            }
        }

        onMounted(() => {
            window.addEventListener('keydown', handleKeydown);
        });

        onUnmounted(() => {
            window.removeEventListener('keydown', handleKeydown);
        });

        return () => h('div', {
            class: 'rounded-2xl border border-violet-200 bg-white px-4 py-3 shadow-sm',
        }, [
            h('div', { class: 'flex flex-col gap-3 md:flex-row md:items-center md:justify-between' }, [
                h('div', [
                    h('p', { class: 'text-sm font-semibold text-slate-900' }, 'Keyboard shortcuts'),
                    h('p', { class: 'mt-1 text-xs text-slate-500' }, 'Designed for faster repeated time entry.'),
                ]),
                h('div', { class: 'flex flex-wrap gap-2 text-xs text-slate-600' }, [
                    shortcutBadge('Ctrl/Cmd + Enter', 'Submit'),
                    shortcutBadge('Ctrl/Cmd + Shift + A', 'Add row'),
                    shortcutBadge('Ctrl/Cmd + Shift + D', 'Duplicate last row'),
                    shortcutBadge('Esc', 'Clear messages'),
                ]),
            ]),
        ]);
    },
};

function shortcutBadge(keys, label) {
    return h('span', {
        class: 'inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1',
    }, [
        h('kbd', { class: 'font-mono text-[11px] font-semibold text-violet-700' }, keys),
        h('span', label),
    ]);
}

function formatHours(value) {
    const hours = Number.parseFloat(value);

    if (Number.isNaN(hours)) {
        return '0.00';
    }

    return hours.toFixed(2);
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    const [year, month, day] = value.split('-');

    return `${month}/${day}/${year}`;
}

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

async function loadSummary() {
    const params = new URLSearchParams();

    if (selectedCompanyId.value) {
        params.set('company_id', selectedCompanyId.value);
    }

    const queryString = params.toString();
    const url = queryString
        ? `/api/time-entries/summary?${queryString}`
        : '/api/time-entries/summary';

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load summary.');
        }

        const payload = await response.json();

        summary.value = {
            total_entries: payload.data?.total_entries ?? 0,
            total_hours: payload.data?.total_hours ?? 0,
            employee_count: payload.data?.employee_count ?? 0,
            project_count: payload.data?.project_count ?? 0,
            latest_entry_date: payload.data?.latest_entry_date ?? null,
        };
    } catch (error) {
        // Keep this quiet so the main app remains usable even if summary fails.
        console.error(error);
    }
}

function handleEntriesCreated() {
    historyRefreshToken.value += 1;
    loadSummary();
}

watch(selectedCompanyId, () => {
    loadSummary();
});

onMounted(() => {
    loadCompanies();
    loadSummary();
});
</script>