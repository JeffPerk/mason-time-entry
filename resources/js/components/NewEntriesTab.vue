<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    New Entries
                </h2>

                <p class="mt-1 text-sm text-slate-600">
                    Enter one or more employee time entries. Dropdowns are filtered by company.
                </p>
            </div>

            <div
                v-if="selectedCompanyName"
                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
            >
                Default company: {{ selectedCompanyName }}
            </div>

            <div
                v-else
                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
            >
                Select company per row
            </div>
        </div>

        <div
            v-if="successMessage"
            class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
        >
            {{ successMessage }}
        </div>

        <div
            v-if="generalError"
            class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            {{ generalError }}
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-56 px-3 py-3 text-left font-semibold text-slate-700">
                            Company
                        </th>
                        <th class="w-40 px-3 py-3 text-left font-semibold text-slate-700">
                            Date
                        </th>
                        <th class="w-56 px-3 py-3 text-left font-semibold text-slate-700">
                            Employee
                        </th>
                        <th class="w-56 px-3 py-3 text-left font-semibold text-slate-700">
                            Project
                        </th>
                        <th class="w-56 px-3 py-3 text-left font-semibold text-slate-700">
                            Task
                        </th>
                        <th class="w-32 px-3 py-3 text-left font-semibold text-slate-700">
                            Hours
                        </th>
                        <th class="w-32 px-3 py-3 text-right font-semibold text-slate-700">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr
                        v-for="(row, index) in rows"
                        :key="row.local_id"
                        class="align-top"
                    >
                        <td class="px-3 py-3">
                            <select
                                v-model="row.company_id"
                                class="field"
                                @change="handleCompanyChange(row)"
                            >
                                <option value="">Select company</option>

                                <option
                                    v-for="company in companies"
                                    :key="company.id"
                                    :value="String(company.id)"
                                >
                                    {{ company.name }}
                                </option>
                            </select>

                            <FieldError :message="firstError(index, 'company_id')" />
                        </td>

                        <td class="px-3 py-3">
                            <input
                                v-model="row.entry_date"
                                type="date"
                                class="field"
                            >

                            <FieldError :message="firstError(index, 'entry_date')" />
                        </td>

                        <td class="px-3 py-3">
                            <select
                                v-model="row.employee_id"
                                class="field"
                                :disabled="!row.company_id || isOptionsLoading(row.company_id)"
                            >
                                <option value="">
                                    {{ row.company_id ? 'Select employee' : 'Select company first' }}
                                </option>

                                <option
                                    v-for="employee in optionsFor(row.company_id).employees"
                                    :key="employee.id"
                                    :value="String(employee.id)"
                                >
                                    {{ employee.name }}
                                </option>
                            </select>

                            <FieldError :message="firstError(index, 'employee_id')" />
                        </td>

                        <td class="px-3 py-3">
                            <select
                                v-model="row.project_id"
                                class="field"
                                :disabled="!row.company_id || isOptionsLoading(row.company_id)"
                            >
                                <option value="">
                                    {{ row.company_id ? 'Select project' : 'Select company first' }}
                                </option>

                                <option
                                    v-for="project in optionsFor(row.company_id).projects"
                                    :key="project.id"
                                    :value="String(project.id)"
                                >
                                    {{ project.name }}
                                </option>
                            </select>

                            <FieldError :message="firstError(index, 'project_id')" />
                        </td>

                        <td class="px-3 py-3">
                            <select
                                v-model="row.task_id"
                                class="field"
                                :disabled="!row.company_id || isOptionsLoading(row.company_id)"
                            >
                                <option value="">
                                    {{ row.company_id ? 'Select task' : 'Select company first' }}
                                </option>

                                <option
                                    v-for="task in optionsFor(row.company_id).tasks"
                                    :key="task.id"
                                    :value="String(task.id)"
                                >
                                    {{ task.name }}
                                </option>
                            </select>

                            <FieldError :message="firstError(index, 'task_id')" />
                        </td>

                        <td class="px-3 py-3">
                            <input
                                v-model="row.hours"
                                type="number"
                                step="0.25"
                                min="0"
                                max="24"
                                class="field"
                                placeholder="0.00"
                            >

                            <FieldError :message="firstError(index, 'hours')" />
                        </td>

                        <td class="px-3 py-3">
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-300 px-2.5 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                                    title="Duplicate row"
                                    @click="duplicateRow(index)"
                                >
                                    Copy
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg border border-red-200 px-2.5 py-2 text-xs font-medium text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="rows.length === 1"
                                    title="Remove row"
                                    @click="removeRow(index)"
                                >
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-slate-500">
                Total pending hours:
                <span class="font-semibold text-slate-800">{{ totalHours }}</span>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                    @click="addRow"
                >
                    Add Row
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="isSubmitting"
                    @click="submitEntries"
                >
                    {{ isSubmitting ? 'Saving...' : 'Submit Entries' }}
                </button>
            </div>
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
    companies: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['entries-created']);

const rows = ref([]);
const optionsByCompany = ref({});
const loadingCompanyIds = ref(new Set());
const validationErrors = ref({});
const generalError = ref('');
const successMessage = ref('');
const isSubmitting = ref(false);

const FieldError = {
    props: {
        message: {
            type: String,
            default: '',
        },
    },
    render() {
        if (!this.message) {
            return null;
        }

        return h('p', {
            class: 'mt-1 text-xs leading-5 text-red-600',
        }, this.message);
    },
};

