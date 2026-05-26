<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'project_id'];

    /**
     * Le projet auquel appartient la tâche.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}