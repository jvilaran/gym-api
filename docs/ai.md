# AI Layer

The AI features are deliberately split into two parts:

1. **Deterministic computation** — numbers (progression, summary metrics) are
   produced by plain PHP services with no AI involved.
2. **Natural-language narration** — an AI provider turns those facts into a
   short coach-style message.

This keeps results predictable and testable, and keeps costs at zero by default.

## Components

| Piece | Responsibility |
| ----- | -------------- |
| `App\Services\Ai\AiProvider` | Interface: `complete(prompt, system)` and `name()` |
| `FakeAiProvider` | Default. Deterministic, no network, no cost |
| `OpenAiProvider` / `AnthropicProvider` / `GeminiProvider` | Stubs for real APIs |
| `ProgressionService` | Rules-based next-session recommendation |
| `SummaryService` | Weight/volume dashboard metrics |
| `AiService` | Builds prompts from the above, calls the provider for prose |

## Progression rules (deterministic)

Double progression per exercise type, based on the latest session:

- If `reps >= 10` (rep target): add `2.5 kg`, reset reps to `8`.
- Otherwise: keep the weight, add `1` rep.

These constants live in `ProgressionService`.

## Provider selection

Resolved from the container in `AppServiceProvider` based on config:

```php
$provider = app(App\Services\Ai\AiProvider::class);
$text = $provider->complete('...', 'system prompt');
```

Config is in `config/ai.php`, driven by env:

```dotenv
AI_PROVIDER=fake          # fake | openai | anthropic | gemini
# OPENAI_API_KEY=...
# ANTHROPIC_API_KEY=...
# GEMINI_API_KEY=...
```

## Enabling a real provider

1. Implement `complete()` in the chosen stub (e.g. `OpenAiProvider`) using the
   provider's HTTP API with Laravel's `Http` client. Use `$system` as the system
   message and `$prompt` as the user message; return the text.
2. Set `AI_PROVIDER` to that driver and provide its API key.
3. Keep the default on `fake` in testing so the suite never calls out.

The endpoints (`GET /api/ai/recommendation`, `GET /api/ai/analysis`) do not
change when you swap providers — only the `message` text and `provider` field do.
