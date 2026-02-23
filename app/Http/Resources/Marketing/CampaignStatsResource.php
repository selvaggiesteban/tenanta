<?php

namespace App\Http\Resources\Marketing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'overview' => $this->resource['overview'],
            'rates' => $this->resource['rates'],
            'engagement' => $this->resource['engagement'],
            'top_links' => $this->resource['top_links'],
            'device_stats' => $this->resource['device_stats'],
            'geo_stats' => $this->resource['geo_stats'],
            'timeline' => $this->resource['timeline'],
        ];
    }
}
