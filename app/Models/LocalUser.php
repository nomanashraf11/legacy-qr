<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone',
        'uuid',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function links()
    {
        return $this->hasMany(Link::class);
    }
}
