<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Les utilisateurs qui appartiennent au projet.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Un projet possède plusieurs tâches.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}