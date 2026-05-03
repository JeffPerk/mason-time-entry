<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Mason Construction'],
            ['name' => 'Summit Electrical'],
            ['name' => 'Desert Ridge Plumbing'],
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate(
                ['name' => $company['name']],
                $company
            );
        }
    }
}