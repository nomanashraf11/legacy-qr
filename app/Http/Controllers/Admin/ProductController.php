<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPriceTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('priceTiers')->orderBy('sku')->get();
        return view('admin.pages.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.pages.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create([
                'name' => $request->name,
                'sku' => strtoupper(Str::slug($request->sku, '')),
                'price' => $request->price,
                'stock' => $request->stock,
                'description' => $request->description ?: null,
            ]);

            if ($request->hasFile('image')) {
                $product->update(['image' => $this->storeProductImage($product, $request->file('image'))]);
            }

            $this->syncPriceTiers($product, $request);

            DB::commit();
            return redirect()->route('admin.products')->with('status', true)->with('message', 'Product created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('status', false)->with('message', $e->getMessage());
        }
    }

    public function edit(Product $product)
    {
        $product->load('priceTiers');
        return view('admin.pages.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $product->update([
                'name' => $request->name,
                'price' => $request->price,
                'stock' => $request->stock,
                'description' => $request->description ?: null,
            ]);

            if ($request->hasFile('image')) {
                $this->deleteProductImage($product);
                $product->update(['image' => $this->storeProductImage($product, $request->file('image'))]);
            }

            $this->syncPriceTiers($product, $request);

            DB::commit();
            return redirect()->route('admin.products')->with('status', true)->with('message', 'Product updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('status', false)->with('message', $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        DB::beginTransaction();
        try {
            $this->deleteProductImage($product);
            $product->delete();
            DB::commit();
            return redirect()->route('admin.products')->with('status', true)->with('message', 'Product deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('admin.products')->with('status', false)->with('message', $e->getMessage());
        }
    }

    protected function storeProductImage(Product $product, $file): string
    {
        $disk = config('filesystems.default');
        $dir = 'images/products';
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = $product->id . '_' . time() . '.' . $ext;

        if ($disk === 's3') {
            Storage::disk('s3')->putFileAs($dir, $file, $filename);
            return $dir . '/' . $filename;
        }

        $path = public_path($dir);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file->move($path, $filename);
        return $dir . '/' . $filename;
    }

    protected function deleteProductImage(Product $product): void
    {
        if (!$product->image) {
            return;
        }
        try {
            $disk = config('filesystems.default');
            if ($disk === 's3' && Storage::disk('s3')->exists($product->image)) {
                Storage::disk('s3')->delete($product->image);
            } elseif ($disk !== 's3' && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
        } catch (\Throwable $e) {
            // Log but don't fail
        }
    }

    protected function syncPriceTiers(Product $product, Request $request): void
    {
        $product->priceTiers()->delete();

        $tiers = $request->input('price_tiers', []);
        if (!is_array($tiers)) {
            return;
        }

        foreach ($tiers as $t) {
            $min = (int) ($t['min_quantity'] ?? 0);
            $max = (int) ($t['max_quantity'] ?? 0);
            $price = (float) ($t['price'] ?? 0);
            if ($min >= 0 && $max >= $min && $price >= 0) {
                $product->priceTiers()->create([
                    'min_quantity' => $min,
                    'max_quantity' => $max,
                    'price' => $price,
                ]);
            }
        }
    }
}
