<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTimeEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:1'],

            'entries.*.company_id' => ['required', 'integer', 'exists:companies,id'],
            'entries.*.entry_date' => ['required', 'date'],
            'entries.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'entries.*.project_id' => ['required', 'integer', 'exists:projects,id'],
            'entries.*.task_id' => ['required', 'integer', 'exists:tasks,id'],
            'entries.*.hours' => ['required', 'numeric', 'gt:0', 'lte:24'],
        ];
    }

    public function messages(): array
    {
        return [
            'entries.required' => 'At least one time entry is required.',
            'entries.array' => 'Entries must be submitted as a list.',
            'entries.min' => 'At least one time entry is required.',

            'entries.*.company_id.required' => 'Company is required.',
            'entries.*.entry_date.required' => 'Date is required.',
            'entries.*.employee_id.required' => 'Employee is required.',
            'entries.*.project_id.required' => 'Project is required.',
            'entries.*.task_id.required' => 'Task is required.',
            'entries.*.hours.required' => 'Hours are required.',
            'entries.*.hours.gt' => 'Hours must be greater than 0.',
            'entries.*.hours.lte' => 'Hours cannot exceed 24.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $entries = collect($this->input('entries', []));

                $this->validateRelationships($validator, $entries);
                $this->validateDuplicateRows($validator, $entries);
                $this->validateOneProjectPerEmployeePerDateWithinRequest($validator, $entries);
                $this->validateOneProjectPerEmployeePerDateAgainstDatabase($validator, $entries);
            },
        ];
    }

    private function validateRelationships(Validator $validator, $entries): void
    {
        foreach ($entries as $index => $entry) {
            $companyId = (int) $entry['company_id'];
            $employeeId = (int) $entry['employee_id'];
            $projectId = (int) $entry['project_id'];
            $taskId = (int) $entry['task_id'];

            $employeeBelongsToCompany = Employee::query()
                ->where('id', $employeeId)
                ->whereHas('companies', function ($query) use ($companyId) {
                    $query->where('companies.id', $companyId);
                })
                ->exists();

            if (! $employeeBelongsToCompany) {
                $validator->errors()->add(
                    "entries.$index.employee_id",
                    'This employee does not belong to the selected company.'
                );
            }

            $projectBelongsToCompany = Project::query()
                ->where('id', $projectId)
                ->where('company_id', $companyId)
                ->exists();

            if (! $projectBelongsToCompany) {
                $validator->errors()->add(
                    "entries.$index.project_id",
                    'This project does not belong to the selected company.'
                );
            }

            $taskBelongsToCompany = Task::query()
                ->where('id', $taskId)
                ->where('company_id', $companyId)
                ->exists();

            if (! $taskBelongsToCompany) {
                $validator->errors()->add(
                    "entries.$index.task_id",
                    'This task does not belong to the selected company.'
                );
            }

            $employeeAssignedToProject = Project::query()
                ->where('id', $projectId)
                ->whereHas('employees', function ($query) use ($employeeId) {
                    $query->where('employees.id', $employeeId);
                })
                ->exists();

            if (! $employeeAssignedToProject) {
                $validator->errors()->add(
                    "entries.$index.project_id",
                    'This employee is not assigned to the selected project.'
                );
            }
        }
    }

    private function validateDuplicateRows(Validator $validator, $entries): void
    {
        $seen = [];

        foreach ($entries as $index => $entry) {
            $key = implode('|', [
                $entry['employee_id'],
                $entry['project_id'],
                $entry['task_id'],
                $entry['entry_date'],
            ]);

            if (isset($seen[$key])) {
                $validator->errors()->add(
                    "entries.$index.task_id",
                    'This entry duplicates another row in the submission.'
                );
            }

            $seen[$key] = true;

            $alreadyExists = TimeEntry::query()
                ->where('employee_id', $entry['employee_id'])
                ->where('project_id', $entry['project_id'])
                ->where('task_id', $entry['task_id'])
                ->whereDate('entry_date', $entry['entry_date'])
                ->exists();

            if ($alreadyExists) {
                $validator->errors()->add(
                    "entries.$index.task_id",
                    'This employee already has a time entry for this project, task, and date.'
                );
            }
        }
    }

    private function validateOneProjectPerEmployeePerDateWithinRequest(Validator $validator, $entries): void
    {
        $employeeDateProjects = [];

        foreach ($entries as $index => $entry) {
            $key = $entry['employee_id'] . '|' . $entry['entry_date'];
            $projectId = (int) $entry['project_id'];

            if (! isset($employeeDateProjects[$key])) {
                $employeeDateProjects[$key] = $projectId;
                continue;
            }

            if ($employeeDateProjects[$key] !== $projectId) {
                $validator->errors()->add(
                    "entries.$index.project_id",
                    'An employee can only work on one project per date.'
                );
            }
        }
    }

    private function validateOneProjectPerEmployeePerDateAgainstDatabase(Validator $validator, $entries): void
    {
        foreach ($entries as $index => $entry) {
            $conflictingProjectExists = TimeEntry::query()
                ->where('employee_id', $entry['employee_id'])
                ->whereDate('entry_date', $entry['entry_date'])
                ->where('project_id', '!=', $entry['project_id'])
                ->exists();

            if ($conflictingProjectExists) {
                $validator->errors()->add(
                    "entries.$index.project_id",
                    'This employee already has time entered for a different project on this date.'
                );
            }
        }
    }

    public function validatedEntries(): array
    {
        return collect($this->validated('entries'))
            ->map(fn (array $entry) => [
                'company_id' => $entry['company_id'],
                'employee_id' => $entry['employee_id'],
                'project_id' => $entry['project_id'],
                'task_id' => $entry['task_id'],
                'entry_date' => $entry['entry_date'],
                'hours' => $entry['hours'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();
    }
}