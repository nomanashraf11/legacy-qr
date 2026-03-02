<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'title',
        'date',
        'description',
        'profile_id',
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
