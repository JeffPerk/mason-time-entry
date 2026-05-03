<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            'Mason Construction' => [
                [
                    'name' => 'Warehouse Renovation',
                    'employees' => [
                        'john.carter@example.com',
                        'maria.lopez@example.com',
                        'taylor.reed@example.com',
                    ],
                ],
                [
                    'name' => 'Office Buildout',
                    'employees' => [
                        'maria.lopez@example.com',
                        'taylor.reed@example.com',
                    ],
                ],
            ],
            'Summit Electrical' => [
                [
                    'name' => 'Panel Upgrade',
                    'employees' => [
                        'john.carter@example.com',
                        'avery.nguyen@example.com',
                    ],
                ],
                [
                    'name' => 'Lighting Retrofit',
                    'employees' => [
                        'avery.nguyen@example.com',
                        'jordan.kim@example.com',
                    ],
                ],
            ],
            'Desert Ridge Plumbing' => [
                [
                    'name' => 'Main Line Replacement',
                    'employees' => [
                        'sam.wilson@example.com',
                        'taylor.reed@example.com',
                    ],
                ],
                [
                    'name' => 'Fixture Install',
                    'employees' => [
                        'sam.wilson@example.com',
                        'jordan.kim@example.com',
                    ],
                ],
            ],
        ];

        foreach ($projects as $companyName => $companyProjects) {
            $company = Company::where('name', $companyName)->firstOrFail();

            foreach ($companyProjects as $projectData) {
                $employeeEmails = $projectData['employees'];

                $project = Project::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $projectData['name'],
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                $employeeIds = Employee::whereIn('email', $employeeEmails)
                    ->pluck('id')
                    ->all();

                $project->employees()->syncWithoutDetaching($employeeIds);
            }
        }
    }
}