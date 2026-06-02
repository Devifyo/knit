<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #18181b; font-size: 11px; margin: 32px; }
        h1 { font-size: 16px; color: {{ $brandColor }}; margin: 0 0 2px; }
        .muted { color: #71717a; font-size: 10px; }
        .summary { margin: 14px 0; }
        .summary span { display: inline-block; margin-right: 18px; }
        .summary b { color: {{ $brandColor }}; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { text-align: left; font-size: 9px; text-transform: uppercase; color: #71717a; border-bottom: 1px solid #e4e4e7; padding: 6px 5px; }
        td { padding: 6px 5px; border-bottom: 1px solid #f0f0f1; }
    </style>
</head>
<body>
    <h1>{{ $tenantName }}</h1>
    <div class="muted">{{ $report['title'] }} · generated {{ $generatedAt }}</div>

    <div class="summary">
        @foreach($report['summary'] as $label => $value)
            <span>{{ $label }}: <b>{{ $value }}</b></span>
        @endforeach
    </div>

    <table>
        <thead><tr>@foreach($report['headers'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach($report['rows'] as $row)
                <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
