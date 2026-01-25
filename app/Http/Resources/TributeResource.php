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
            try {
                $data['image'] = Storage::disk(config('filesystems.default'))->url('images/profile/tributes/' . $this->image);
            } catch (\Exception $e) {
                \Log::warning('TributeResource: Failed to generate S3 URL', ['error' => $e->getMessage()]);
                $data['image'] = asset('images/profile/tributes/' . $this->image);
            }
        }

        return $data;
    }
}
