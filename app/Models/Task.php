<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    public const STATUSES = ['inbox', 'todo', 'in_progress', 'waiting', 'done', 'ignored'];
    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];
    public const SOURCE_TYPES = ['manual', 'gmail', 'slack', 'telegram', 'monday', 'wrike'];
    public const SYNC_STATUSES = ['not_synced', 'synced', 'sync_failed', 'imported_only'];

    protected $fillable = [
        'user_id', 'title', 'description', 'status', 'priority', 'due_date',
        'source_type', 'source_account_id', 'source_account_label',
        'source_external_id', 'source_parent_external_id', 'source_url',
        'context_text', 'ai_summary', 'ai_reasoning_short', 'follow_up_suggestion',
        'ai_confidence', 'external_sync_status', 'external_last_synced_at',
        'completed_at', 'needs_review',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'external_last_synced_at' => 'datetime',
            'needs_review' => 'boolean',
            'ai_confidence' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(SourceAccount::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function events(): HasMany
    {
        return $this->hasMany(TaskEvent::class)->latest();
    }

    public function externalMappings(): HasMany
    {
        return $this->hasMany(ExternalTaskMapping::class);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['done', 'ignored'], true);
    }
}
