<?php

namespace App\Modules\Tasks\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'project' => [
                'id' => $this->project_id,
                'name' => $this->whenLoaded(
                    'project',
                    fn (): ?string => $this->project?->name
                ),
            ],

            'title' => $this->title,

            'description' => $this->description,

            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
            ],

            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],

            'due_date' => $this->due_date?->toISOString(),

            'is_overdue' => $this->isOverdue(),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}