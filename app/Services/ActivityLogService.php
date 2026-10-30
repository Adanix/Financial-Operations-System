<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public static function log(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'description' => $description,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
