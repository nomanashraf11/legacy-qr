<?php

namespace App\Http\Controllers\Sellar;

use App\Http\Controllers\Controller;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    protected function buildCheckoutCartHash(array $cart): string
    {
        $normalized = collect($cart)
            ->map(fn($item) => [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'quantity' => (int) ($item['quantity'] ?? 0),
                'price' => (float) ($item['price'] ?? 0),
            ])
            ->sortBy('product_id')
            ->values()
            ->all();

        return hash('sha256', json_encode($normalized));
    }

    protected function ensureResellerCanPurchase(): ?RedirectResponse
    {
        $admin = User::role('admin')->first()?->admin;
        if ($admin && $admin->purchase == 1) {
            return redirect()->route('sellar.dashboard')->with('status', false)->with('message', 'Contact your admin to turn purchase ON');
        }
        $reseller = Auth::user()->reSeller;
        if (!$reseller) {
            return redirect()->route('settings')->with('status', false)
                ->with('message', 'Complete your profile to continue.')
                ->with('missing_profile_fields', ['phone' => 'Phone Number', 'address' => 'Shipping Address']);
        }
        $missing = [];
        if (empty(trim($reseller->phone ?? ''))) {
            $missing['phone'] = 'Phone Number';
        }
        if (empty(trim($reseller->shipping_address ?? '')) || $reseller->shipping_address === 'N/A') {
            $missing['address'] = 'Shipping Address';
        }
        if (!empty($missing)) {
            return redirect()->route('settings')->with('status', false)
                ->with('message', 'Please complete the following to browse products and place orders:')
                ->with('missing_profile_fields', $missing);
        }
        return null;
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->ensureResellerCanPurchase()) {
            return $redirect;
        }
        $products = Product::with('priceTiers')->where('stock', '>', 0)->get();
        $cart = session('reseller_cart', []);
        $cartCount = collect($cart)->sum('quantity');
        return view('admin.pages.reseller.products', compact('products', 'cartCount'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = session('reseller_cart', []);
        $key = array_search($request->product_id, array_column($cart, 'product_id'));
        $currentQty = ($key !== false) ? $cart[$key]['quantity'] : 0;
        $newQty = $currentQty + $request->quantity;

        if ($product->stock < $newQty) {
            return response()->json([
                'status' => false,
                'message' => "Not enough stock. Only {$product->stock} available.",
            ], 422);
        }

        $unitPrice = $product->getPriceForQuantity($request->quantity);

        if ($key !== false) {
            $cart[$key]['quantity'] = $newQty;
            $cart[$key]['price'] = (float) $product->getPriceForQuantity($newQty);
        } else {
            $cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $unitPrice,
                'quantity' => $request->quantity,
            ];
        }

        session(['reseller_cart' => $cart]);
        return response()->json([
            'status' => true,
            'message' => 'Added to cart',
            'cart_count' => collect($cart)->sum('quantity'),
        ]);
    }

    public function cart(): View|RedirectResponse
    {
        if ($redirect = $this->ensureResellerCanPurchase()) {
            return $redirect;
        }
        $cart = session('reseller_cart', []);
        $cartItems = collect($cart)->map(function ($item) {
            $item['subtotal'] = $item['price'] * $item['quantity'];
            $product = Product::with('priceTiers')->find($item['product_id']);
            $item['price_tiers'] = $product ? $product->priceTiers : collect();
            $item['is_tiered'] = $product && $product->priceTiers->isNotEmpty();
            return $item;
        });
        $total = $cartItems->sum('subtotal');
        return view('admin.pages.reseller.cart', compact('cartItems', 'total'));
    }

    public function updateCart(Request $request)
    {
        $productId = $request->product_id;
        $quantity = (int) $request->quantity;

        if ($quantity <= 0) {
            $cart = session('reseller_cart', []);
            $cart = array_values(array_filter($cart, fn($i) => $i['product_id'] != $productId));
            session(['reseller_cart' => $cart]);
            return response()->json(['status' => true, 'cart_count' => collect($cart)->sum('quantity')]);
        }

        $cart = session('reseller_cart', []);
        $updatedPrice = null;
        $itemFound = false;
        foreach ($cart as $i => $item) {
            if ($item['product_id'] == $productId) {
                $itemFound = true;
                $product = Product::find($productId);
                if (!$product) {
                    return response()->json(['status' => false, 'message' => 'Product not found'], 404);
                }
                if ($product->stock < $quantity) {
                    return response()->json([
                        'status' => false,
                        'message' => "Only {$product->stock} in stock.",
                        'max_quantity' => $product->stock,
                    ], 422);
                }
                $cart[$i]['quantity'] = $quantity;
                $cart[$i]['price'] = (float) $product->getPriceForQuantity($quantity);
                $updatedPrice = $cart[$i]['price'];
                break;
            }
        }
        if (!$itemFound) {
            return response()->json(['status' => false, 'message' => 'Item not in cart'], 404);
        }
        session(['reseller_cart' => $cart]);
        return response()->json([
            'status' => true,
            'cart_count' => collect($cart)->sum('quantity'),
            'price' => $updatedPrice,
            'quantity' => $quantity,
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = session('reseller_cart', []);
        $cart = array_values(array_filter($cart, fn($i) => $i['product_id'] != $request->product_id));
        session(['reseller_cart' => $cart]);
        return response()->json(['status' => true, 'cart_count' => collect($cart)->sum('quantity')]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureResellerCanPurchase()) {
            return $redirect;
        }
        $cart = session('reseller_cart', []);
        if (empty($cart)) {
            return redirect()->route('reseller.products')->with('status', false)->with('message', 'Your cart is empty.');
        }

        $lineItems = [];
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->stock < $item['quantity']) {
                return redirect()->route('reseller.cart')->with('status', false)->with('message', "Insufficient stock for {$item['name']}");
            }
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $item['name'], 'description' => $item['sku']],
                    'unit_amount' => (int) round($item['price'] * 100),
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Payment is not configured. Please contact support.');
        }
        $stripe = new \Stripe\StripeClient($stripeSecret);
        $cartHash = $this->buildCheckoutCartHash($cart);
        $session = $stripe->checkout->sessions->create([
            'success_url' => route('reseller.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('reseller.cart'),
            'customer_email' => Auth::user()->email,
            'payment_method_types' => ['card', 'link'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'allow_promotion_codes' => true,
            'shipping_address_collection' => [
                'allowed_countries' => ['US'],
            ],
            'shipping_options' => [[
                'shipping_rate_data' => [
                    'type' => 'fixed_amount',
                    'fixed_amount' => [
                        'amount' => 0,
                        'currency' => 'usd',
                    ],
                    'display_name' => 'Standard Shipping',
                    'delivery_estimate' => [
                        'minimum' => ['unit' => 'business_day', 'value' => 3],
                        'maximum' => ['unit' => 'business_day', 'value' => 7],
                    ],
                ],
            ]],
            'metadata' => [
                'app_source' => 'reseller_cart_checkout',
                'reseller_user_id' => (string) Auth::id(),
                'cart_hash' => $cartHash,
            ],
        ]);

        session([
            'reseller_checkout_cart' => $cart,
            'reseller_checkout_context' => [
                'session_id' => $session->id,
                'cart_hash' => $cartHash,
                'user_id' => Auth::id(),
            ],
        ]);
        return redirect($session->url);
    }

    public function checkoutSuccess(Request $request): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Invalid checkout session.');
        }

        $checkoutContext = session('reseller_checkout_context', []);
        if (
            empty($checkoutContext['session_id']) ||
            !hash_equals((string) $checkoutContext['session_id'], $sessionId) ||
            (int) ($checkoutContext['user_id'] ?? 0) !== (int) Auth::id()
        ) {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Checkout session validation failed.');
        }

        $cart = session('reseller_checkout_cart', []);
        if (empty($cart)) {
            return redirect()->route('reseller.products')->with('status', false)->with('message', 'Session expired.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return redirect()->route('reseller.products')->with('status', false)->with('message', 'Payment is not configured.');
        }

        $existingOrder = Order::where('stripe_checkout_session_id', $sessionId)->first();
        if ($existingOrder) {
            session()->forget(['reseller_cart', 'reseller_checkout_cart', 'reseller_checkout_context']);
            return redirect()->route('myOrders')->with('status', true)->with('message', 'Order already processed for this payment.');
        }

        $stripe = new \Stripe\StripeClient($stripeSecret);
        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            \Log::warning('Stripe session retrieval failed: ' . $e->getMessage());
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Unable to verify payment session.');
        }

        if ($session->payment_status !== 'paid') {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Payment was not completed.');
        }

        $sessionEmail = strtolower(trim((string) ($session->customer_details->email ?? $session->customer_email ?? '')));
        $expectedEmail = strtolower(trim((string) Auth::user()->email));
        if ($sessionEmail !== '' && $sessionEmail !== $expectedEmail) {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Checkout session does not match your account.');
        }

        $metadataUserId = (string) ($session->metadata->reseller_user_id ?? '');
        if ($metadataUserId !== (string) Auth::id()) {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Checkout session ownership mismatch.');
        }

        $cartHash = $this->buildCheckoutCartHash($cart);
        if (
            (string) ($session->metadata->cart_hash ?? '') !== $cartHash ||
            (string) ($checkoutContext['cart_hash'] ?? '') !== $cartHash
        ) {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Cart changed during checkout. Please try again.');
        }

        // Re-validate stock before creating order (may have changed since checkout)
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->stock < $item['quantity']) {
                return redirect()->route('reseller.cart')->with('status', false)->with('message', "Insufficient stock for {$item['name']}. Please update your cart.");
            }
        }

        $totalQty = collect($cart)->sum('quantity');
        $totalAmount = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);

        DB::beginTransaction();
        try {
            $shippingDetails = $session->shipping_details ?? null;
            $shippingAddress = $shippingDetails->address ?? null;
            $shippingAddressPayload = $shippingAddress ? [
                'line1' => $shippingAddress->line1 ?? null,
                'line2' => $shippingAddress->line2 ?? null,
                'city' => $shippingAddress->city ?? null,
                'state' => $shippingAddress->state ?? null,
                'postal_code' => $shippingAddress->postal_code ?? null,
                'country' => $shippingAddress->country ?? null,
            ] : null;

            $order = Order::create([
                'uuid' => Str::uuid(),
                'qr_codes' => $totalQty,
                'amount' => $totalAmount,
                'status' => 0,
                're_seller_id' => Auth::user()->reSeller->id,
                'tracking_details' => 'Order is pending',
                'stripe_checkout_session_id' => $sessionId,
                'stripe_payment_intent_id' => is_string($session->payment_intent ?? null) ? $session->payment_intent : ($session->payment_intent->id ?? null),
                'stripe_customer_id' => is_string($session->customer ?? null) ? $session->customer : ($session->customer->id ?? null),
                'stripe_payment_status' => $session->payment_status ?? null,
                'stripe_shipping_name' => $shippingDetails->name ?? null,
                'stripe_shipping_phone' => $shippingDetails->phone ?? null,
                'stripe_shipping_address' => $shippingAddressPayload,
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
                Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Checkout success error: ' . $e->getMessage());
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Order could not be completed. Please try again.');
        }

        try {
            Mail::to(Auth::user()->email)->send(new OrderPlacedMail([
                'userName' => Auth::user()->name,
                'orderNumber' => $order->id,
                'items' => $totalQty . ' item(s)',
                'amount' => number_format($totalAmount, 2),
            ]));
        } catch (\Throwable $e) {
            \Log::warning('Order placed email failed: ' . $e->getMessage());
        }

        $adminEmail = config('mail.admin_notification_email');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new AdminNewOrderMail([
                    'orderNumber' => $order->id,
                    'resellerName' => Auth::user()->name,
                    'items' => $totalQty . ' item(s)',
                    'amount' => number_format($totalAmount, 2),
                    'adminUrl' => url('/orders'),
                ]));
            } catch (\Throwable $e) {
                \Log::warning('Admin new order notification failed: ' . $e->getMessage());
            }
        }

        session()->forget(['reseller_cart', 'reseller_checkout_cart', 'reseller_checkout_context']);
        return redirect()->route('myOrders')->with('status', true)->with('message', 'Order placed successfully!');
    }
}
