<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\AuditActionModule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    private const SENSITIVE_KEYS = [
        'national_id',
        'password',
        'token',
        'verification_code',
        'security_hash',
        'guardian_national_id',
    ];

    public function log(
        string $action,
        ?User $user = null,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?Request $request = null,
        ?string $module = null,
        ?string $description = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'module' => $module ?? AuditActionModule::resolve($action),
            'description' => $description,
            'auditable_type' => $auditable ? $auditable->getMorphClass() : null,
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'metadata' => $this->sanitize($metadata),
            'created_at' => now(),
        ]);
    }

    private function sanitize(?array $data): ?array
    {
        if ($data === null) {
            return null;
        }

        $filtered = [];

        foreach ($data as $key => $value) {
            if (in_array((string) $key, self::SENSITIVE_KEYS, true)) {
                continue;
            }

            if (is_array($value)) {
                $filtered[$key] = $this->sanitize($value);
                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }
}
