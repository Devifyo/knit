<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;background:#f4f4f5;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#18181b;">
    <div style="max-width:480px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #e4e4e7;border-radius:14px;padding:28px;">
            <h1 style="margin:0 0 8px;font-size:18px;font-weight:600;">Thanks{{ $name ? ', '.$name : '' }} — we got your message</h1>
            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#52525b;">
                Your enquiry to <strong>{{ $workspace }}</strong> has been received. A member of our team will get back to you shortly.
            </p>
            <p style="margin:0;font-size:14px;line-height:1.6;color:#52525b;">— The {{ $workspace }} team</p>
        </div>
        <p style="margin:16px 0 0;text-align:center;font-size:12px;color:#a1a1aa;">Powered by Knit</p>
    </div>
</body>
</html>
