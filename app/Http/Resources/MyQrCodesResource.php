<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyQrCodesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'image' => asset('images/qr_codes/' . $this->image),
            'profile' => new ProfileResource($this->profile),
            'version_type' => $this->version_type ?? 'full',
        ];
    }
}
