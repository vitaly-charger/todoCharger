<?php

namespace App\Services\Ai;

class AiAnalysisResult
{
    public function __construct(
        public bool $isTask,
        public ?string $title = null,
        public ?string $description = null,
        public string $priority = 'normal',
        public ?string $dueDate = null,
        public ?string $summary = null,
        public ?string $reasoningShort = null,
        public ?string $followUpSuggestion = null,
        public ?float $confidence = null,
        public ?array $raw = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isTask: (bool) ($data['is_task'] ?? false),
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            priority: in_array($data['priority'] ?? 'normal', ['low', 'normal', 'high', 'urgent'], true)
                ? $data['priority'] : 'normal',
            dueDate: $data['due_date'] ?? null,
            summary: $data['summary'] ?? null,
            reasoningShort: $data['reasoning_short'] ?? null,
            followUpSuggestion: $data['follow_up_suggestion'] ?? null,
            confidence: isset($data['confidence']) ? (float) $data['confidence'] : null,
            raw: $data,
        );
    }
}
