<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function record(
        string $action,
        ?Model $subject = null,
        array $before = [],
        array $after = [],
        array $metadata = []
    ): AuditLog {
        $request = request();

        return AuditLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'before' => $before ?: null,
            'after' => $after ?: null,
            'metadata' => $metadata ?: null,
        ]);
    }
}
