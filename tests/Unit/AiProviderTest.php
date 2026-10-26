<?php

namespace Tests\Unit;

use App\Services\Ai\AiProvider;
use App\Services\Ai\FakeAiProvider;
use Tests\TestCase;

class AiProviderTest extends TestCase
{
    public function test_container_resolves_fake_driver_by_default(): void
    {
        config()->set('ai.default', 'fake');

        $provider = $this->app->make(AiProvider::class);

        $this->assertInstanceOf(FakeAiProvider::class, $provider);
        $this->assertSame('fake', $provider->name());
    }

    public function test_fake_provider_is_deterministic(): void
    {
        $provider = new FakeAiProvider();

        $prompt = "Recomendaciones:\n- Sentadilla: sube 2.5kg";
        $first = $provider->complete($prompt);
        $second = $provider->complete($prompt);

        $this->assertSame($first, $second);
        $this->assertStringContainsString('Sentadilla: sube 2.5kg', $first);
    }

    public function test_fake_provider_drops_instruction_lines(): void
    {
        $provider = new FakeAiProvider();

        $message = $provider->complete(
            "Recomendaciones:\n- Sentadilla: sube 2.5kg\n"
            .'Redacta un mensaje corto y motivador explicando la progresión.'
        );

        // The instruction aimed at the model must never leak to the user.
        $this->assertStringNotContainsString('Redacta', $message);
        $this->assertStringContainsString('Sentadilla', $message);
    }
}
