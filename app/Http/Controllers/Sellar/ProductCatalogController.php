<?php

namespace App\Http\Controllers\Sellar;

use App\Http\Controllers\Controller;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    protected function cartSessionKey(): string
    {
        return 'reseller_cart_' . (string) Auth::id();
    }

    protected function checkoutCartSessionKey(): string
    {
        return 'reseller_checkout_cart_' . (string) Auth::id();
    }

    protected function checkoutContextSessionKey(): string
    {
        return 'reseller_checkout_context_' . (string) Auth::id();
    }

    protected function net30ContextSessionKey(): string
    {
        return 'reseller_net30_context_' . (string) Auth::id();
    }

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

    protected function getCheckoutAllowedCountries(): array
    {
        $countries = config('services.stripe.shipping_allowed_countries', ['US', 'CA']);
        if (!is_array($countries) || empty($countries)) {
            return ['US', 'CA'];
        }
        return array_values(array_filter(array_map(
            fn($country) => strtoupper(trim((string) $country)),
            $countries
        )));
    }

    protected function resolveStandardShippingAmount(array $cart): int
    {
        $totalQty = max(0, (int) collect($cart)->sum('quantity'));
        $tier1Amount = max(0, (int) config('services.stripe.shipping_standard_amount', 999));
        $tier2Amount = max(0, (int) config('services.stripe.shipping_tier2_amount', 1299));
        $tier3Amount = max(0, (int) config('services.stripe.shipping_tier3_amount', 1599));
        $tier2MinQty = max(1, (int) config('services.stripe.shipping_tier2_min_qty', 25));
        $tier3MinQty = max($tier2MinQty + 1, (int) config('services.stripe.shipping_tier3_min_qty', 50));

        if ($totalQty >= $tier3MinQty) {
            return $tier3Amount;
        }
        if ($totalQty >= $tier2MinQty) {
            return $tier2Amount;
        }

        return $tier1Amount;
    }

    protected function buildCheckoutShippingOptions(array $cart): array
    {
        $standardId = (string) config('services.stripe.shipping_rate_standard_id', '');
        if ($standardId !== '') {
            return [['shipping_rate' => $standardId]];
        }

        $currency = strtolower((string) config('services.stripe.shipping_currency', 'usd'));
        $standardAmount = $this->resolveStandardShippingAmount($cart);

        return [
            [
                'shipping_rate_data' => [
                    'type' => 'fixed_amount',
                    'fixed_amount' => [
                        'amount' => $standardAmount,
                        'currency' => $currency,
                    ],
                    'display_name' => 'Standard Shipping',
                    'delivery_estimate' => [
                        'minimum' => ['unit' => 'business_day', 'value' => 3],
                        'maximum' => ['unit' => 'business_day', 'value' => 5],
                    ],
                ],
            ],
        ];
    }

    protected function createOrGetStripeCustomer(\Stripe\StripeClient $stripe, User $user, string $cartHash): object
    {
        $existing = $stripe->customers->all([
            'email' => $user->email,
            'limit' => 1,
        ]);
        if (!empty($existing->data)) {
            return $existing->data[0];
        }

        $reseller = $user->reSeller;
        $addressParts = $reseller?->getAddressParts() ?? [];
        $street = trim((string) ($addressParts['street_address'] ?? ''));
        $city = trim((string) ($addressParts['city'] ?? ''));
        $state = trim((string) ($addressParts['state'] ?? ''));
        $postalCode = trim((string) ($addressParts['postal_code'] ?? ''));

        $customerPayload = [
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $reseller?->phone ?: null,
            'metadata' => [
                'app_source' => 'reseller_net30_checkout',
                'reseller_user_id' => (string) $user->id,
                'cart_hash' => $cartHash,
            ],
        ];

        if ($street !== '' && $city !== '' && $state !== '') {
            $customerPayload['address'] = array_filter([
                'line1' => $street,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postalCode ?: null,
                'country' => 'US',
            ], fn($v) => $v !== null && $v !== '');
        }

        return $stripe->customers->create($customerPayload);
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->ensureResellerCanPurchase()) {
            return $redirect;
        }
        $products = Product::with('priceTiers')->where('stock', '>', 0)->get();
        $cart = session($this->cartSessionKey(), []);
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
        $cart = session($this->cartSessionKey(), []);
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

        session([$this->cartSessionKey() => $cart]);
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
        $cart = session($this->cartSessionKey(), []);
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
            $cart = session($this->cartSessionKey(), []);
            $cart = array_values(array_filter($cart, fn($i) => $i['product_id'] != $productId));
            session([$this->cartSessionKey() => $cart]);
            return response()->json(['status' => true, 'cart_count' => collect($cart)->sum('quantity')]);
        }

        $cart = session($this->cartSessionKey(), []);
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
        session([$this->cartSessionKey() => $cart]);
        return response()->json([
            'status' => true,
            'cart_count' => collect($cart)->sum('quantity'),
            'price' => $updatedPrice,
            'quantity' => $quantity,
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = session($this->cartSessionKey(), []);
        $cart = array_values(array_filter($cart, fn($i) => $i['product_id'] != $request->product_id));
        session([$this->cartSessionKey() => $cart]);
        return response()->json(['status' => true, 'cart_count' => collect($cart)->sum('quantity')]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureResellerCanPurchase()) {
            return $redirect;
        }
        $cart = session($this->cartSessionKey(), []);
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
                'allowed_countries' => $this->getCheckoutAllowedCountries(),
            ],
            'shipping_options' => $this->buildCheckoutShippingOptions($cart),
            'metadata' => [
                'app_source' => 'reseller_cart_checkout',
                'reseller_user_id' => (string) Auth::id(),
                'cart_hash' => $cartHash,
            ],
        ]);

        session([
            $this->checkoutCartSessionKey() => $cart,
            $this->checkoutContextSessionKey() => [
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

        $checkoutContext = session($this->checkoutContextSessionKey(), []);
        if (
            empty($checkoutContext['session_id']) ||
            !hash_equals((string) $checkoutContext['session_id'], $sessionId) ||
            (int) ($checkoutContext['user_id'] ?? 0) !== (int) Auth::id()
        ) {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Checkout session validation failed.');
        }

        $cart = session($this->checkoutCartSessionKey(), []);
        if (empty($cart)) {
            return redirect()->route('reseller.products')->with('status', false)->with('message', 'Session expired.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return redirect()->route('reseller.products')->with('status', false)->with('message', 'Payment is not configured.');
        }

        $existingOrder = Order::where('stripe_checkout_session_id', $sessionId)->first();
        if ($existingOrder) {
            session()->forget([$this->cartSessionKey(), $this->checkoutCartSessionKey(), $this->checkoutContextSessionKey()]);
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
                'payment_method' => 'card_checkout',
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

        session()->forget([$this->cartSessionKey(), $this->checkoutCartSessionKey(), $this->checkoutContextSessionKey()]);
        return redirect()->route('myOrders')->with('status', true)->with('message', 'Order placed successfully!');
    }

    public function checkoutNet30(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureResellerCanPurchase()) {
            return $redirect;
        }

        $cart = session($this->cartSessionKey(), []);
        if (empty($cart)) {
            return redirect()->route('reseller.products')->with('status', false)->with('message', 'Your cart is empty.');
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Payment is not configured. Please contact support.');
        }

        $cartHash = $this->buildCheckoutCartHash($cart);
        $lastNet30Context = session($this->net30ContextSessionKey(), []);
        if (
            !empty($lastNet30Context['cart_hash']) &&
            (string) $lastNet30Context['cart_hash'] === $cartHash &&
            !empty($lastNet30Context['created_at']) &&
            Carbon::parse((string) $lastNet30Context['created_at'])->greaterThan(now()->subMinutes(2))
        ) {
            return redirect()->route('myOrders')->with('status', true)->with('message', 'A Net 30 invoice for this cart was just created. Please check your email.');
        }

        $lineItems = [];
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->stock < $item['quantity']) {
                return redirect()->route('reseller.cart')->with('status', false)->with('message', "Insufficient stock for {$item['name']}");
            }
            $lineItems[] = [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'quantity' => (int) $item['quantity'],
                'unit_amount' => (int) round($item['price'] * 100),
                'unit_price' => (float) $item['price'],
            ];
        }

        $user = Auth::user();
        $reSeller = $user->reSeller;
        $stripe = new \Stripe\StripeClient($stripeSecret);
        $dueDays = max(1, (int) config('services.stripe.net30_due_days', 30));

        DB::beginTransaction();
        try {
            $customer = $this->createOrGetStripeCustomer($stripe, $user, $cartHash);

            foreach ($lineItems as $item) {
                $stripe->invoiceItems->create([
                    'customer' => $customer->id,
                    'currency' => 'usd',
                    'unit_amount' => $item['unit_amount'],
                    'quantity' => $item['quantity'],
                    'description' => $item['name'],
                    'metadata' => [
                        'product_id' => (string) $item['product_id'],
                        'reseller_user_id' => (string) $user->id,
                    ],
                ]);
            }

            $invoice = $stripe->invoices->create([
                'customer' => $customer->id,
                'collection_method' => 'send_invoice',
                'days_until_due' => $dueDays,
                'auto_advance' => false,
                'metadata' => [
                    'app_source' => 'reseller_net30_checkout',
                    'reseller_user_id' => (string) $user->id,
                    'cart_hash' => $cartHash,
                ],
            ]);

            $finalized = $stripe->invoices->finalizeInvoice($invoice->id, []);
            $invoiceSent = true;
            $invoiceSendError = null;
            try {
                $stripe->invoices->sendInvoice($invoice->id, []);
            } catch (\Throwable $sendError) {
                $invoiceSent = false;
                $invoiceSendError = $sendError->getMessage();
                \Log::warning('Net 30 invoice created but sendInvoice failed', [
                    'reseller_user_id' => Auth::id(),
                    'invoice_id' => $finalized->id ?? null,
                    'error' => $invoiceSendError,
                ]);
            }

            $totalQty = collect($cart)->sum('quantity');
            $totalAmount = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);

            $order = Order::create([
                'uuid' => Str::uuid(),
                'qr_codes' => $totalQty,
                'amount' => $totalAmount,
                'status' => Order::STATUS_PENDING,
                're_seller_id' => $reSeller->id,
                'tracking_details' => $invoiceSent
                    ? 'Net 30 invoice sent. Awaiting payment.'
                    : 'Net 30 invoice created. Email send pending; please check Stripe dashboard.',
                'payment_method' => 'net30_invoice',
                'stripe_customer_id' => $customer->id,
                'stripe_payment_status' => 'unpaid',
                'stripe_invoice_id' => $finalized->id,
                'stripe_invoice_number' => $finalized->number ?? null,
                'stripe_invoice_status' => $finalized->status ?? null,
                'payment_terms_days' => $dueDays,
                'invoice_due_at' => !empty($finalized->due_date) ? Carbon::createFromTimestamp((int) $finalized->due_date) : null,
                'invoice_sent_at' => $invoiceSent ? now() : null,
                'invoice_send_status' => $invoiceSent ? 'sent' : 'failed',
                'invoice_send_error' => $invoiceSendError,
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
            \Log::error('Net 30 checkout error: ' . $e->getMessage(), [
                'reseller_user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Could not create Net 30 invoice. Please try again.');
        }

        session()->forget([$this->cartSessionKey(), $this->checkoutCartSessionKey(), $this->checkoutContextSessionKey()]);
        session([
            $this->net30ContextSessionKey() => [
                'invoice_id' => $order->stripe_invoice_id,
                'cart_hash' => $cartHash,
                'created_at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('myOrders')->with('status', true)->with(
            'message',
            ($order->invoice_send_status === 'sent')
                ? 'Net 30 invoice created and sent to your email.'
                : 'Net 30 invoice created, but email send failed. Our team can resend it from admin.'
        );
    }
}
