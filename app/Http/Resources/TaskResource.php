<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'due_date'    => $this->due_date?->format('Y-m-d'),
            'is_overdue'  => $this->isOverdue(),
            'user'        => new UserResource($this->whenLoaded('user')),
            'created_at'  => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at->format('Y-m-d H:i:s'),
            'links'       => [
                'self'   => route('api.tasks.show', $this->id),
                'update' => route('api.tasks.update', $this->id),
                'delete' => route('api.tasks.destroy', $this->id),
            ],
        ];
    }
}
