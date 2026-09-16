<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QadUser extends Model
{
    protected $table = 'qad_users';

    protected $fillable = [
        'user_id',
        'user_name',
        'active',
        'last_logon',
        'jenis_lisensi',
        'cc_kuota_lisensi',
        'active_date',
        'code',
        'description',
    ];

    protected $casts = [
        'last_logon' => 'date',
        'active_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function activityLogs(): HasMany
    {
        return $this->hasMany(QadActivityLog::class, 'user_id', 'id');
    }
    
    public function sessions(): HasMany
    {
        return $this->hasMany(QadUserSession::class, 'user_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('active', 'yes');
    }

    public function scopeInactive($query)
    {
        return $query->where('active', 'no');
    }

    public function scopeWithRecentActivity($query, $days = 30)
    {
        return $query->whereHas('activityLogs', function ($q) use ($days) {
            $q->whereDate(
                'activity_date',
                '>=',
                now()->subDays($days)->toDateString()
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getStatusLabel(): string
    {
        return $this->active === 'yes'
            ? 'Active'
            : 'Inactive';
    }

    public function getActivityCount(): int
    {
        return $this->activityLogs()->count();
    }

    public function getUniqueModulesCount(): int
    {
        return $this->activityLogs()
            ->join(
                'qad_programs',
                'qad_activity_logs.program_id',
                '=',
                'qad_programs.id'
            )
            ->join(
                'qad_modules',
                'qad_programs.module_id',
                '=',
                'qad_modules.id'
            )
            ->distinct()
            ->count('qad_modules.id');
    }

    public function getUniqueProgramsCount(): int
    {
        return $this->activityLogs()
            ->distinct()
            ->count('program_id');
    }

    public function getLastActivityDate()
    {
        return $this->activityLogs()
            ->orderByDesc('activity_date')
            ->value('activity_date');
    }
}