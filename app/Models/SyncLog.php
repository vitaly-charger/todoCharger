<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_account_id', 'source_type', 'status', 'started_at', 'finished_at',
        'imported_count', 'created_task_count', 'error_message', 'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(SourceAccount::class);
    }
}
