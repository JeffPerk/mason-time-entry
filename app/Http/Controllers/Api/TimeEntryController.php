<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');

        $timeEntries = TimeEntry::query()
            ->with([
                'company:id,name',
                'employee:id,first_name,last_name,email',
                'project:id,name',
                'task:id,name',
            ])
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (TimeEntry $entry) => [
                'id' => $entry->id,
                'company' => [
                    'id' => $entry->company->id,
                    'name' => $entry->company->name,
                ],
                'employee' => [
                    'id' => $entry->employee->id,
                    'name' => $entry->employee->name,
                    'email' => $entry->employee->email,
                ],
                'project' => [
                    'id' => $entry->project->id,
                    'name' => $entry->project->name,
                ],
                'task' => [
                    'id' => $entry->task->id,
                    'name' => $entry->task->name,
                ],
                'entry_date' => $entry->entry_date->format('Y-m-d'),
                'hours' => $entry->hours,
                'created_at' => $entry->created_at?->toISOString(),
            ]);

        return response()->json([
            'data' => $timeEntries,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Temporary placeholder.
        // We will replace this with real validation in the next step.
        return response()->json([
            'message' => 'Store endpoint is connected. Validation will be added next.',
            'received' => $request->all(),
        ], 422);
    }
}