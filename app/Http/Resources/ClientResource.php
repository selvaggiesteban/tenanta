<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'vat_number' => $this->vat_number,
            'status' => $this->status,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'full_address' => $this->full_address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'notes' => $this->notes,
            'contacts_count' => $this->when($this->contacts_count !== null, $this->contacts_count),
            'projects_count' => $this->when($this->projects_count !== null, $this->projects_count),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'projects' => $this->when($this->relationLoaded('projects'), function () {
                return $this->projects->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'status' => $p->status,
                ]);
            }),
            'created_by' => $this->when($this->relationLoaded('createdBy'), function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                ];
            }),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
