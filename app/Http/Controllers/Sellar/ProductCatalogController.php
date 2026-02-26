<?php

namespace App\Http\Controllers\Sellar;

use App\Http\Controllers\Controller;
use App\Mail\AdminNewOrderMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    protected function ensureResellerCanPurchase(): ?RedirectResponse
    {
        $admin = User::role('admin')->first()?->admin;
        if ($admin && $admin->purchase == 1) {
            return redirect()->route('sellar.dashboard')->with('status', false)->with('message', 'Contact your admin to turn purchase ON');
        }
        $reseller = Auth::user()->reSeller;
        if (!$reseller || !$reseller->shipping_address || !$reseller->phone) {
            return redirect()->route('settings')->with('status', false)->with('message', 'Complete Your Profile First');
        }
        return null;
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->ensureResellerCanPurchase()) {
            return $redirect;
        }
        $products = Product::where('stock', '>', 0)->get();
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
        if ($product->stock < $request->quantity) {
            return response()->json(['status' => false, 'message' => 'Not enough stock'], 422);
        }

        $cart = session('reseller_cart', []);
        $key = array_search($request->product_id, array_column($cart, 'product_id'));

        if ($key !== false) {
            $cart[$key]['quantity'] += $request->quantity;
        } else {
            $cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
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
        foreach ($cart as $i => $item) {
            if ($item['product_id'] == $productId) {
                $product = Product::find($productId);
                if ($product && $product->stock >= $quantity) {
                    $cart[$i]['quantity'] = $quantity;
                }
                break;
            }
        }
        session(['reseller_cart' => $cart]);
        return response()->json(['status' => true, 'cart_count' => collect($cart)->sum('quantity')]);
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

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->create([
            'success_url' => route('reseller.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('reseller.cart'),
            'customer_email' => Auth::user()->email,
            'payment_method_types' => ['card', 'link'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'allow_promotion_codes' => true,
        ]);

        session(['reseller_checkout_cart' => $cart]);
        return redirect($session->url);
    }

    public function checkoutSuccess(Request $request): RedirectResponse
    {
        $cart = session('reseller_checkout_cart', []);
        if (empty($cart)) {
            return redirect()->route('reseller.products')->with('status', false)->with('message', 'Session expired.');
        }

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);

        if ($session->payment_status !== 'paid') {
            return redirect()->route('reseller.cart')->with('status', false)->with('message', 'Payment was not completed.');
        }

        $totalQty = collect($cart)->sum('quantity');
        $totalAmount = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);

        $order = Order::create([
            'uuid' => Str::uuid(),
            'qr_codes' => $totalQty,
            'amount' => $totalAmount,
            'status' => 0,
            're_seller_id' => Auth::user()->reSeller->id,
            'tracking_details' => 'Order is pending',
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

        $adminEmail = config('mail.admin_notification_email');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new AdminNewOrderMail([
                    'orderNumber' => substr($order->uuid, 0, 8),
                    'resellerName' => Auth::user()->name,
                    'items' => $totalQty . ' item(s)',
                    'amount' => number_format($totalAmount, 2),
                    'adminUrl' => url('/orders'),
                ]));
            } catch (\Throwable $e) {
                \Log::warning('Admin new order notification failed: ' . $e->getMessage());
            }
        }

        session()->forget(['reseller_cart', 'reseller_checkout_cart']);
        return redirect()->route('myOrders')->with('status', true)->with('message', 'Order placed successfully!');
    }
}
