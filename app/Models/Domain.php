<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    /** @use HasFactory<\Database\Factories\DomainFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'objective',
        'owner_user_id',
        'project_id'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }
    public function modules()
    {
        return $this->hasMany(Modules::class, 'domain_id');
    }
}
