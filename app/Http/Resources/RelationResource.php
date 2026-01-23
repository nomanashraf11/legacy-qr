<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "uuid" => $this->uuid,
            "name" => $this->name,
            "person_name" => $this->person_name,
            "dob" => $this->dob,
            "dod" => $this->dod,
            "image" => $this->image,
            "image_name" => $this->image_name,
            "bio" => $this->bio,
            "relation_id" => $this->relation_id,
            "is_legacy" => $this->is_legacy,
            "related" => self::collection($this->related)
        ];
    }
}
