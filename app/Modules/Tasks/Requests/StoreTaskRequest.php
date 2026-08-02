<?php

namespace App\Modules\Tasks\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-task') ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'priority' => [
                'required',
                'integer',
                Rule::enum(TaskPriority::class),
            ],

            'status' => [
                'sometimes',
                'required',
                'integer',
                Rule::enum(TaskStatus::class),
            ],

            'due_date' => [
                'nullable',
                'date',
            ],
        ];
    }
}