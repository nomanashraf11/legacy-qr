@extends('admin.layout.master')
@section('title', 'Living Legacy | Products')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="page-title mb-1">Products</h4>
                    <p class="text-muted mb-0">Manage reseller catalog – edit price, description, and stock</p>
                </div>
            </div>
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-secondary">{{ $product->sku }}</span>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="uil uil-pen"></i> Edit
                                    </a>
                                </div>
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="text-success fw-semibold mb-1">${{ number_format($product->price, 2) }}</p>
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
                                <p class="text-muted mb-0">Run <code>php artisan db:seed --class=ProductSeeder</code> to add products.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
