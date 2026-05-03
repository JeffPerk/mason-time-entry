<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $mason = Company::where('name', 'Mason Construction')->firstOrFail();
        $summit = Company::where('name', 'Summit Electrical')->firstOrFail();
        $desert = Company::where('name', 'Desert Ridge Plumbing')->firstOrFail();

        $employees = [
            [
                'first_name' => 'John',
                'last_name' => 'Carter',
                'email' => 'john.carter@example.com',
                'companies' => [$mason->id, $summit->id],
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Lopez',
                'email' => 'maria.lopez@example.com',
                'companies' => [$mason->id],
            ],
            [
                'first_name' => 'Avery',
                'last_name' => 'Nguyen',
                'email' => 'avery.nguyen@example.com',
                'companies' => [$summit->id],
            ],
            [
                'first_name' => 'Sam',
                'last_name' => 'Wilson',
                'email' => 'sam.wilson@example.com',
                'companies' => [$desert->id],
            ],
            [
                'first_name' => 'Taylor',
                'last_name' => 'Reed',
                'email' => 'taylor.reed@example.com',
                'companies' => [$mason->id, $desert->id],
            ],
            [
                'first_name' => 'Jordan',
                'last_name' => 'Kim',
                'email' => 'jordan.kim@example.com',
                'companies' => [$summit->id, $desert->id],
            ],
        ];

        foreach ($employees as $data) {
            $companyIds = $data['companies'];
            unset($data['companies']);

            $employee = Employee::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            $employee->companies()->syncWithoutDetaching($companyIds);
        }
    }
}