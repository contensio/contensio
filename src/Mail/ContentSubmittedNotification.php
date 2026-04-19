<?php

/**
 * Contensio - The open content platform for Laravel.
 * Notifies reviewers when an author submits content for review.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Mail;

use Contensio\Models\Content;
use Contensio\Http\Controllers\Admin\ReviewController;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content as MailContent;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContentSubmittedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Content $content,
        public readonly mixed   $submittedBy, // App\Models\User
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->content->translations->first()?->title ?? 'Untitled';

        return new Envelope(
            subject: "Content pending review: \"{$title}\"",
        );
    }

    public function content(): MailContent
    {
        return new MailContent(
            view: 'contensio::emails.content-submitted',
            with: [
                'reviewUrl' => route('contensio.account.reviews.index'),
                'editUrl'   => ReviewController::editUrl($this->content),
                'title'     => $this->content->translations->first()?->title ?? 'Untitled',
                'typeName'  => $this->content->contentType?->name ?? 'content',
                'authorName'=> $this->submittedBy?->name ?? 'Unknown',
            ],
        );
    }
}
