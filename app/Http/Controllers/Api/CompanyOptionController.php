<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CompanyOptionController extends Controller
{
    public function show(Company $company): JsonResponse
    {
        $employees = $company->employees()
            ->select([
                'employees.id',
                'employees.first_name',
                'employees.last_name',
                'employees.email',
            ])
            ->orderBy('employees.last_name')
            ->orderBy('employees.first_name')
            ->get()
            ->map(fn ($employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
            ]);

        $projects = $company->projects()
            ->select(['id', 'company_id', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $tasks = $company->tasks()
            ->select(['id', 'company_id', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                ],
                'employees' => $employees,
                'projects' => $projects,
                'tasks' => $tasks,
            ],
        ]);
    }
}