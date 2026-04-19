<?php

namespace App\Http\Resources;

use App\Support\ProfileMediaUrls;
use App\Support\TabVisibility;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'Photos' => PhotoResource::collection($this->photos),
            'Timelines' => TimelineResource::collection($this->timelines),
            'Tributes' => TributeResource::collection($this->tributes),
        ];
        if ($this) {
            $data['uuid'] = $this->profile->uuid ?? '';
            $data['name'] = $this->profile->name ?? '';
            $data['title'] = $this->profile->title ?? '';
            $data['dob'] = $this->profile->dob ?? '';
            $data['dod'] = $this->profile->dod ?? ''; // Assuming 'dod' is date of death
            $data['facebook'] = $this->profile->facebook ?? '';
            $data['instagram'] = $this->profile->instagram ?? '';
            $data['twitter'] = $this->profile->twitter ?? '';
            $data['spouse_facebook'] = $this->profile->spouse_facebook ?? '';
            $data['spouse_instagram'] = $this->profile->spouse_instagram ?? '';
            $data['spouse_twitter'] = $this->profile->spouse_twitter ?? '';
            $data['spotify'] = $this->profile->spotify ?? '';
            $data['youtube'] = $this->profile->youtube ?? '';
            $data['bio'] = $this->profile->bio ?? '';
            $data['longitude'] = $this->profile->longitude ?? '';
            $data['latitude'] = $this->profile->latitude ?? '';
            $data['badge'] = $this->profile->badge ?? '';
            $data['spouse_badge'] = $this->profile->spouse_badge ?? '';
            $data['dark_theme'] = $this->profile->dark_theme ?? true;
            $data['tab_visibility'] = TabVisibility::merge($this->profile->tab_visibility ?? null);
            $data['relations'] = RelationResource::collection($this->profile->relations ?? []);
            // 🎄 Add version_type to the Details section
            $data['version_type'] = $this->version_type ?? 'full';
            if ($this->profile && $this->profile->profile_picture) {
                $url = ProfileMediaUrls::profilePicture($this->profile->profile_picture);
                if ($url) {
                    $data['profile_picture'] = $url;
                }
            }
            if ($this->profile && $this->profile->cover_picture) {
                $url = ProfileMediaUrls::coverPicture($this->profile->cover_picture);
                if ($url) {
                    $data['cover_picture'] = $url;
                }
            }
        }
        return $data;
    }
}
