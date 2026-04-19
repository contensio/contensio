<?php

/**
 * Contensio - The open content platform for Laravel.
 * Notifies the author when their content has been reviewed (approved or rejected).
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Mail;

use Contensio\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content as MailContent;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContentReviewedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Content $content,
        public readonly string  $decision,  // 'approved' | 'soft_rejected' | 'hard_rejected'
        public readonly ?string $notes,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->content->translations->first()?->title ?? 'Untitled';

        $subject = match ($this->decision) {
            'approved'      => "Your content was approved: \"{$title}\"",
            'soft_rejected' => "Revision requested for: \"{$title}\"",
            'hard_rejected' => "Your content was not accepted: \"{$title}\"",
            default         => "Content review update: \"{$title}\"",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): MailContent
    {
        return new MailContent(
            view: 'contensio::emails.content-reviewed',
            with: [
                'decision'  => $this->decision,
                'notes'     => $this->notes,
                'title'     => $this->content->translations->first()?->title ?? 'Untitled',
                'typeName'  => $this->content->contentType?->name ?? 'content',
                'editUrl'   => $this->decision === 'soft_rejected'
                    ? \Contensio\Http\Controllers\Admin\ReviewController::editUrl($this->content)
                    : null,
            ],
        );
    }
}
