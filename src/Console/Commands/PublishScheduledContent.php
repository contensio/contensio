<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Console\Commands;

use Contensio\Models\Content;
use Illuminate\Console\Command;

class PublishScheduledContent extends Command
{
    protected $signature   = 'contensio:publish-scheduled';
    protected $description = 'Publish any content whose scheduled publish time has passed.';

    public function handle(): int
    {
        $count = Content::where('status', 'scheduled')
            ->where('published_at', '<=', now())
            ->update(['status' => 'published']);

        if ($count > 0) {
            $this->info("Published {$count} scheduled item(s).");
        }

        return Command::SUCCESS;
    }
}
