<?php

namespace App\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditsActivity
{
    public static function bootAuditsActivity(): void
    {
        static::created(function ($model) {
            static::logAudit('created', $model, [], $model->getAttributes());
        });

        static::updated(function ($model) {
            static::logAudit('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            static::logAudit('deleted', $model, $model->getAttributes(), []);
        });
    }

    protected static function logAudit(string $action, $model, array $old, array $new): void
    {
        if (! property_exists(static::class, 'auditExclude') || ! in_array($action, static::$auditExclude)) {
            // Skip "touch"-style updates with no meaningful changes on audit fields only.
            if ($action === 'updated') {
                unset($new['updated_at']);
                if (empty($new)) {
                    return;
                }
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'action' => $action,
                'old_values' => $old,
                'new_values' => $new,
            ]);
        }
    }
}