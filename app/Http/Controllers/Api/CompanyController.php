<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $companies,
        ]);
    }
}