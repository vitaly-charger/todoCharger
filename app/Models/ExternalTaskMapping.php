<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalTaskMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'provider', 'source_account_id', 'external_id',
        'external_parent_id', 'external_url', 'sync_status',
        'last_synced_at', 'metadata',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function sourceAccount(): BelongsTo { return $this->belongsTo(SourceAccount::class); }
}
