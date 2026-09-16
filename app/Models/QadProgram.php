<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QadProgram extends Model
{
    protected $table = 'qad_programs';

    protected $fillable = [
        'module_id',
        'program_code',
        'kode',
        'address',
        'name',
        'program_type',
    ];

    // Relationships
    public function module(): BelongsTo
    {
        return $this->belongsTo(QadModule::class, 'module_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(QadActivityLog::class, 'program_id');
    }

    // Scopes
    public function scopeByModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

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

    public function getLastActivityDate()
    {
        return $this->activityLogs()
            ->orderByDesc('activity_date')
            ->value('activity_date');
    }
}
