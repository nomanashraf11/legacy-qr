<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ substr($order->uuid, 0, 8) }} - Living Legacy</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; font-size: 14px; line-height: 1.5; color: #333; max-width: 800px; margin: 0 auto; padding: 24px; }
        .invoice-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; padding-bottom: 20px; border-bottom: 2px solid #333; }
        .invoice-header h1 { margin: 0; font-size: 24px; }
        .invoice-meta { text-align: right; }
        .invoice-meta p { margin: 4px 0; }
        .section { margin-bottom: 24px; }
        .section h3 { margin: 0 0 12px 0; font-size: 12px; text-transform: uppercase; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 16px; background: #f8f9fa; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center; }
        @media print { body { padding: 16px; } .no-print { display: none !important; } }
        .print-btn { position: fixed; top: 16px; right: 16px; padding: 10px 20px; background: #0d6efd; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .print-btn:hover { background: #0b5ed7; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        Print Invoice
    </button>

    <div class="invoice-header">
        <div>
            <h1>Living Legacy</h1>
            <p>Invoice</p>
        </div>
        <div class="invoice-meta">
            <p><strong>Invoice #</strong> {{ substr($order->uuid, 0, 8) }}</p>
            <p><strong>Date</strong> {{ $order->created_at ? $order->created_at->format('F j, Y') : '—' }}</p>
            <p><strong>Status</strong> {{ $order->status == 2 ? 'Delivered' : ($order->status == 1 ? 'In Progress' : 'Pending') }}</p>
        </div>
    </div>

    <div class="section">
        <h3>Bill To</h3>
        @php $resel = $order->reSeller; @endphp
        @if($resel)
        <p><strong>{{ $resel->user->name ?? '—' }}</strong></p>
        <p>{{ $resel->shipping_address ?? '—' }}</p>
        <p>{{ $resel->phone ?? '—' }}</p>
        @endif
    </div>

    <div class="section">
        <h3>Order Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if($order->orderItems && $order->orderItems->isNotEmpty())
                    @foreach($order->orderItems as $oi)
                        @php $product = $oi->product; @endphp
                        <tr>
                            <td>{{ $product ? $product->name : '—' }} <span class="text-muted">({{ $product ? $product->sku : '—' }})</span></td>
                            <td class="text-right">{{ $oi->quantity }}</td>
                            <td class="text-right">${{ number_format($oi->price, 2) }}</td>
                            <td class="text-right">${{ number_format($oi->price * $oi->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>QR Codes</td>
                        <td class="text-right">{{ $order->qr_codes }}</td>
                        <td class="text-right">—</td>
                        <td class="text-right">${{ number_format($order->amount, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <table>
            <tr class="total-row">
                <td colspan="3" class="text-right">Total Paid</td>
                <td class="text-right">${{ number_format($order->amount, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($order->shipping_carrier || $order->tracking_id || $order->tracking_details)
    <div class="section">
        <h3>Shipping</h3>
        @if($order->shipping_carrier)
        <p><strong>Carrier:</strong> {{ $order->shipping_carrier }}</p>
        @endif
        @if($order->tracking_id)
        <p><strong>Tracking ID:</strong> {{ $order->tracking_id }}</p>
        @endif
        @if($order->tracking_details)
        <p><strong>Notes:</strong> {{ $order->tracking_details }}</p>
        @endif
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your order. Living Legacy</p>
    </div>
</body>
</html>