const totalHours = computed(() => {
    const total = rows.value.reduce((sum, row) => {
        const hours = Number.parseFloat(row.hours);

        if (Number.isNaN(hours)) {
            return sum;
        }

        return sum + hours;
    }, 0);

    return total.toFixed(2);
});

function today() {
    return new Date().toISOString().slice(0, 10);
}

function makeRow(overrides = {}) {
    return {
        local_id: crypto.randomUUID(),
        company_id: props.selectedCompanyId || '',
        entry_date: today(),
        employee_id: '',
        project_id: '',
        task_id: '',
        hours: '',
        ...overrides,
    };
}

function addRow() {
    const previousRow = rows.value[rows.value.length - 1];

    rows.value.push(makeRow({
        company_id: props.selectedCompanyId || previousRow?.company_id || '',
        entry_date: previousRow?.entry_date || today(),
    }));

    const newRow = rows.value[rows.value.length - 1];

    if (newRow.company_id) {
        loadOptionsForCompany(newRow.company_id);
    }
}

function duplicateRow(index) {
    const source = rows.value[index];

    rows.value.splice(index + 1, 0, makeRow({
        company_id: source.company_id,
        entry_date: source.entry_date,
        employee_id: source.employee_id,
        project_id: source.project_id,
        task_id: '',
        hours: source.hours,
    }));

    if (source.company_id) {
        loadOptionsForCompany(source.company_id);
    }
}

function removeRow(index) {
    if (rows.value.length === 1) {
        return;
    }

    rows.value.splice(index, 1);
    validationErrors.value = {};
}

function handleCompanyChange(row) {
    row.employee_id = '';
    row.project_id = '';
    row.task_id = '';

    if (row.company_id) {
        loadOptionsForCompany(row.company_id);
    }
}

function optionsFor(companyId) {
    if (!companyId) {
        return {
            employees: [],
            projects: [],
            tasks: [],
        };
    }

    return optionsByCompany.value[companyId] || {
        employees: [],
        projects: [],
        tasks: [],
    };
}

function isOptionsLoading(companyId) {
    return loadingCompanyIds.value.has(String(companyId));
}

async function loadOptionsForCompany(companyId) {
    if (!companyId) {
        return;
    }

    const key = String(companyId);

    if (optionsByCompany.value[key]) {
        return;
    }

    loadingCompanyIds.value.add(key);
    loadingCompanyIds.value = new Set(loadingCompanyIds.value);

    try {
        const response = await fetch(`/api/companies/${companyId}/options`, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Unable to load company options.');
        }

        const payload = await response.json();

        optionsByCompany.value[key] = {
            employees: payload.data?.employees ?? [],
            projects: payload.data?.projects ?? [],
            tasks: payload.data?.tasks ?? [],
        };
    } catch (error) {
        generalError.value = error.message || 'Something went wrong while loading company options.';
    } finally {
        loadingCompanyIds.value.delete(key);
        loadingCompanyIds.value = new Set(loadingCompanyIds.value);
    }
}

function firstError(index, field) {
    const key = `entries.${index}.${field}`;

    return validationErrors.value[key]?.[0] ?? '';
}

function normalizeRow(row) {
    return {
        company_id: Number(row.company_id),
        entry_date: row.entry_date,
        employee_id: Number(row.employee_id),
        project_id: Number(row.project_id),
        task_id: Number(row.task_id),
        hours: Number(row.hours),
    };
}

async function submitEntries() {
    isSubmitting.value = true;
    validationErrors.value = {};
    generalError.value = '';
    successMessage.value = '';

    try {
        const response = await fetch('/api/time-entries', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                entries: rows.value.map(normalizeRow),
            }),
        });

        const payload = await response.json();

        if (response.status === 422) {
            validationErrors.value = payload.errors ?? {};
            generalError.value = payload.message ?? 'Please fix the highlighted fields.';
            return;
        }

        if (!response.ok) {
            throw new Error(payload.message || 'Unable to save time entries.');
        }

        successMessage.value = payload.message || 'Time entries created successfully.';

        emit('entries-created', payload.data ?? []);

        rows.value = [
            makeRow(),
        ];

        if (rows.value[0].company_id) {
            loadOptionsForCompany(rows.value[0].company_id);
        }
    } catch (error) {
        generalError.value = error.message || 'Something went wrong while saving time entries.';
    } finally {
        isSubmitting.value = false;
    }
}

watch(
    () => props.selectedCompanyId,
    (newCompanyId) => {
        if (!newCompanyId) {
            return;
        }

        loadOptionsForCompany(newCompanyId);

        rows.value = rows.value.map((row) => {
            if (row.company_id) {
                return row;
            }

            return {
                ...row,
                company_id: newCompanyId,
            };
        });
    }
);

onMounted(() => {
    rows.value = [
        makeRow(),
    ];

    if (props.selectedCompanyId) {
        loadOptionsForCompany(props.selectedCompanyId);
    }
});
</script>

<style scoped>
.field {
    width: 100%;
    border-radius: 0.5rem;
    border: 1px solid rgb(203 213 225);
    background: white;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    color: rgb(15 23 42);
    outline: none;
    box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
    transition: border-color 150ms ease, box-shadow 150ms ease;
}

.field:focus {
    border-color: rgb(71 85 105);
    box-shadow: 0 0 0 2px rgb(226 232 240);
}

.field:disabled {
    cursor: not-allowed;
    background: rgb(248 250 252);
    color: rgb(100 116 139);
}
</style>