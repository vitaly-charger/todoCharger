<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProvider
{
    public function __construct(
        protected ?string $apiKey,
        protected string $model = 'gpt-4o-mini',
    ) {}

    public function name(): string { return 'openai'; }
    public function model(): string { return $this->model; }

    public function analyze(string $systemPrompt, string $userPrompt): AiAnalysisResult
    {
        if (! $this->apiKey) {
            throw new RuntimeException('OPENAI_API_KEY is not configured');
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI error: ' . $response->body());
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? '';
        $json = json_decode($content, true);
        if (! is_array($json)) {
            $json = ['is_task' => false, 'reasoning_short' => 'AI returned non-JSON'];
        }
        return AiAnalysisResult::fromArray($json);
    }
}
