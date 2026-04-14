<?php
namespace App\Services;

use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public static function log(string $event, string $entityType, int $entityId, array $oldValues = [], array $newValues = []): void
    {
        try {
            \DB::table('audit_logs')->insert([
                'user_id' => Auth::id(),
                'event' => $event,
                'auditable_type' => $entityType,
                'auditable_id' => $entityId,
                'old_values' => json_encode($oldValues) ?: null,
                'new_values' => json_encode($newValues) ?: null,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditService error: ' . $e->getMessage());
        }
    }

    public static function alert(string $type, string $severity, string $description, array $context = [], ?string $ip = null): void
    {
        SecurityAlert::create([
            'type' => $type,
            'severity' => $severity,
            'description' => $description,
            'context' => $context ?: null,
            'ip_address' => $ip ?? request()->ip(),
            'created_at' => now(),
        ]);
    }
}