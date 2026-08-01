<?php

namespace App\Modules\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteProjectsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete-project') ?? false;
    }

    public function rules(): array
    {
        return [
            'project_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'project_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:projects,id',
            ],
        ];
    }
}