<?php

namespace App\Providers;

use App\Services\Sources\GmailSourceProvider;
use App\Services\Sources\MondaySourceProvider;
use App\Services\Sources\SlackSourceProvider;
use App\Services\Sources\SourceProviderRegistry;
use App\Services\Sources\TelegramSourceProvider;
use App\Services\Sources\WrikeSourceProvider;
use Illuminate\Support\ServiceProvider;

class SourceProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SourceProviderRegistry::class, function () {
            $registry = new SourceProviderRegistry();
            $registry->register(new GmailSourceProvider());
            $registry->register(new SlackSourceProvider());
            $registry->register(new TelegramSourceProvider());
            $registry->register(new MondaySourceProvider());
            $registry->register(new WrikeSourceProvider());
            return $registry;
        });
    }
}
