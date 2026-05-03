<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanyOptionController;
use App\Http\Controllers\Api\TimeEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/companies', [CompanyController::class, 'index']);

Route::get('/companies/{company}/options', [CompanyOptionController::class, 'show']);

Route::get('/time-entries', [TimeEntryController::class, 'index']);
Route::post('/time-entries', [TimeEntryController::class, 'store']);