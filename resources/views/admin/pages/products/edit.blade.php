@extends('admin.layout.master')
@section('title', 'Living Legacy | Edit Product')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="{{ route('admin.products') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                        <i class="uil uil-arrow-left me-1"></i> Back to Products
                    </a>
                    <h4 class="page-title mb-1">Edit Product</h4>
                    <p class="text-muted mb-0">{{ $product->sku }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    @if($product->image_url)
                                        <div class="mb-2">
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-height: 180px;">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                    <small class="text-muted">JPEG, PNG, GIF, WebP. Max 5MB. Leave empty to keep current.</small>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SKU</label>
                                    <input type="text" class="form-control bg-light" value="{{ $product->sku }}" readonly>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">Base Price ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                                        <small class="text-muted">Used when no tier matches</small>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                        <input type="number" min="0" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', $product->stock) }}" required>
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h6 class="mb-2">Tiered Pricing</h6>
                                <p class="text-muted small mb-3">Quantity ranges with different prices. Use 999999 for max to mean "and above".</p>
                                <div id="priceTiersContainer">
                                    @foreach($product->priceTiers as $i => $tier)
                                    <div class="price-tier-row row g-2 mb-2 align-items-end">
                                        <div class="col-3">
                                            <label class="form-label small">Min Qty</label>
                                            <input type="number" name="price_tiers[{{ $i }}][min_quantity]" class="form-control form-control-sm" value="{{ $tier->min_quantity }}" min="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Max Qty</label>
                                            <input type="number" name="price_tiers[{{ $i }}][max_quantity]" class="form-control form-control-sm" value="{{ $tier->max_quantity }}" min="0">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Price ($)</label>
                                            <input type="number" step="0.01" name="price_tiers[{{ $i }}][price]" class="form-control form-control-sm" value="{{ $tier->price }}" min="0">
                                        </div>
                                        <div class="col-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-tier"><i class="uil uil-trash-alt"></i></button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="addTier"><i class="uil uil-plus me-1"></i>Add tier</button>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="uil uil-check me-1"></i> Save Changes
                                    </button>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="uil uil-trash-alt me-1"></i> Delete Product
                                        </button>
                                    </form>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
(function() {
    var tierIndex = {{ $product->priceTiers->count() }};
    document.getElementById('addTier').addEventListener('click', function() {
        var html = '<div class="price-tier-row row g-2 mb-2 align-items-end">' +
            '<div class="col-3"><label class="form-label small">Min Qty</label><input type="number" name="price_tiers[' + tierIndex + '][min_quantity]" class="form-control form-control-sm" value="1" min="0"></div>' +
            '<div class="col-3"><label class="form-label small">Max Qty</label><input type="number" name="price_tiers[' + tierIndex + '][max_quantity]" class="form-control form-control-sm" value="999999" min="0"></div>' +
            '<div class="col-3"><label class="form-label small">Price ($)</label><input type="number" step="0.01" name="price_tiers[' + tierIndex + '][price]" class="form-control form-control-sm" min="0"></div>' +
            '<div class="col-2"><button type="button" class="btn btn-outline-danger btn-sm remove-tier"><i class="uil uil-trash-alt"></i></button></div></div>';
        document.getElementById('priceTiersContainer').insertAdjacentHTML('beforeend', html);
        tierIndex++;
    });
    document.getElementById('priceTiersContainer').addEventListener('click', function(e) {
        if (e.target.closest('.remove-tier')) e.target.closest('.price-tier-row').remove();
    });
})();
</script>
@endpush
@endsection
