<?php

namespace App\Modules\Projects\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'description' => $this->description,

            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],

            'user' => [
                'id' => $this->user_id,
            ],

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}