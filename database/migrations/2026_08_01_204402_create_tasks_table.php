<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->unsignedTinyInteger('priority')
                ->default(TaskPriority::MEDIUM->value);

            $table->unsignedTinyInteger('status')
                ->default(TaskStatus::TODO->value);

            $table->dateTime('due_date')->nullable();

            /*
             * Prevents sending the same overdue notification
             * repeatedly on every scheduler execution.
             */
            $table->timestamp('overdue_notified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'priority']);
            $table->index(['due_date', 'status']);
            $table->index([
                'due_date',
                'overdue_notified_at',
                'deleted_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};