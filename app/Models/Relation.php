<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'person_name',
        'dob',
        'dod',
        'bio',
        'relation_id',
        'profile_id',
        'image',
        'image_name',
        'is_legacy'
    ];

    protected $casts = [
        'is_legacy' => 'boolean'
    ];

    public function related()
    {
        return $this->hasMany(Relation::class, 'relation_id', 'uuid');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
