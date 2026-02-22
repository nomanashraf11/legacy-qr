<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::updateOrCreate(
            ['sku' => 'LLQR01'],
            [
                'name' => 'Living Legacy QR Medallion - Serenity',
                'price' => 49.99,
                'stock' => 100,
            ]
        );
        Product::updateOrCreate(
            ['sku' => 'LLQR02'],
            [
                'name' => 'Living Legacy QR Medallion - Elegant',
                'price' => 49.99,
                'stock' => 100,
            ]
        );
    }
}
