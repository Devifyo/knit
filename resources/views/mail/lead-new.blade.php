<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#f4f4f5;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#18181b;">
    <div style="max-width:480px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #e4e4e7;border-radius:14px;padding:28px;">
            <h1 style="margin:0 0 4px;font-size:18px;font-weight:600;">New lead in {{ $workspace }}</h1>
            <p style="margin:0 0 18px;font-size:13px;color:#a1a1aa;">A new lead just came in.</p>
            <table style="width:100%;font-size:14px;color:#3f3f46;border-collapse:collapse;">
                <tr><td style="padding:6px 0;color:#71717a;width:90px;">Name</td><td style="padding:6px 0;font-weight:600;color:#18181b;">{{ $lead->name }}</td></tr>
                @if($lead->email)<tr><td style="padding:6px 0;color:#71717a;">Email</td><td style="padding:6px 0;">{{ $lead->email }}</td></tr>@endif
                @if($lead->phone)<tr><td style="padding:6px 0;color:#71717a;">Phone</td><td style="padding:6px 0;">{{ $lead->phone }}</td></tr>@endif
                <tr><td style="padding:6px 0;color:#71717a;">Source</td><td style="padding:6px 0;">{{ $lead->source }}</td></tr>
                <tr><td style="padding:6px 0;color:#71717a;">Score</td><td style="padding:6px 0;font-weight:600;">{{ $lead->score }}/100</td></tr>
            </table>
            <a href="{{ $url }}" style="display:inline-block;margin-top:18px;background:#4f46e5;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:10px 18px;border-radius:8px;">View lead</a>
        </div>
        <p style="margin:16px 0 0;text-align:center;font-size:12px;color:#a1a1aa;">You receive these because you have lead notifications enabled. Powered by Knit</p>
    </div>
</body>
</html>
