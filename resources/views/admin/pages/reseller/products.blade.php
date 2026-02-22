@extends('admin.layout.master')
@section('title', 'Living Legacy | Products')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between">
                        <h4 class="page-title mb-0">Products</h4>
                        <a href="{{ route('reseller.cart') }}" class="btn btn-primary position-relative">
                            <i class="uil uil-shopping-cart-alt me-1"></i>Cart
                            @if($cartCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <p class="text-muted mb-0">Browse our wholesale catalog</p>
                </div>
                <div class="col-md-6 mt-2">
                    <input type="text" id="searchProducts" class="form-control" placeholder="Search products...">
                </div>
                <div class="col-md-3 mt-2">
                    <select id="filterCategory" class="form-select">
                        <option value="">All Categories</option>
                        <option value="medallion">Medallions</option>
                    </select>
                </div>
            </div>

            <div class="row" id="productsList">
                @forelse($products as $product)
                    <div class="col-12 col-md-6 col-xl-4 product-card" data-search="{{ strtolower($product->name . ' ' . $product->sku) }}">
                        <div class="card h-100">
                            <img src="{{ asset('assets/images/products/living-legacy-medallion.png') }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit: cover; height: 220px; background: #f5f5f5;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="text-muted small mb-2">{{ $product->sku }}</p>
                                <p class="fw-bold text-primary fs-4 mb-3">${{ number_format($product->price, 2) }}</p>
                                <p class="text-muted small mb-3">{{ $product->stock }} in stock</p>
                                <div class="mt-auto">
                                    <form class="add-to-cart-form d-flex align-items-center gap-2" data-product-id="{{ $product->id }}">
                                        @csrf
                                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                                               class="form-control form-control-sm" style="width: 80px;">
                                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                            <i class="uil uil-plus me-1"></i>Add
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">No products available at the moment.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            function filterProducts() {
                var search = $('#searchProducts').val().toLowerCase();
                var category = $('#filterCategory').val();
                $('.product-card').each(function() {
                    var $card = $(this);
                    var matchesSearch = !search || $card.data('search').indexOf(search) >= 0;
                    var matchesCategory = !category || $card.data('search').indexOf('medallion') >= 0;
                    $card.toggle(matchesSearch && matchesCategory);
                });
            }

            $('#searchProducts').on('keyup', filterProducts);
            $('#filterCategory').on('change', filterProducts);

            $(document).on('submit', '.add-to-cart-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var productId = $form.data('product-id');
                var quantity = parseInt($form.find('[name=quantity]').val(), 10);
                var $btn = $form.find('button[type=submit]');
                $btn.prop('disabled', true);

                $.post("{{ route('reseller.cart.add') }}", {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: quantity
                })
                    .done(function(r) {
                        toastr.success(r.message, "Success");
                        var $cartBadge = $('.page-title-box .btn-primary .badge');
                        if ($cartBadge.length) {
                            $cartBadge.text(r.cart_count);
                        } else {
                            $('.page-title-box .btn-primary').append(
                                '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' + r.cart_count + '</span>'
                            );
                        }
                        $form.find('[name=quantity]').val(1);
                    })
                    .fail(function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Could not add to cart';
                        toastr.error(msg, "Error");
                    })
                    .always(function() {
                        $btn.prop('disabled', false);
                    });
            });
        });
    </script>
@endpush
