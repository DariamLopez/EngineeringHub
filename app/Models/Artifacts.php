<?php

namespace App\Models;

use App\ArtifactTypeEnum;
use App\Enums\ArtifactTypeEnum as EnumsArtifactTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailables\Content;

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
        if ($this->type === EnumsArtifactTypeEnum::DOMAIN_BREAKDOWN->value) {
            $content = $this->content_json ?? [];

            // Support both: [1,2] and ['domains' => [1,2]]
            if (isset($content['domains']) && is_array($content['domains'])) {
                $ids = $content['domains'];
            } elseif (is_array($content)) {
                $ids = $content;
            } else {
                $ids = [];
            }

            // Normalize nested arrays if passed as [ [1,2] ]
            if (count($ids) === 1 && is_array($ids[0] ?? null)) {
                $ids = $ids[0];
            }

            $ids = array_values(array_filter($ids, function ($id) {
                return !is_array($id) && $id !== null;
            }));

            if (empty($ids)) {
                return collect();
            }

            $domains = Domain::whereIn('id', $ids)->with('owner')->get()->sortBy(function ($d) use ($ids) {
                return array_search($d->id, $ids);
            })->values();
            $content['domains'] = $domains;
            return collect($content);
        }

        else if ($this->type === EnumsArtifactTypeEnum::BIG_PICTURE->value) {
            $content = $this->content_json ?? [];
            $ids = $content['impacted_domains'] ?? [];
            if (!empty($ids)) {
                $domains = Domain::whereIn('id', $ids)->get()->sortBy(function ($d) use ($ids) {
                    return array_search($d->id, $ids);
                })->values();
                $content['impacted_domains'] = $domains;
            }
            return collect($content);
        }

        else if ($this->type === EnumsArtifactTypeEnum::MODULE_MATRIX->value){
            $content = $this->content_json ?? [];
            $ids = $content['modules_overview'] ?? [];
            if (!empty($ids)) {
                $modules = Modules::whereIn('id', $ids)->with('domain')->get()->sortBy(function ($m) use ($ids) {
                    return array_search($m->id, $ids);
                })->values();
                $content['modules_overview'] = $modules;
            }
            return collect($content);
        }

        else if($this->type === EnumsArtifactTypeEnum::PHASE_SCOPE->value){
            $content = $this->content_json ?? [];
            $ids = $content['included_modules'] ?? [];
            if (!empty($ids)) {
                $modules = Modules::whereIn('id', $ids)->with('domain')->get()->sortBy(function ($m) use ($ids) {
                    return array_search($m->id, $ids);
                })->values();
                $content['included_modules'] = $modules;
            }
            return collect($content);
        }

        return collect($this->content_json);
    }
}
