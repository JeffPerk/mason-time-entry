<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();
    
            $table->foreignId('employee_id')
                ->constrained()
                ->cascadeOnDelete();
    
            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();
    
            $table->foreignId('task_id')
                ->constrained()
                ->cascadeOnDelete();
    
            $table->date('entry_date');
            $table->decimal('hours', 5, 2);
    
            $table->timestamps();
    
            $table->index(['company_id', 'entry_date']);
            $table->index(['employee_id', 'entry_date']);
            $table->index(['project_id', 'entry_date']);
    
            $table->unique([
                'employee_id',
                'project_id',
                'task_id',
                'entry_date',
            ], 'unique_employee_project_task_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
