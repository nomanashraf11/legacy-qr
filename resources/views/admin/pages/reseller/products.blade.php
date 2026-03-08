@extends('admin.layout.master')
@section('title', 'Living Legacy | Products')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row align-items-center mb-4">
                <div class="col-12 col-md-6">
                    <h4 class="page-title mb-1">Products</h4>
                    <p class="text-muted mb-0">Browse our wholesale catalog</p>
                </div>
                <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('reseller.cart') }}" id="cartButton" class="btn btn-primary position-relative px-4">
                        <i class="uil uil-shopping-cart-alt me-2"></i>Cart
                        @if($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadge">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="uil uil-search text-muted"></i></span>
                        <input type="text" id="searchProducts" class="form-control" placeholder="Search products...">
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg-3">
                    <select id="filterCategory" class="form-select">
                        <option value="">All Categories</option>
                    </select>
                </div>
            </div>

            <div class="row g-4" id="productsList">
                @forelse($products as $product)
                    @php
                        $tiersJson = $product->priceTiers->isEmpty()
                            ? json_encode([['min' => 1, 'max' => 999999, 'price' => (float) $product->price]])
                            : $product->priceTiers->map(fn($t) => ['min' => $t->min_quantity, 'max' => $t->max_quantity, 'price' => (float) $t->price])->toJson();
                    @endphp
                    <div class="col-12 col-sm-6 col-xl-4 product-card" data-search="{{ strtolower($product->name . ' ' . $product->sku) }}">
                        <div class="card h-100 border-0 rounded-3 overflow-hidden shadow-sm product-card-modern">
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
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2 fw-semibold text-dark">{{ $product->name }}</h5>
                                @if($product->description)
                                    <p class="text-muted small mb-2 opacity-90 text-wrap text-break">{{ $product->description }}</p>
                                @endif

                                @if($product->priceTiers->isNotEmpty())
                                    <div class="mb-2">
                                        <p class="text-muted small text-uppercase mb-1 fw-semibold" style="letter-spacing: 0.5px;">Volume Pricing</p>
                                        <ul class="list-unstyled small mb-0">
                                            @foreach($product->priceTiers as $tier)
                                                <li class="text-muted">{{ $tier->min_quantity }}-{{ $tier->max_quantity >= 999999 ? '∞' : $tier->max_quantity }}: ${{ number_format($tier->price, 2) }}/ea</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-success fw-bold fs-4 product-price" data-tiers="{{ e($tiersJson) }}" data-base="{{ $product->price }}">
                                        ${{ number_format($product->price, 2) }}
                                        @if($product->priceTiers->isNotEmpty())
                                            <span class="badge bg-info bg-opacity-25 text-info ms-1">tiered</span>
                                        @endif
                                    </span>
                                    <span class="text-muted small"><i class="uil uil-box me-1"></i>{{ $product->stock }} in stock</span>
                                </div>

                                <form class="add-to-cart-form mt-auto" data-product-id="{{ $product->id }}" data-stock="{{ $product->stock }}">
                                    @csrf
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="input-group input-group-sm rounded-2" style="width: 120px;">
                                            <button type="button" class="btn btn-outline-secondary qty-minus">−</button>
                                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="form-control form-control-sm text-center qty-input" style="max-width: 50px;">
                                            <button type="button" class="btn btn-outline-secondary qty-plus">+</button>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-2">
                                            <i class="uil uil-plus me-1"></i>Add to cart
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 rounded-3 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="uil uil-shopping-bag display-4 text-muted mb-3 opacity-50"></i>
                                <h5 class="fw-semibold">No products available</h5>
                                <p class="text-muted mb-0">Check back later for new items.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            function getPriceForQty(tiers, qty) {
                if (!tiers || !tiers.length) return null;
                for (var i = 0; i < tiers.length; i++) {
                    if (qty >= tiers[i].min && qty <= tiers[i].max) return tiers[i].price;
                }
                return tiers[0] ? tiers[0].price : null;
            }

            function filterProducts() {
                var search = $('#searchProducts').val().toLowerCase();
                $('.product-card').each(function() {
                    var $card = $(this);
                    var matchesSearch = !search || $card.data('search').indexOf(search) >= 0;
                    $card.toggle(matchesSearch);
                });
            }

            $('#searchProducts').on('keyup', filterProducts);
            $('#filterCategory').on('change', filterProducts);

            $(document).on('click', '.qty-minus', function() {
                var $input = $(this).siblings('.qty-input');
                var v = Math.max(1, parseInt($input.val(), 10) - 1);
                $input.val(v);
                var $price = $input.closest('.card-body').find('.product-price');
                var tiers = $price.data('tiers');
                var p = getPriceForQty(tiers, v);
                if (p != null) $price.text('$' + p.toFixed(2));
            });
            $(document).on('click', '.qty-plus', function() {
                var $input = $(this).siblings('.qty-input');
                var max = parseInt($input.attr('max'), 10) || 999;
                var v = Math.min(max, parseInt($input.val(), 10) + 1);
                $input.val(v);
                var $price = $input.closest('.card-body').find('.product-price');
                var tiers = $price.data('tiers');
                var p = getPriceForQty(tiers, v);
                if (p != null) $price.text('$' + p.toFixed(2));
            });
            $(document).on('change', '.qty-input', function() {
                var $input = $(this);
                var v = Math.max(1, Math.min(parseInt($input.attr('max'), 10) || 999, parseInt($input.val(), 10) || 1));
                $input.val(v);
                var $price = $input.closest('.card-body').find('.product-price');
                var tiers = $price.data('tiers');
                var p = getPriceForQty(tiers, v);
                if (p != null) $price.text('$' + p.toFixed(2));
            });

            $(document).on('submit', '.add-to-cart-form', function(e) {
                e.preventDefault();
                var $form = $(this);
                var productId = $form.data('product-id');
                var quantity = parseInt($form.find('.qty-input').val(), 10) || 1;
                var $btn = $form.find('button[type=submit]');
                $btn.prop('disabled', true);

                $.post("{{ route('reseller.cart.add') }}", {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: quantity
                })
                    .done(function(r) {
                        toastr.success(r.message, "Success");
                        var $badge = $('#cartBadge');
                        if ($badge.length) {
                            $badge.text(r.cart_count);
                        } else {
                            $('#cartButton').append(
                                '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadge">' + r.cart_count + '</span>'
                            );
                        }
                        $form.find('.qty-input').val(1);
                        var $price = $form.closest('.card-body').find('.product-price');
                        var tiers = $price.data('tiers');
                        var p = getPriceForQty(tiers, 1);
                        if (p != null) $price.text('$' + p.toFixed(2));
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
