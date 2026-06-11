<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Project extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'description', 'priority'];

    /**
     * Définir quelles données doivent être enregistrées dans l'historique
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
