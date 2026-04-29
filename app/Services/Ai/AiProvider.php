<?php

namespace App\Services\Ai;

interface AiProvider
{
    /**
     * Send a prompt to the model and return the parsed JSON analysis.
     */
    public function analyze(string $systemPrompt, string $userPrompt): AiAnalysisResult;

    public function name(): string;

    public function model(): string;
}
