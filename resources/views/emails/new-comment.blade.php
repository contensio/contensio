<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New comment awaiting moderation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; padding: 24px; color: #111827; }
        .wrap { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .head { background: #111827; padding: 24px 32px; }
        .head h1 { color: #fff; font-size: 18px; font-weight: 700; margin: 0; }
        .body { padding: 32px; }
        .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 4px; }
        .value { font-size: 15px; color: #111827; margin-bottom: 20px; }
        .comment-body { background: #f9fafb; border-left: 3px solid #e5e7eb; padding: 12px 16px; border-radius: 4px; font-size: 15px; line-height: 1.6; color: #374151; margin-bottom: 24px; }
        .btn { display: inline-block; background: #111827; color: #fff; text-decoration: none; font-size: 14px; font-weight: 600; padding: 12px 24px; border-radius: 8px; }
        .footer { padding: 16px 32px; border-top: 1px solid #f3f4f6; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <h1>New comment awaiting moderation</h1>
    </div>
    <div class="body">
        <div class="label">Post</div>
        <div class="value">{{ $content->translations->first()?->title ?? 'Untitled' }}</div>

        <div class="label">Author</div>
        <div class="value">
            @if($comment->author)
                {{ $comment->author->name }}
            @else
                {{ $comment->author_name }}
                @if($comment->author_email)
                &lt;{{ $comment->author_email }}&gt;
                @endif
            @endif
        </div>

        <div class="label">Comment</div>
        <div class="comment-body">{{ $comment->body }}</div>

        <a href="{{ $moderateUrl }}" class="btn">Review &amp; Moderate</a>
    </div>
    <div class="footer">
        Sent by {{ config('app.name') }}. You received this because you are an administrator.
    </div>
</div>
</body>
</html>
