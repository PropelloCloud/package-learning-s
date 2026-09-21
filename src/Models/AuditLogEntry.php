<?php

namespace Propello\PackageLearningS\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLogEntry extends Model
{
    const UPDATED_AT = null;

    protected $table = 'multi_audit_log_entries';

    protected $fillable = [
        'group_name',
        'group_id',
        'event',
        'old_values',
        'new_values',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }
}
