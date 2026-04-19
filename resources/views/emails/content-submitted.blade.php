@php
    use Contensio\Services\WhitelabelService;
    $emailLogoUrl = WhitelabelService::isActive() ? WhitelabelService::adminLogoUrl() : null;
    $emailSender  = WhitelabelService::emailSenderName();
    $emailFooter  = WhitelabelService::emailFooterText();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Content pending review</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 24px; color: #111827; }
        .wrap { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .head { background: #111827; padding: 24px 32px; }
        .head img { display: block; max-height: 36px; margin-bottom: 14px; }
        .head h1 { color: #fff; font-size: 18px; font-weight: 700; margin: 0; }
        .body { padding: 32px; }
        .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 4px; }
        .value { font-size: 15px; color: #111827; margin-bottom: 20px; }
        .actions { display: flex; gap: 12px; margin-top: 8px; }
        .btn { display: inline-block; background: #111827; color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 24px; border-radius: 8px; }
        .btn-outline { background: transparent; color: #111827; border: 1.5px solid #e5e7eb; }
        .footer { padding: 16px 32px; border-top: 1px solid #f3f4f6; font-size: 12px; color: #9ca3af; line-height: 1.5; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        @if($emailLogoUrl)
        <img src="{{ $emailLogoUrl }}" alt="{{ $emailSender }}">
        @endif
        <h1>New {{ $typeName }} awaiting review</h1>
    </div>
    <div class="body">
        <div class="label">Title</div>
        <div class="value">{{ $title }}</div>

        <div class="label">Type</div>
        <div class="value" style="text-transform: capitalize;">{{ $typeName }}</div>

        <div class="label">Submitted by</div>
        <div class="value">{{ $authorName }}</div>

        <div class="actions">
            <a href="{{ $reviewUrl }}" class="btn">View Review Queue</a>
            <a href="{{ $editUrl }}" class="btn btn-outline">Open Content</a>
        </div>
    </div>
    <div class="footer">
        @if($emailFooter)
            {!! nl2br(e($emailFooter)) !!}
        @else
            Sent by {{ $emailSender }}. You received this because you have content review permissions.
        @endif
    </div>
</div>
</body>
</html>
