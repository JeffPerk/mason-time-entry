<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    History
                </h2>

                <p class="mt-1 text-sm text-slate-600">
                    View previously submitted employee time entries.
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

        <div
            v-if="loadError"
            class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ loadError }}
        </div>

        <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-col gap-2 md:flex-row md:items-center">
                <label class="sr-only" for="history-search">
                    Search history
                </label>

                <input
                    id="history-search"
                    v-model="searchTerm"
                    type="search"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm shadow-sm outline-none transition placeholder:text-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-200 md:w-80"
                    placeholder="Search company, employee, project, task..."
                >

                <button
                    type="button"
                    class="rounded-xl border border-violet-200 bg-white px-4 py-2 text-sm font-semibold text-violet-700 shadow-sm transition hover:bg-violet-50"
                    @click="loadEntries"
                >
                    Refresh
                </button>
            </div>

            <div class="text-sm text-slate-500">
                <span v-if="isLoading">Loading entries...</span>

                <span v-else>
                    {{ filteredEntries.length }} entries ·
                    <span class="font-semibold text-slate-800">{{ totalHours }}</span>
                    total hours
                </span>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-3 text-left font-semibold text-slate-700">
                            <SortButton
                                label="Company"
                                field="company"
                                :sort-field="sortField"
                                :sort-direction="sortDirection"
                                @sort="setSort"
                            />
                        </th>

                        <th class="px-3 py-3 text-left font-semibold text-slate-700">
                            <SortButton
                                label="Date"
                                field="entry_date"
                                :sort-field="sortField"
                                :sort-direction="sortDirection"
                                @sort="setSort"
                            />
                        </th>

                        <th class="px-3 py-3 text-left font-semibold text-slate-700">
                            <SortButton
                                label="Employee"
                                field="employee"
                                :sort-field="sortField"
                                :sort-direction="sortDirection"
                                @sort="setSort"
                            />
                        </th>

                        <th class="px-3 py-3 text-left font-semibold text-slate-700">
                            <SortButton
                                label="Project"
                                field="project"
                                :sort-field="sortField"
                                :sort-direction="sortDirection"
                                @sort="setSort"
                            />
                        </th>

                        <th class="px-3 py-3 text-left font-semibold text-slate-700">
                            <SortButton
                                label="Task"
                                field="task"
                                :sort-field="sortField"
                                :sort-direction="sortDirection"
                                @sort="setSort"
                            />
                        </th>

                        <th class="px-3 py-3 text-right font-semibold text-slate-700">
                            <SortButton
                                label="Hours"
                                field="hours"
                                :sort-field="sortField"
                                :sort-direction="sortDirection"
                                align="right"
                                @sort="setSort"
                            />
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr
                        v-if="!isLoading && sortedEntries.length === 0"
                    >
                        <td
                            colspan="6"
                            class="px-3 py-10 text-center text-sm text-slate-500"
                        >
                            No time entries found.
                        </td>
                    </tr>

                    <tr
                        v-for="entry in sortedEntries"
                        :key="entry.id"
                        class="transition hover:bg-slate-50"
                    >
                        <td class="whitespace-nowrap px-3 py-3 text-slate-700">
                            {{ entry.company.name }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-slate-700">
                            {{ formatDate(entry.entry_date) }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-3">
                            <div class="font-medium text-slate-900">
                                {{ entry.employee.name }}
                            </div>

                            <div class="text-xs text-slate-500">
                                {{ entry.employee.email }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-slate-700">
                            {{ entry.project.name }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-slate-700">
                            {{ entry.task.name }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-3 text-right font-medium text-slate-900">
                            {{ formatHours(entry.hours) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<script setup>
import { computed, h, onMounted, ref, watch } from 'vue';

const props = defineProps({
    selectedCompanyId: {
        type: String,
        required: true,
    },
    selectedCompanyName: {
        type: String,
        default: '',
    },
    refreshToken: {
        type: Number,
        default: 0,
    },
});

const entries = ref([]);
const isLoading = ref(false);
const loadError = ref('');
const searchTerm = ref('');
const sortField = ref('entry_date');
const sortDirection = ref('desc');

const SortButton = {
    props: {
        label: {
            type: String,
            required: true,
        },
        field: {
            type: String,
            required: true,
        },
        sortField: {
            type: String,
            required: true,
        },
        sortDirection: {
            type: String,
            required: true,
        },
        align: {
            type: String,
            default: 'left',
        },
    },
    emits: ['sort'],
    setup(props, { emit }) {
        return () => {
            const isActive = props.sortField === props.field;
            const icon = isActive
                ? props.sortDirection === 'asc'
                    ? '↑'
                    : '↓'
                : '↕';

            return h(
                'button',
                {
                    type: 'button',
                    class: [
                        'inline-flex w-full items-center gap-1 text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:text-slate-950',
                        props.align === 'right' ? 'justify-end' : 'justify-start',
                    ],
                    onClick: () => emit('sort', props.field),
                },
                [
                    h('span', props.label),
                    h('span', { class: 'text-slate-400' }, icon),
                ]
            );
        };
    },
};

const filteredEntries = computed(() => {
    const term = searchTerm.value.trim().toLowerCase();

    if (!term) {
        return entries.value;
    }

    return entries.value.filter((entry) => {
        const haystack = [
            entry.company?.name,
            entry.entry_date,
            entry.employee?.name,
            entry.employee?.email,
            entry.project?.name,
            entry.task?.name,
            entry.hours,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(term);
    });
});

const sortedEntries = computed(() => {
    return [...filteredEntries.value].sort((a, b) => {
        const aValue = sortValue(a, sortField.value);
        const bValue = sortValue(b, sortField.value);

        if (aValue < bValue) {
            return sortDirection.value === 'asc' ? -1 : 1;
        }

        if (aValue > bValue) {
            return sortDirection.value === 'asc' ? 1 : -1;
        }

        return 0;
    });
});

const totalHours = computed(() => {
    const total = filteredEntries.value.reduce((sum, entry) => {
        const hours = Number.parseFloat(entry.hours);

        if (Number.isNaN(hours)) {
            return sum;
        }

        return sum + hours;
    }, 0);

    return total.toFixed(2);
});

function sortValue(entry, field) {
    const values = {
        company: entry.company?.name ?? '',
        entry_date: entry.entry_date ?? '',
        employee: entry.employee?.name ?? '',
        project: entry.project?.name ?? '',
        task: entry.task?.name ?? '',
        hours: Number.parseFloat(entry.hours) || 0,
    };

    const value = values[field] ?? '';

    return typeof value === 'string' ? value.toLowerCase() : value;
}

function setSort(field) {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        return;
    }

    sortField.value = field;
    sortDirection.value = field === 'entry_date' ? 'desc' : 'asc';
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    const [year, month, day] = value.split('-');

    return `${month}/${day}/${year}`;
}

function formatHours(value) {
    const hours = Number.parseFloat(value);

    if (Number.isNaN(hours)) {
        return '0.00';
    }

    return hours.toFixed(2);
}

async function loadEntries() {
    isLoading.value = true;
    loadError.value = '';

    const params = new URLSearchParams();

    if (props.selectedCompanyId) {
        params.set('company_id', props.selectedCompanyId);
    }

    const queryString = params.toString();
    const url = queryString
        ? `/api/time-entries?${queryString}`
        : '/api/time-entries';

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load time entries.');
        }

        const payload = await response.json();

        entries.value = payload.data ?? [];
    } catch (error) {
        loadError.value = error.message || 'Something went wrong while loading history.';
    } finally {
        isLoading.value = false;
    }
}

watch(
    () => props.selectedCompanyId,
    () => {
        searchTerm.value = '';
        loadEntries();
    }
);

watch(
    () => props.refreshToken,
    () => {
        loadEntries();
    }
);

onMounted(() => {
    loadEntries();
});
</script>