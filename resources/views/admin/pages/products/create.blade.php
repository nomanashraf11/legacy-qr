@extends('admin.layout.master')
@section('title', 'Living Legacy | Add Product')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="{{ route('admin.products') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                        <i class="uil uil-arrow-left me-1"></i> Back to Products
                    </a>
                    <h4 class="page-title mb-1">Add Product</h4>
                    <p class="text-muted mb-0">Create a new product for the reseller catalog</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}" placeholder="e.g. MED-001" required>
                                    <small class="text-muted">Unique identifier; will be uppercased</small>
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                    <small class="text-muted">JPEG, PNG, GIF, WebP. Max 5MB. Stored in AWS S3.</small>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">Base Price ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                                        <small class="text-muted">Default price; add tiers below for quantity discounts</small>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                        <input type="number" min="0" class="form-control @error('stock') is-invalid @enderror" id="stock" name="stock" value="{{ old('stock', 0) }}" required>
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Product description for resellers...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h6 class="mb-2">Tiered Pricing (optional)</h6>
                                <p class="text-muted small mb-3">Set quantity ranges with different prices. E.g. 1–10 units = $5, 11–50 = $4, 51+ = $3. Use 999999 for max to mean "and above".</p>
                                <div id="priceTiersContainer"></div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mb-4" id="addTier"><i class="uil uil-plus me-1"></i>Add tier</button>

                                <button type="submit" class="btn btn-primary">
                                    <i class="uil uil-check me-1"></i> Create Product
                                </button>
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
    var tierIndex = 0;
    function addTierRow(min, max, price) {
        var html = '<div class="price-tier-row row g-2 mb-2 align-items-end">' +
            '<div class="col-3"><label class="form-label small">Min Qty</label><input type="number" name="price_tiers[' + tierIndex + '][min_quantity]" class="form-control form-control-sm" value="' + (min || 1) + '" min="0"></div>' +
            '<div class="col-3"><label class="form-label small">Max Qty</label><input type="number" name="price_tiers[' + tierIndex + '][max_quantity]" class="form-control form-control-sm" value="' + (max || 999999) + '" min="0" placeholder="999999=no limit"></div>' +
            '<div class="col-3"><label class="form-label small">Price ($)</label><input type="number" step="0.01" name="price_tiers[' + tierIndex + '][price]" class="form-control form-control-sm" value="' + (price || '') + '" min="0"></div>' +
            '<div class="col-2"><button type="button" class="btn btn-outline-danger btn-sm remove-tier"><i class="uil uil-trash-alt"></i></button></div></div>';
        document.getElementById('priceTiersContainer').insertAdjacentHTML('beforeend', html);
        tierIndex++;
    }
    document.getElementById('addTier').addEventListener('click', function() { addTierRow(1, 999999, ''); });
    document.getElementById('priceTiersContainer').addEventListener('click', function(e) {
        if (e.target.closest('.remove-tier')) e.target.closest('.price-tier-row').remove();
    });
})();
</script>
@endpush
@endsection
