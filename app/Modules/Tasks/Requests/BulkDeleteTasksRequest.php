<?php

namespace App\Modules\Tasks\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete-task') ?? false;
    }

    public function rules(): array
    {
        return [
            'task_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'task_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:tasks,id',
            ],
        ];
    }
}