@extends('admin.layout.master')
@section('title', 'Living Legacy | Products')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="page-title mb-1">Products</h4>
                        <p class="text-muted mb-0">Manage reseller catalog – add, edit, or remove products</p>
                    </div>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                        <i class="uil uil-plus me-1"></i> Add Product
                    </a>
                </div>
            </div>
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit: cover; height: 160px; background: #f8f9fa;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                                    <i class="uil uil-image display-4 text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-secondary">{{ $product->sku }}</span>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary">
                                            <i class="uil uil-pen"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger"><i class="uil uil-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="text-success fw-semibold mb-1">${{ number_format($product->price, 2) }}
                                    @if($product->priceTiers->isNotEmpty())
                                        <small class="text-muted">(tiered)</small>
                                    @endif
                                </p>
                                <p class="text-muted small mb-0">{{ $product->stock }} in stock</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="uil uil-shopping-bag display-4 text-muted mb-3"></i>
                                <h5>No products</h5>
                                <p class="text-muted mb-0">Click "Add Product" above to create your first product.</p>
                                <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-3">Add Product</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
