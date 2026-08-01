<?php

namespace App\Modules\Tasks\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('list-task') ?? false;
    }

    public function rules(): array
    {
        return [
            'project_id' => [
                'nullable',
                'integer',
                'exists:projects,id',
            ],

            'status' => [
                'nullable',
                'integer',
                Rule::enum(TaskStatus::class),
            ],

            'priority' => [
                'nullable',
                'integer',
                Rule::enum(TaskPriority::class),
            ],

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}