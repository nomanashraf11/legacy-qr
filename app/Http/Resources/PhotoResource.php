<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if ($this->image && $this->image !== 'youtube_placeholder') {
            // Use S3 if configured, otherwise fallback to asset()
            if (config('filesystems.default') === 's3') {
                $imageUrl = Storage::disk('s3')->url('images/profile/photos/' . $this->image);
            } else {
                $imageUrl = asset('images/profile/photos/' . $this->image);
            }
        }
        
        return [
            'uuid' => $this->uuid,
            'image' => $imageUrl,
            'caption' => $this->caption,
            'link' => $this->link,
        ];
    }
}
