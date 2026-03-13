<?php

namespace App\Models;

use App\ArtifactTypeEnum;
use App\Enums\ArtifactTypeEnum as EnumsArtifactTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artifacts extends Model
{
    /** @use HasFactory<\Database\Factories\ArtifactsFactory> */
    use HasFactory;
    protected $fillable = [
        'type',
        'content_json',
        'status',
        'owner_user_id',
        'project_id',
        'domain_id'
    ];
    protected $casts = [
        'content_json' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
    public function getJsonAttribute()
    {
        if($this->type !== EnumsArtifactTypeEnum::DOMAIN_BREAKDOWN->value) {
            return collect($this->content_json);
        }
        $ids = $this->content_json ?? [];
        if (empty($ids)) {
            return collect();
        }
        return Domain::whereIn('id', $ids)->get()->sortBy(function ($d) use ($ids) {
            return array_search($d->id, $ids);
        })->values();
    }
}
