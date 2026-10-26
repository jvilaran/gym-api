<?php

namespace App\Providers;

use App\Services\Ai\AiProvider;
use App\Services\Ai\AnthropicProvider;
use App\Services\Ai\FakeAiProvider;
use App\Services\Ai\GeminiProvider;
use App\Services\Ai\OpenAiProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiProvider::class, function ($app): AiProvider {
            $driver = config('ai.default', 'fake');
            $config = config("ai.providers.{$driver}", []);

            return match ($driver) {
                'fake' => new FakeAiProvider(),
                'openai' => new OpenAiProvider($config['api_key'] ?? null, $config['model'] ?? 'gpt-4o-mini'),
                'anthropic' => new AnthropicProvider($config['api_key'] ?? null, $config['model'] ?? 'claude-3-5-sonnet-latest'),
                'gemini' => new GeminiProvider($config['api_key'] ?? null, $config['model'] ?? 'gemini-1.5-flash'),
                default => throw new InvalidArgumentException("Driver de IA no soportado: {$driver}"),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
