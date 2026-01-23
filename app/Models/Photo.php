<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'image',
        'link',
        'caption',
        'profile_id',
        'link_id',
    ];
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
    public function qrCode()
    {
        return $this->belongsTo(Link::class, 'link_id');
    }
}
