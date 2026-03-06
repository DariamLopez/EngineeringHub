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
        'responsability',
        'failure_scenarios',
        'audit_trail_requirements',
        'dependencies',
        'version_note',
        'domain_id',
        'project_id'
    ];

    protected $casts = [
        'inputs' => 'array',
        'outputs' => 'array',
        'data_structure' => 'array',
        'dependencies' => 'array',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
}
