<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QadModuleImplementation extends Model
{
    protected $table = 'qad_module_implementations';

    protected $fillable = [
        'module_id',
        'sub_module',
        'implementation_status',
        'implementation_possible',
        'investment',
        'notes',
    ];

    protected $casts = [
        'implementation_possible' => 'boolean',
    ];

    // Relationships
    public function module(): BelongsTo
    {
        return $this->belongsTo(QadModule::class, 'module_id');
    }
}
