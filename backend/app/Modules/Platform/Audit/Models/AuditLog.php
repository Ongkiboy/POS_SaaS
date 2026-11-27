<?php

namespace App\Modules\Platform\Audit\Models;

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Append-only (BR-020, ADR-007): tanpa updated_at, tanpa update/delete.
 */
class AuditLog extends Model
{
    use BelongsToBusiness;

    public const UPDATED_AT = null;

    protected $fillable = [
        'business_id', 'outlet_id', 'user_id', 'action', 'resource_type', 'resource_id',
        'before_data', 'after_data', 'reason', 'ip_address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'before_data' => 'array',
            'after_data' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('audit_logs bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('audit_logs bersifat append-only.'));
    }
}
