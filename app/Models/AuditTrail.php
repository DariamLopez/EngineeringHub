<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    /** @use HasFactory<\Database\Factories\AuditTrailFactory> */
    use HasFactory;

    protected $fillable = [
        'actor_user_id',
        'entity_type',
        'entity_id',
        'action',
        'before_json',
        'after_json'
    ];
    protected $casts = [
        'before_json' => 'array',
        'after_json' => 'array',
    ];
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
    /**
     * Registra una acción en la tabla audit_trails
     * @param int|null $actorUserId
     * @param string $entityType
     * @param int $entityId
     * @param string $action
     * @param array|null $beforeJson
     * @param array|null $afterJson
     * @return AuditTrail
     */
    public static function logAction(?int $actorUserId, string $entityType, int $entityId, string $action, ?array $beforeJson = null, ?array $afterJson = null): AuditTrail
    {
        return self::create([
            'actor_user_id' => $actorUserId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'before_json' => $beforeJson ? json_encode($beforeJson) : null,
            'after_json' => $afterJson ? json_encode($afterJson) : null,
        ]);
    }
}
