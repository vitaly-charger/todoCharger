<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClaudeProvider implements AiProvider
{
    public function __construct(
        protected ?string $apiKey,
        protected string $model = 'claude-3-5-sonnet-latest',
    ) {}

    public function name(): string { return 'claude'; }
    public function model(): string { return $this->model; }

    public function analyze(string $systemPrompt, string $userPrompt): AiAnalysisResult
    {
        if (! $this->apiKey) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured');
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => $this->model,
            'max_tokens' => 1024,
            'system' => $systemPrompt,
            'messages' => [[
                'role' => 'user',
                'content' => $userPrompt . "\n\nReply with strict JSON only.",
            ]],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Claude error: ' . $response->body());
        }

        $body = $response->json();
        $text = collect($body['content'] ?? [])
            ->firstWhere('type', 'text')['text'] ?? '';

        $json = $this->extractJson($text);
        return AiAnalysisResult::fromArray($json);
    }

    protected function extractJson(string $text): array
    {
        $text = trim($text);
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }
        return ['is_task' => false, 'reasoning_short' => 'AI returned non-JSON: ' . $text];
    }
}
