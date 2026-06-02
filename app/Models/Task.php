<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'project_id','priority','due_date'];

    /**
     * Le projet auquel appartient la tâche.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function users()
    {
    return $this->belongsToMany(User::class, 'task_user');
    }
}