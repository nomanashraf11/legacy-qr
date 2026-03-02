<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'name',
        'number',
        'version_type',
    ];
    public function links()
    {
        return $this->hasMany(Link::class);
    }
    public function scopeHasAvailableLinks($query)
    {
        return $query->whereHas('links', function ($query) {
            $query->whereNull('local_user_id');
        });
    }

    public function availableLinks()
    {
        return $this->links()->whereNull('local_user_id')->get();
    }
}
