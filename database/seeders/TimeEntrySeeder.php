<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Database\Seeder;

class TimeEntrySeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'company' => 'Mason Construction',
                'employee' => 'maria.lopez@example.com',
                'project' => 'Warehouse Renovation',
                'task' => 'Framing',
                'entry_date' => now()->subDays(2)->toDateString(),
                'hours' => 4.00,
            ],
            [
                'company' => 'Mason Construction',
                'employee' => 'maria.lopez@example.com',
                'project' => 'Warehouse Renovation',
                'task' => 'Site Cleanup',
                'entry_date' => now()->subDays(2)->toDateString(),
                'hours' => 2.50,
            ],
            [
                'company' => 'Summit Electrical',
                'employee' => 'avery.nguyen@example.com',
                'project' => 'Lighting Retrofit',
                'task' => 'Wiring',
                'entry_date' => now()->subDay()->toDateString(),
                'hours' => 6.00,
            ],
            [
                'company' => 'Desert Ridge Plumbing',
                'employee' => 'sam.wilson@example.com',
                'project' => 'Main Line Replacement',
                'task' => 'Pipe Installation',
                'entry_date' => now()->subDay()->toDateString(),
                'hours' => 5.25,
            ],
        ];

        foreach ($entries as $entry) {
            $company = Company::where('name', $entry['company'])->firstOrFail();
            $employee = Employee::where('email', $entry['employee'])->firstOrFail();

            $project = Project::where('company_id', $company->id)
                ->where('name', $entry['project'])
                ->firstOrFail();

            $task = Task::where('company_id', $company->id)
                ->where('name', $entry['task'])
                ->firstOrFail();

            TimeEntry::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'project_id' => $project->id,
                    'task_id' => $task->id,
                    'entry_date' => $entry['entry_date'],
                ],
                [
                    'company_id' => $company->id,
                    'hours' => $entry['hours'],
                ]
            );
        }
    }
}