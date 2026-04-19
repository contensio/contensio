@php
    use Contensio\Services\WhitelabelService;
    $emailLogoUrl = WhitelabelService::isActive() ? WhitelabelService::adminLogoUrl() : null;
    $emailSender  = WhitelabelService::emailSenderName();
    $emailFooter  = WhitelabelService::emailFooterText();

    $headBg = match($decision) {
        'approved'      => '#166534',  // green-800
        'soft_rejected' => '#92400e',  // amber-800
        'hard_rejected' => '#991b1b',  // red-800
        default         => '#111827',
    };

    $badge = match($decision) {
        'approved'      => 'Approved',
        'soft_rejected' => 'Revision Requested',
        'hard_rejected' => 'Not Accepted',
        default         => 'Reviewed',
    };

    $message = match($decision) {
        'approved'      => 'Your ' . $typeName . ' has been reviewed and approved.',
        'soft_rejected' => 'Your ' . $typeName . ' needs some changes before it can be published. Please review the notes below and resubmit when ready.',
        'hard_rejected' => 'Your ' . $typeName . ' has been reviewed and will not be published.',
        default         => 'Your ' . $typeName . ' has been reviewed.',
    };
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Content review update</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 24px; color: #111827; }
        .wrap { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .head { background: {{ $headBg }}; padding: 24px 32px; }
        .head img { display: block; max-height: 36px; margin-bottom: 14px; }
        .head h1 { color: #fff; font-size: 18px; font-weight: 700; margin: 0; }
        .head .badge { display: inline-block; margin-top: 8px; background: rgba(255,255,255,.15); color: #fff; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 99px; letter-spacing: .04em; text-transform: uppercase; }
        .body { padding: 32px; }
        .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 4px; }
        .value { font-size: 15px; color: #111827; margin-bottom: 20px; }
        .message { font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 24px; }
        .notes-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; }
        .notes-box .notes-label { font-size: 12px; font-weight: 700; color: #92400e; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .04em; }
        .notes-box p { margin: 0; font-size: 14px; color: #78350f; line-height: 1.6; }
        .btn { display: inline-block; background: #111827; color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 24px; border-radius: 8px; }
        .footer { padding: 16px 32px; border-top: 1px solid #f3f4f6; font-size: 12px; color: #9ca3af; line-height: 1.5; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        @if($emailLogoUrl)
        <img src="{{ $emailLogoUrl }}" alt="{{ $emailSender }}">
        @endif
        <h1>{{ $title }}</h1>
        <span class="badge">{{ $badge }}</span>
    </div>
    <div class="body">
        <p class="message">{{ $message }}</p>

        @if($notes)
        <div class="notes-box">
            <div class="notes-label">Reviewer notes</div>
            <p>{!! nl2br(e($notes)) !!}</p>
        </div>
        @endif

        @if($decision === 'soft_rejected' && $editUrl)
        <a href="{{ $editUrl }}" class="btn">Edit &amp; Resubmit</a>
        @endif
    </div>
    <div class="footer">
        @if($emailFooter)
            {!! nl2br(e($emailFooter)) !!}
        @else
            Sent by {{ $emailSender }}.
        @endif
    </div>
</div>
</body>
</html>
