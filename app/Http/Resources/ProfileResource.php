<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'Photos' => PhotoResource::collection($this->link->photos),
            'Timelines' => TimelineResource::collection($this->link->timelines),
            'Tributes' => TributeResource::collection($this->link->tributes),
        ];
        if ($this) {
            $data['uuid'] = $this->uuid ?? '';
            $data['name'] = $this->name ?? '';
            $data['title'] = $this->title ?? '';
            $data['dob'] = $this->dob ?? '';
            $data['dod'] = $this->dod ?? ''; // Assuming 'dod' is date of death
            $data['facebook'] = $this->facebook ?? '';
            $data['instagram'] = $this->instagram ?? '';
            $data['twitter'] = $this->twitter ?? '';
            $data['spouse_facebook'] = $this->spouse_facebook ?? '';
            $data['spouse_instagram'] = $this->spouse_instagram ?? '';
            $data['spouse_twitter'] = $this->spouse_twitter ?? '';
            $data['spotify'] = $this->spotify ?? '';
            $data['youtube'] = $this->youtube ?? '';
            $data['bio'] = $this->bio ?? '';
            $data['longitude'] = $this->longitude ?? '';
            $data['latitude'] = $this->latitude ?? '';
            $data['badge'] = $this->badge ?? '';
            $data['spouse_badge'] = $this->spouse_badge ?? '';
            $data['dark_theme'] = $this->dark_theme ?? true;
            $data['created_at'] = $this->created_at ?? null;
            $data['relations'] = RelationResource::collection($this->relations ?? []);
            if ($this->profile && $this->profile->profile_picture) {
                // Use S3 if configured, otherwise fallback to asset()
                if (config('filesystems.default') === 's3') {
                    $data['profile_picture'] = Storage::disk('s3')->url('images/profile/profile_pictures/'.$this->profile->profile_picture);
                } else {
                    $data['profile_picture'] = asset('images/profile/profile_pictures/'.$this->profile->profile_picture);
                }
            }
            if ($this->profile && $this->profile->cover_picture) {
                // Use S3 if configured, otherwise fallback to asset()
                if (config('filesystems.default') === 's3') {
                    $data['cover_picture'] = Storage::disk('s3')->url('images/profile/cover_pictures/'.$this->profile->cover_picture);
                } else {
                    $data['cover_picture'] = asset('images/profile/cover_pictures/'.$this->profile->cover_picture);
                }
            }
        }
        return $data;
    }
}
