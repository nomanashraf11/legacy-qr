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
            try {
                $disk = config('filesystems.default');
                $path = 'images/profile/photos/' . $this->image;
                
                // Check if file exists before generating URL
                if (Storage::disk($disk)->exists($path)) {
                    $imageUrl = Storage::disk($disk)->url($path);
                } else {
                    // Fallback to local asset if S3 fails or file doesn't exist
                    $imageUrl = asset('images/profile/photos/' . $this->image);
                }
            } catch (\Exception $e) {
                // Fallback to local asset if S3 is not configured
                \Log::warning('PhotoResource: Failed to generate S3 URL, using local asset', [
                    'image' => $this->image,
                    'error' => $e->getMessage()
                ]);
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
