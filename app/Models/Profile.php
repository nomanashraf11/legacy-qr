<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'name',
        'title',
        'dob',
        'dod',
        'profile_picture',
        'cover_picture',
        'facebook',
        'instagram',
        'twitter',
        'spouse_facebook',
        'spouse_instagram',
        'spouse_twitter',
        'spotify',
        'youtube',
        'bio',
        'longitude',
        'latitude',
        'link_id',
        'badge',
        'spouse_badge',
        'dark_theme',
        'tab_visibility',
    ];

    protected $casts = [
        'dark_theme' => 'boolean',
        'tab_visibility' => 'array',
    ];

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
    public function timelines()
    {
        return $this->hasMany(Timeline::class);
    }
    public function tributes()
    {
        return $this->hasMany(Tribute::class);
    }
    public function link()
    {
        return $this->belongsTo(Link::class);
    }
    public function relations()
    {
        return $this->hasMany(Relation::class);
    }
}
