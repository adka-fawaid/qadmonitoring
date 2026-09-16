<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QadModule extends Model
{
    protected $table = 'qad_modules';

    protected $fillable = [
        'name',
        'normalized_name',
    ];

    // Relationships
    public function programs(): HasMany
    {
        return $this->hasMany(QadProgram::class, 'module_id');
    }

    public function implementations(): HasMany
    {
        return $this->hasMany(QadModuleImplementation::class, 'module_id', 'id');
    }

    public function activityLogs()
    {
        return $this->hasManyThrough(
            QadActivityLog::class,
            QadProgram::class,
            'module_id',
            'program_id',
            'id',
            'id'
        );
    }

    // Scopes
    public function scopeWithActivity($query, $days = 30)
    {
        return $query->whereHas('activityLogs', function ($q) use ($days) {
            $q->whereDate('activity_date', '>=', now()->subDays($days)->toDateString());
        });
    }

    // Accessors
    public function getActivityCount($days = 30): int
    {
        return $this->activityLogs()
            ->whereDate('activity_date', '>=', now()->subDays($days)->toDateString())
            ->count();
    }

    public function getUniqueUsersCount($days = 30): int
    {
        return $this->activityLogs()
            ->whereDate('activity_date', '>=', now()->subDays($days)->toDateString())
            ->distinct('user_id')
            ->count('user_id');
    }

    public function getUniqueProgramsCount($days = 30): int
    {
        return $this->activityLogs()
            ->whereDate('activity_date', '>=', now()->subDays($days)->toDateString())
            ->distinct('program_id')
            ->count('program_id');
    }
}
