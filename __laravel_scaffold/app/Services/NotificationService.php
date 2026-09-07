<?php

namespace App\Services;

use App\Jobs\CreateInAppNotification;
use App\Models\Member;

class NotificationService
{
    /** Queue only after the surrounding database transaction commits. */
    public function queue(Member $recipient, string $type, array $data = []): void
    {
        CreateInAppNotification::dispatch($recipient->id, $type, $data)->afterCommit();
    }
}
