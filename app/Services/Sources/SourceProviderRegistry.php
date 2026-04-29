<?php

namespace App\Services\Sources;

use App\Models\SourceAccount;
use InvalidArgumentException;

class SourceProviderRegistry
{
    /** @var array<string, AbstractSourceProvider> */
    protected array $providers = [];

    public function register(AbstractSourceProvider $provider): void
    {
        $this->providers[$provider->type()] = $provider;
    }

    public function for(string|SourceAccount $type): AbstractSourceProvider
    {
        $key = $type instanceof SourceAccount ? $type->type : $type;
        if (! isset($this->providers[$key])) {
            throw new InvalidArgumentException("Unknown source type: {$key}");
        }
        return $this->providers[$key];
    }

    /** @return array<string, AbstractSourceProvider> */
    public function all(): array
    {
        return $this->providers;
    }
}
