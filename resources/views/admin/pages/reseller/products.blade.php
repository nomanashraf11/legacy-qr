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
                    <a href="{{ route('reseller.cart') }}" class="btn btn-primary position-relative px-4">
                        <i class="uil uil-shopping-cart-alt me-2"></i>Cart
                        @if($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $cartCount }}</span>
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
                        <option value="medallion">Medallions</option>
                    </select>
                </div>
            </div>

            <div class="row g-4" id="productsList">
                @forelse($products as $product)
                    <div class="col-12 col-sm-6 col-xl-4 product-card" data-search="{{ strtolower($product->name . ' ' . $product->sku) }}">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="position-relative overflow-hidden rounded-top">
                                <img src="{{ asset('assets/images/products/living-legacy-medallion.png') }}" class="card-img-top" alt="{{ $product->name }}" style="object-fit: cover; height: 220px; background: #f8f9fa;">
                                <span class="badge bg-dark position-absolute top-0 end-0 m-2">{{ $product->sku }}</span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-2">{{ $product->name }}</h5>
                                @if($product->description)
                                    <p class="text-muted small mb-2">{{ Str::limit($product->description, 100) }}</p>
                                @endif
                                <p class="text-success fw-semibold fs-4 mb-2">${{ number_format($product->price, 2) }}</p>
                                <p class="text-muted small mb-3">{{ $product->stock }} in stock</p>
                                <form class="add-to-cart-form d-flex align-items-center gap-2 mt-auto" data-product-id="{{ $product->id }}">
                                    @csrf
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="form-control form-control-sm" style="width: 72px;">
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                        <i class="uil uil-plus me-1"></i>Add to cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="uil uil-shopping-bag display-4 text-muted mb-3"></i>
                                <h5>No products available</h5>
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
