<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourceMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_account_id', 'source_type', 'external_id', 'external_parent_id',
        'sender_name', 'sender_identifier', 'title', 'body_text', 'body_html',
        'metadata', 'source_url', 'received_at', 'processed_at',
        'ai_processed', 'created_task_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'ai_processed' => 'boolean',
        ];
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(SourceAccount::class);
    }

    public function createdTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'created_task_id');
    }
}
