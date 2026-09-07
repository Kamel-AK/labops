<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Member;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function record(Model $entity, string $action, ?Member $actor, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): ActivityLog
    {
        return ActivityLog::create([
            'entity_type' => $entity::class,
            'entity_id' => $entity->getKey(),
            'action' => $action,
            'actor_type' => $actor ? 'member' : 'system',
            'performed_by' => $actor?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => now(),
        ]);
    }

    public function recordSystem(Model $entity, string $action, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): ActivityLog
    {
        return $this->record($entity, $action, null, $oldValues, $newValues, $description);
    }
}
