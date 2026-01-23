<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'image',
        'is_sold',
        'batch_id',
        'local_user_id',
        'version_type',
    ];
    public function localUser()
    {
        return $this->belongsTo(LocalUser::class);
    }
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
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
}
