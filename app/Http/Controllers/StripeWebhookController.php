<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');
        $webhookSecret = (string) config('services.stripe.webhook_secret', '');

        if ($webhookSecret === '' || $signature === '') {
            Log::warning('Stripe webhook missing signature or secret configuration.');
            return response()->json(['received' => false], 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload: ' . $e->getMessage());
            return response()->json(['received' => false], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['received' => false], 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook unexpected verification error: ' . $e->getMessage());
            return response()->json(['received' => false], 500);
        }

        $eventType = (string) ($event->type ?? '');
        $invoice = $event->data->object ?? null;

        try {
            if (in_array($eventType, ['invoice.paid', 'invoice.payment_failed', 'invoice.finalized', 'invoice.sent'], true)) {
                $this->handleInvoiceEvent($eventType, $invoice);
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook processing failed', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['received' => false], 500);
        }

        return response()->json(['received' => true], 200);
    }

    protected function handleInvoiceEvent(string $eventType, ?object $invoice): void
    {
        $invoiceId = (string) ($invoice->id ?? '');
        if ($invoiceId === '') {
            Log::warning('Stripe invoice webhook missing invoice id', ['event_type' => $eventType]);
            return;
        }

        $order = Order::where('stripe_invoice_id', $invoiceId)->first();
        if (!$order) {
            Log::warning('No order matched Stripe invoice webhook', [
                'event_type' => $eventType,
                'invoice_id' => $invoiceId,
            ]);
            return;
        }

        $updates = [
            'stripe_invoice_status' => (string) ($invoice->status ?? $order->stripe_invoice_status),
            'stripe_invoice_number' => (string) ($invoice->number ?? $order->stripe_invoice_number),
            'invoice_due_at' => !empty($invoice->due_date)
                ? Carbon::createFromTimestamp((int) $invoice->due_date)
                : $order->invoice_due_at,
        ];

        if ($eventType === 'invoice.paid') {
            // Idempotent update: repeated paid events should keep same state.
            $updates['stripe_payment_status'] = 'paid';
            $updates['status'] = Order::STATUS_PENDING;
            if (empty($order->tracking_details) || str_contains((string) $order->tracking_details, 'Awaiting payment')) {
                $updates['tracking_details'] = 'Payment received. Awaiting order acceptance.';
            }
        } elseif ($eventType === 'invoice.payment_failed') {
            $updates['stripe_payment_status'] = 'payment_failed';
            if (empty($order->tracking_details) || str_contains((string) $order->tracking_details, 'Awaiting payment')) {
                $updates['tracking_details'] = 'Invoice payment failed. Awaiting payment.';
            }
        } elseif ($eventType === 'invoice.finalized') {
            $updates['stripe_payment_status'] = $order->stripe_payment_status ?: 'unpaid';
        } elseif ($eventType === 'invoice.sent') {
            $updates['stripe_payment_status'] = $order->stripe_payment_status ?: 'unpaid';
        }

        $order->update($updates);

        Log::info('Stripe invoice webhook applied', [
            'event_type' => $eventType,
            'invoice_id' => $invoiceId,
            'order_id' => $order->id,
            'stripe_payment_status' => $order->fresh()->stripe_payment_status,
        ]);
    }
}
