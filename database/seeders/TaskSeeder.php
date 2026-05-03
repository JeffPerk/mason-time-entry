<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            'Mason Construction' => [
                'Framing',
                'Site Cleanup',
                'Inspection Prep',
                'Material Handling',
            ],
            'Summit Electrical' => [
                'Wiring',
                'Troubleshooting',
                'Panel Work',
                'Safety Check',
            ],
            'Desert Ridge Plumbing' => [
                'Pipe Installation',
                'Leak Repair',
                'Fixture Work',
                'Pressure Testing',
            ],
        ];

        foreach ($tasks as $companyName => $taskNames) {
            $company = Company::where('name', $companyName)->firstOrFail();

            foreach ($taskNames as $taskName) {
                Task::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $taskName,
                    ],
                    [
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}