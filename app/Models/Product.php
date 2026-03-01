<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sku', 'price', 'stock', 'description', 'image'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_quantity');
    }

    /**
     * Get the unit price for a given quantity (uses tiered pricing if applicable).
     */
    public function getPriceForQuantity(int $quantity): float
    {
        $tier = $this->priceTiers()
            ->where('min_quantity', '<=', $quantity)
            ->where('max_quantity', '>=', $quantity)
            ->first();

        return $tier ? (float) $tier->price : (float) $this->price;
    }

    /**
     * Get the full URL for the product image (S3 or local).
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        try {
            $disk = config('filesystems.default');
            if ($disk === 's3') {
                return Storage::disk('s3')->url($this->image);
            }
            return asset($this->image);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
