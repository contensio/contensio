<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Mail;

use Contensio\Models\Comment;
use Contensio\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content as MailContent;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Comment $comment,
        public readonly Content $content,
        public readonly string  $moderateUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New comment awaiting moderation',
        );
    }

    public function content(): MailContent
    {
        return new MailContent(
            view: 'contensio::emails.new-comment',
        );
    }
}
