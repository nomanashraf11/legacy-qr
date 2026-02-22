@extends('admin.layout.master')
@section('title', 'Living Legacy | Cart')
@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Cart</h4>
                    </div>
                </div>
            </div>

            @if($cartItems->isEmpty())
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="uil uil-shopping-cart-alt display-4 text-muted mb-3"></i>
                                <h5>Your cart is empty</h5>
                                <p class="text-muted">Browse our catalog and add products to your cart.</p>
                                <a href="{{ route('reseller.products') }}" class="btn btn-primary">Browse Products</a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>SKU</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Subtotal</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cartItems as $item)
                                                <tr data-product-id="{{ $item['product_id'] }}" data-price="{{ $item['price'] }}">
                                                    <td>{{ $item['name'] }}</td>
                                                    <td>{{ $item['sku'] }}</td>
                                                    <td class="text-center">
                                                        <input type="number" class="form-control form-control-sm d-inline-block cart-qty" style="width: 70px;"
                                                               value="{{ $item['quantity'] }}" min="1" data-product-id="{{ $item['product_id'] }}">
                                                    </td>
                                                    <td class="text-end">${{ number_format($item['price'], 2) }}</td>
                                                    <td class="text-end subtotal">${{ number_format($item['subtotal'], 2) }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-from-cart" data-product-id="{{ $item['product_id'] }}">
                                                            <i class="uil uil-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <a href="{{ route('reseller.products') }}" class="btn btn-outline-secondary">
                                        <i class="uil uil-arrow-left me-1"></i>Continue Shopping
                                    </a>
                                    <div class="d-flex align-items-center gap-4">
                                        <h5 class="mb-0">Total: <span class="text-primary" id="cartTotal">${{ number_format($total, 2) }}</span></h5>
                                        <form action="{{ route('reseller.checkout') }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="uil uil-credit-card me-1"></i>Proceed to Checkout
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
