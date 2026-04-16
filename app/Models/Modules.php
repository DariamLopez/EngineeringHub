<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    /** @use HasFactory<\Database\Factories\ModulesFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'objective',
        'inputs',
        'outputs',
        'data_structure',
        'logic_rules',
        'responsibility',
        'failure_scenarios',
        'audit_trail_requirements',
        'dependencies',
        'version_note',
        'domain_id',
        'project_id',
        'priority',
        'phase'
    ];

    protected $casts = [
        'inputs' => 'array',
        'outputs' => 'array',
        'data_structure' => 'array',
    ];

    private static int $dependencyLoadingDepth = 0;

    public function getDependenciesAttribute(mixed $value): array
    {
        $ids = is_array($value) ? $value : (json_decode($value, true) ?? []);

        if (empty($ids) || self::$dependencyLoadingDepth > 0) {
            return $ids;
        }

        self::$dependencyLoadingDepth++;
        try {
            $result = static::whereIn('id', $ids)->get()->map(fn($m) => $m->toArray())->all();
        } finally {
            self::$dependencyLoadingDepth--;
        }

        return $result;
    }

    public function setDependenciesAttribute(mixed $value): void
    {
        $this->attributes['dependencies'] = is_array($value) ? json_encode($value) : $value;
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
}
