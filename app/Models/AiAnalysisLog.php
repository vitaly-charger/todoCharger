<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAnalysisLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_message_id', 'task_id', 'provider', 'model', 'prompt_hash',
        'response', 'is_task', 'confidence', 'tokens_used', 'error_message',
    ];

    protected $casts = [
        'response' => 'array',
        'is_task' => 'boolean',
        'confidence' => 'float',
    ];
}
