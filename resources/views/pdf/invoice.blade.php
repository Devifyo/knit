<!DOCTYPE html>
<html lang="en">
@php($brand = $tenant?->brand_color ?? '#18181b')
<head>
    <meta charset="utf-8" />
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #18181b; font-size: 12px; margin: 40px; }
        .head { display: flex; justify-content: space-between; border-bottom: 2px solid {{ $brand }}; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { font-size: 22px; font-weight: bold; color: {{ $brand }}; }
        .muted { color: #71717a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; color: #71717a; border-bottom: 1px solid #e4e4e7; padding: 8px 6px; }
        td { padding: 8px 6px; border-bottom: 1px solid #f0f0f1; }
        .right { text-align: right; }
        .totals { width: 260px; float: right; margin-top: 16px; }
        .totals td { border: 0; padding: 4px 6px; }
        .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #e4e4e7; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #f4f4f5; font-size: 10px; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">{{ $tenant?->name ?? 'Knit' }}</div>
            <div class="muted">Invoice</div>
        </div>
        <div class="right">
            <h1>{{ $invoice->number }}</h1>
            <div><span class="badge">{{ ucfirst($invoice->status) }}</span></div>
            @if($invoice->issued_at)<div class="muted">Issued {{ $invoice->issued_at->toFormattedDateString() }}</div>@endif
            @if($invoice->paid_at)<div class="muted">Paid {{ $invoice->paid_at->toFormattedDateString() }}</div>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ $money->format($item->unit_amount_minor, $invoice->currency) }}</td>
                    <td class="right">{{ $money->format($item->lineTotal(), $invoice->currency) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ $money->format($invoice->subtotal_minor, $invoice->currency) }}</td></tr>
        @if($invoice->discount_minor > 0)
            <tr><td>Discount</td><td class="right">−{{ $money->format($invoice->discount_minor, $invoice->currency) }}</td></tr>
        @endif
        <tr><td>Tax</td><td class="right">{{ $money->format($invoice->tax_minor, $invoice->currency) }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right">{{ $money->format($invoice->total_minor, $invoice->currency) }}</td></tr>
    </table>
</body>
</html>
