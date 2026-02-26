@extends('admin.layout.master')
@section('title', 'Living Legacy | Cart')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h4 class="page-title mb-1">Cart</h4>
                    <p class="text-muted mb-0">Review your items and proceed to checkout</p>
                </div>
            </div>

            @if($cartItems->isEmpty())
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5 px-4">
                                <div class="rounded-circle bg-light d-inline-flex p-4 mb-3">
                                    <i class="uil uil-shopping-cart-alt display-4 text-muted"></i>
                                </div>
                                <h5 class="mb-2">Your cart is empty</h5>
                                <p class="text-muted mb-4">Browse our catalog and add products to get started.</p>
                                <a href="{{ route('reseller.products') }}" class="btn btn-primary px-4">
                                    <i class="uil uil-shopping-bag me-2"></i>Browse Products
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0 ps-4">Product</th>
                                                <th class="border-0">SKU</th>
                                                <th class="border-0 text-center" style="width: 100px;">Quantity</th>
                                                <th class="border-0 text-end">Price</th>
                                                <th class="border-0 text-end">Subtotal</th>
                                                <th class="border-0 pe-4" style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cartItems as $item)
                                                <tr data-product-id="{{ $item['product_id'] }}" data-price="{{ $item['price'] }}">
                                                    <td class="ps-4 fw-medium">{{ $item['name'] }}</td>
                                                    <td class="text-muted">{{ $item['sku'] }}</td>
                                                    <td class="text-center">
                                                        <input type="number" class="form-control form-control-sm cart-qty mx-auto" style="width: 70px;" value="{{ $item['quantity'] }}" min="1" data-product-id="{{ $item['product_id'] }}">
                                                    </td>
                                                    <td class="text-end">${{ number_format($item['price'], 2) }}</td>
                                                    <td class="text-end fw-medium subtotal">${{ number_format($item['subtotal'], 2) }}</td>
                                                    <td class="pe-4">
                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-from-cart" data-product-id="{{ $item['product_id'] }}" title="Remove">
                                                            <i class="uil uil-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3 mt-4 p-4 bg-light rounded-bottom">
                                    <a href="{{ route('reseller.products') }}" class="btn btn-outline-secondary">
                                        <i class="uil uil-arrow-left me-2"></i>Continue Shopping
                                    </a>
                                    <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                                        <div class="fs-5">Total: <span class="fw-bold text-primary" id="cartTotal">${{ number_format($total, 2) }}</span></div>
                                        <form action="{{ route('reseller.checkout') }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="uil uil-credit-card me-2"></i>Proceed to Checkout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            function updateRowSubtotal($row, qty, price) {
                var subtotal = (parseFloat(price) * parseInt(qty, 10)).toFixed(2);
                $row.find('.subtotal').text('$' + subtotal);
            }

            function recalcTotal() {
                var total = 0;
                $('tr[data-product-id]').each(function() {
                    var qty = parseInt($(this).find('.cart-qty').val(), 10) || 0;
                    var price = parseFloat($(this).data('price'));
                    total += price * qty;
                });
                $('#cartTotal').text('$' + total.toFixed(2));
            }

            $(document).on('change', '.cart-qty', function() {
                var $input = $(this);
                var productId = $input.data('product-id');
                var qty = parseInt($input.val(), 10);
                var $row = $input.closest('tr');
                var price = parseFloat($row.data('price'));

                $.post("{{ route('reseller.cart.update') }}", {
                    _token: "{{ csrf_token() }}",
                    product_id: productId,
                    quantity: qty
                })
                    .done(function(r) {
                        if (qty <= 0) {
                            $row.remove();
                            if ($('tr[data-product-id]').length === 0) {
                                location.reload();
                            }
                        } else {
                            updateRowSubtotal($row, qty, price);
                        }
                        recalcTotal();
                    });
            });

            $(document).on('click', '.remove-from-cart', function() {
                var productId = $(this).data('product-id');
                var $row = $(this).closest('tr');

                $.post("{{ route('reseller.cart.remove') }}", {
                    _token: "{{ csrf_token() }}",
                    product_id: productId
                })
                    .done(function(r) {
                        $row.remove();
                        if ($('tr[data-product-id]').length === 0) {
                            location.reload();
                        } else {
                            recalcTotal();
                        }
                        toastr.success('Item removed', 'Success');
                    });
            });
        });
    </script>
@endpush
