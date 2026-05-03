<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeEntriesRequest;
use App\Models\TimeEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->map(fn (TimeEntry $entry) => $this->formatTimeEntry($entry));

        return response()->json([
            'data' => $timeEntries,
        ]);
    }

    public function store(StoreTimeEntriesRequest $request): JsonResponse
    {
        $createdEntryIds = DB::transaction(function () use ($request) {
            return collect($request->validatedEntries())
                ->map(function (array $entry) {
                    unset($entry['created_at'], $entry['updated_at']);
    
                    return TimeEntry::query()->create($entry)->id;
                })
                ->all();
        });
    
        $createdEntries = TimeEntry::query()
            ->with([
                'company:id,name',
                'employee:id,first_name,last_name,email',
                'project:id,name',
                'task:id,name',
            ])
            ->whereIn('id', $createdEntryIds)
            ->orderBy('id')
            ->get();
    
        return response()->json([
            'message' => 'Time entries created successfully.',
            'data' => $createdEntries->map(fn (TimeEntry $entry) => $this->formatTimeEntry($entry)),
        ], 201);
    }

    private function formatTimeEntry(TimeEntry $entry): array
    {
        return [
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
        ];
    }
}