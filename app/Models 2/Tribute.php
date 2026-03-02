<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tribute extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'profile_id',
        'image',
        'link_id',

    ];
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
