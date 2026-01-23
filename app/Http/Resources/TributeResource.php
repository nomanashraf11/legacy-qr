<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,

        ];
        if ($this->image) {
            // Use S3 if configured, otherwise fallback to asset()
            if (config('filesystems.default') === 's3') {
                $data['image'] = Storage::disk('s3')->url('images/profile/tributes/' . $this->image);
            } else {
                $data['image'] = asset('images/profile/tributes/' . $this->image);
            }
        }

        return $data;
    }
}
