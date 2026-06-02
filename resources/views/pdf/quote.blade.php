<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #18181b; font-size: 12px; margin: 40px; }
        .head { display: flex; justify-content: space-between; border-bottom: 2px solid {{ $brandColor }}; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { font-size: 22px; font-weight: bold; color: {{ $brandColor }}; }
        .muted { color: #71717a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; color: #71717a; border-bottom: 1px solid #e4e4e7; padding: 8px 6px; }
        td { padding: 8px 6px; border-bottom: 1px solid #f0f0f1; }
        .right { text-align: right; }
        .totals { width: 240px; float: right; margin-top: 16px; }
        .totals td { border: 0; padding: 4px 6px; }
        .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #e4e4e7; }
    </style>
</head>
<body>
    <div class="head">
        <div>
            <div class="brand">{{ $tenantName }}</div>
            <div class="muted">Quotation</div>
        </div>
        <div class="right">
            <h1>{{ $quote->number }}</h1>
            <div class="muted">Status: {{ ucfirst($quote->status) }}</div>
            @if($quote->valid_until)<div class="muted">Valid until {{ $quote->valid_until->toFormattedDateString() }}</div>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Unit price</th>
                <th class="right">Discount</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ $pricing->format($item->unit_price, $quote->currency) }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format((float) $item->discount_pct, 2), '0'), '.') }}%</td>
                    <td class="right">{{ $pricing->format($item->lineTotal(), $quote->currency) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">{{ $totals['subtotal_formatted'] }}</td></tr>
        <tr><td>Tax ({{ rtrim(rtrim(number_format($totals['tax_rate'], 2), '0'), '.') }}%)</td><td class="right">{{ $totals['tax_formatted'] }}</td></tr>
        <tr class="grand"><td>Total</td><td class="right">{{ $totals['total_formatted'] }}</td></tr>
    </table>

    @if($quote->notes)
        <div style="clear: both; margin-top: 40px;" class="muted">{{ $quote->notes }}</div>
    @endif
</body>
</html>
