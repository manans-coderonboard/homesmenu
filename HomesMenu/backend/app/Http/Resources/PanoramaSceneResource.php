<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PanoramaSceneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_label' => $this->room_label,
            'url' => $this->url,
            'default_yaw' => (float) $this->default_yaw,
            'default_pitch' => (float) $this->default_pitch,
            'sort_order' => $this->sort_order,
        ];
    }
}
