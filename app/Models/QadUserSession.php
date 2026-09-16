<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QadUserSession extends Model
{
    protected $table = 'qad_user_sessions';

    protected $fillable = [
        'user_id',
        'year',
        'first_login_date',
        'last_logout_date',
        'total_sessions',
        'days_active',
        'avg_sess_per_day',
        'sess_at_peak',
        'avg_hrs_per_wk',
    ];

    protected $casts = [
        'first_login_date' => 'date',
        'last_logout_date' => 'date',
        'avg_sess_per_day' => 'float',
        'avg_hrs_per_wk' => 'float',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(QadUser::class, 'user_id');
    }

    // Scopes
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }
}
