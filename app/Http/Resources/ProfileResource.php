<?php

namespace App\Http\Resources;

use App\Support\ProfileMediaUrls;
use App\Support\TabVisibility;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            $data['tab_visibility'] = TabVisibility::merge($this->tab_visibility ?? null);
            $data['created_at'] = $this->created_at ?? null;
            $data['relations'] = RelationResource::collection($this->relations ?? []);
            if ($this->profile_picture) {
                $url = ProfileMediaUrls::profilePicture($this->profile_picture);
                if ($url) {
                    $data['profile_picture'] = $url;
                }
            }
            if ($this->cover_picture) {
                $url = ProfileMediaUrls::coverPicture($this->cover_picture);
                if ($url) {
                    $data['cover_picture'] = $url;
                }
            }
        }
        return $data;
    }
}
