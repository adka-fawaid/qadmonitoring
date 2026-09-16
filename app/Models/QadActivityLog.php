<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QadActivityLog extends Model
{
    protected $table = 'qad_activity_logs';

    protected $fillable = [
        'user_id',
        'program_id',
        'cost_center',
        'end_user',
        'transaction_code',
        'activity_date',
        'activity_time',
        'effective_date',
        'last_activity',
        'promise',
        'activity_type',
        'status',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'effective_date' => 'date',
        'activity_time' => 'float',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(QadUser::class, 'user_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(QadProgram::class, 'program_id');
    }

    public function module()
    {
        return $this->belongsTo(QadModule::class, 'module_id')
            ->through('program', 'module');
    }

    // Scopes
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->whereHas('user', function ($q) use ($userId) {
            $q->where('id', $userId);
        });
    }

    public function scopeByModule($query, $moduleId)
    {
        return $query->whereHas('program', function ($q) use ($moduleId) {
            $q->where('module_id', $moduleId);
        });
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->whereDate('activity_date', '>=', now()->subDays($days)->toDateString());
    }
}
