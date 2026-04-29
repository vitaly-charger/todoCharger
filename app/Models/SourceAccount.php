<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceAccount extends Model
{
    use HasFactory;

    public const TYPE_GMAIL = 'gmail';
    public const TYPE_SLACK = 'slack';
    public const TYPE_TELEGRAM = 'telegram';
    public const TYPE_MONDAY = 'monday';
    public const TYPE_WRIKE = 'wrike';

    public const TYPES = [
        self::TYPE_GMAIL,
        self::TYPE_SLACK,
        self::TYPE_TELEGRAM,
        self::TYPE_MONDAY,
        self::TYPE_WRIKE,
    ];

    protected $fillable = [
        'user_id', 'type', 'name', 'identifier', 'credentials', 'settings',
        'enabled', 'last_synced_at', 'last_sync_status',
    ];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'settings' => 'array',
            'enabled' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SourceMessage::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    public function label(): string
    {
        return $this->type . ' - ' . ($this->identifier ?: $this->name);
    }
}
