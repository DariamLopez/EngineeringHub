<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectsFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'client_name',
        'description',
        'status',
        'created_by',
        'is_archived'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')->withTimestamps();
    }
    public function domains()
    {
        return $this->hasMany(Domain::class, 'project_id');
    }
    public function modules()
    {
        return $this->hasMany(Modules::class, 'project_id');
    }
}
