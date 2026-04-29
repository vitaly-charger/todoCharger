<?php

namespace App\Providers;

use App\Services\Ai\AiProvider;
use App\Services\Ai\AiTaskAnalyzer;
use App\Services\Ai\ClaudeProvider;
use App\Services\Ai\OpenAiProvider;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiProvider::class, function () {
            $provider = strtolower((string) config('inbox.ai.provider', 'claude'));
            return match ($provider) {
                'openai' => new OpenAiProvider(
                    apiKey: config('inbox.ai.openai.api_key'),
                    model: config('inbox.ai.openai.model'),
                ),
                default => new ClaudeProvider(
                    apiKey: config('inbox.ai.claude.api_key'),
                    model: config('inbox.ai.claude.model'),
                ),
            };
        });

        $this->app->singleton(AiTaskAnalyzer::class, function ($app) {
            return new AiTaskAnalyzer($app->make(AiProvider::class));
        });
    }
}
