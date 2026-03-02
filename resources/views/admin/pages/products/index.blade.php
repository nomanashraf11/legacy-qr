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
                        <div class="card border-0 rounded-3 overflow-hidden shadow-sm h-100 product-card-modern">
                            <div class="position-relative overflow-hidden d-flex align-items-center justify-content-center product-card-image bg-light" style="height: 200px;">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" class="w-100 h-100" alt="{{ $product->name }}" style="object-fit: contain;">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                        <i class="uil uil-image display-1 text-muted opacity-50"></i>
                                    </div>
                                @endif
                                <span class="badge rounded-pill bg-dark position-absolute top-0 end-0 m-2 px-2 py-1">{{ $product->sku }}</span>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-end align-items-start mb-2">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary btn-sm rounded-2">
                                            <i class="uil uil-pen"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-2"><i class="uil uil-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <h5 class="card-title fw-semibold text-dark mb-2">{{ $product->name }}</h5>
                                <p class="text-success fw-bold fs-5 mb-2">
                                    ${{ number_format($product->price, 2) }}
                                    @if($product->priceTiers->isNotEmpty())
                                        <span class="badge bg-info bg-opacity-25 text-info ms-1">tiered</span>
                                    @endif
                                </p>
                                @if($product->priceTiers->isNotEmpty())
                                    <ul class="list-unstyled small text-muted mb-2">
                                        @foreach($product->priceTiers as $tier)
                                            <li>{{ $tier->min_quantity }}-{{ $tier->max_quantity >= 999999 ? '∞' : $tier->max_quantity }}: ${{ number_format($tier->price, 2) }}/ea</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <p class="text-muted small mb-0"><i class="uil uil-box me-1"></i>{{ $product->stock }} in stock</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 rounded-3 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="uil uil-shopping-bag display-4 text-muted mb-3 opacity-50"></i>
                                <h5 class="fw-semibold">No products</h5>
                                <p class="text-muted mb-0">Click "Add Product" above to create your first product.</p>
                                <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-3 rounded-2">Add Product</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
