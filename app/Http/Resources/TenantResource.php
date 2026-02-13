<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'primary_color' => $this->primary_color,
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'is_trial' => $this->trial_ends_at && $this->trial_ends_at->isFuture(),
            'settings' => $this->settings,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
