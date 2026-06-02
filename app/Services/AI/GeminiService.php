<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiOutput;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Single entry point for every Gemini-backed AI feature.
 *
 * Each method is cache-keyed by a content hash, runs through the real Gemini REST
 * transport when AI is enabled for the active tenant, persists the result to
 * `ai_outputs` for audit, and degrades gracefully to a safe fallback when AI is
 * disabled or the upstream call fails (e.g. a 429 rate-limit). NO raw Gemini HTTP
 * may live anywhere else in the codebase.
 */
class GeminiService
{
    public function __construct(
        protected readonly bool $enabled = false,
        protected readonly ?string $apiKey = null,
        protected readonly string $model = 'gemini-2.5-flash',
    ) {}

    /** @return array{score:int, reasons:array<int,string>} */
    public function scoreLead(object $lead): array
    {
        $data = $this->attrs($lead, ['name', 'email', 'phone', 'source', 'status', 'score', 'custom_fields']);

        return $this->run('lead.score', $lead, $data,
            'You are a B2B sales lead-scoring assistant. Score this lead 0-100 (higher = more likely to convert) '
            .'and give 2-4 short reasons. Return JSON {"score": int, "reasons": string[]}. Lead: '.json_encode($data),
            fn ($d) => ['score' => (int) max(0, min(100, $d['score'] ?? 0)), 'reasons' => array_slice((array) ($d['reasons'] ?? []), 0, 4)],
            ['score' => 0, 'reasons' => ['AI scoring is off for this workspace.']],
            json: true,
        );
    }

    /** @return array{action:string, rationale:string} */
    public function recommendNextAction(object $deal): array
    {
        $data = $this->attrs($deal, ['name', 'amount', 'currency', 'status', 'probability']);

        return $this->run('deal.next_action', $deal, $data,
            'You are a sales coach. Given this deal, recommend the single best next action and a one-sentence rationale. '
            .'Return JSON {"action": string, "rationale": string}. Deal: '.json_encode($data),
            fn ($d) => ['action' => (string) ($d['action'] ?? 'review'), 'rationale' => (string) ($d['rationale'] ?? '')],
            ['action' => 'review', 'rationale' => 'AI recommendations are off for this workspace.'],
            json: true,
        );
    }

    /** @return array{risk:string, factors:array<int,string>} */
    public function predictDealRisk(object $deal): array
    {
        $data = $this->attrs($deal, ['name', 'amount', 'status', 'probability', 'expected_close_date']);

        return $this->run('deal.risk', $deal, $data,
            'Assess the risk this deal is lost. Return JSON {"risk": "low"|"medium"|"high", "factors": string[]}. Deal: '.json_encode($data),
            fn ($d) => ['risk' => (string) ($d['risk'] ?? 'low'), 'factors' => array_slice((array) ($d['factors'] ?? []), 0, 5)],
            ['risk' => 'low', 'factors' => []],
            json: true,
        );
    }

    /**
     * @param  array<int,object>  $deals
     * @return array{by_month:array<string,int>, confidence:float}
     */
    public function forecastRevenue(array $deals): array
    {
        $data = array_map(fn ($d) => $this->attrs($d, ['amount', 'probability', 'expected_close_date', 'status']), $deals);

        return $this->run('revenue.forecast', $deals, $data,
            'Forecast weighted revenue by month from these deals (amounts are minor units). '
            .'Return JSON {"by_month": {"YYYY-MM": int}, "confidence": number 0-1}. Deals: '.json_encode($data),
            fn ($d) => ['by_month' => (array) ($d['by_month'] ?? []), 'confidence' => (float) ($d['confidence'] ?? 0)],
            ['by_month' => [], 'confidence' => 0.0],
            json: true,
        );
    }

    public function summarizeTicket(object $ticket): string
    {
        $data = $this->attrs($ticket, ['subject', 'body', 'priority', 'status']);

        return $this->run('ticket.summary', $ticket, $data,
            'Summarize this support ticket in 1-2 sentences for an agent. Ticket: '.json_encode($data),
            fn ($t) => trim((string) $t),
            '',
        );
    }

    /** @return array<int,string> */
    public function suggestReply(object $ticket): array
    {
        $data = $this->attrs($ticket, ['subject', 'body', 'priority']);

        return $this->run('ticket.reply', $ticket, $data,
            'Suggest 2 short, friendly agent reply drafts for this ticket. Return JSON {"replies": string[]}. Ticket: '.json_encode($data),
            fn ($d) => array_slice((array) ($d['replies'] ?? []), 0, 3),
            [],
            json: true,
        );
    }

    public function analyzeSentiment(string $text): string
    {
        return $this->run('sentiment', $text, $text,
            'Classify the sentiment of this text as exactly one word: pos, neu, or neg. Text: '.$text,
            fn ($t) => in_array($r = strtolower(trim((string) $t)), ['pos', 'neu', 'neg'], true) ? $r : 'neu',
            'neu',
        );
    }

    /** @param array<string,mixed> $context */
    public function chatbotReply(string $message, array $context = []): string
    {
        return $this->run('chatbot', [$message, $context], [$message, $context],
            "You are a helpful support assistant. Answer the user's question using ONLY the knowledge base below; "
            ."if it isn't covered, say a human agent will follow up. Keep it to 3 sentences.\n\n"
            ."Knowledge base:\n".(string) ($context['kb'] ?? '(none)')."\n\nQuestion: ".$message,
            fn ($t) => trim((string) $t),
            '',
        );
    }

    /** @param array<string,mixed> $brief */
    public function generateEmail(array $brief): string
    {
        return $this->run('email.generate', $brief, $brief,
            'Write a concise, friendly sales email body (no subject) from this brief: '.json_encode($brief),
            fn ($t) => trim((string) $t),
            '',
        );
    }

    /** @param array<string,mixed> $options */
    public function generateProposal(object $deal, array $options = []): string
    {
        $data = $this->attrs($deal, ['name', 'amount', 'currency']);

        return $this->run('proposal.generate', [$data, $options], [$data, $options],
            'Draft a short proposal summary for this deal: '.json_encode([$data, $options]),
            fn ($t) => trim((string) $t),
            '',
        );
    }

    /** @return array{summary:string, action_items:array<int,string>, crm_updates:array<int,string>} */
    public function summarizeMeeting(string $transcript): array
    {
        return $this->run('meeting.summary', $transcript, $transcript,
            'Summarize this meeting transcript for a CRM. Return JSON '
            .'{"summary": string, "action_items": string[], "crm_updates": string[]}. '
            .'action_items are concrete follow-up tasks. Transcript: '.$transcript,
            fn ($d) => [
                'summary' => (string) ($d['summary'] ?? ''),
                'action_items' => array_values(array_filter(array_map('strval', (array) ($d['action_items'] ?? [])))),
                'crm_updates' => array_values(array_filter(array_map('strval', (array) ($d['crm_updates'] ?? [])))),
            ],
            ['summary' => '', 'action_items' => [], 'crm_updates' => []],
            json: true,
        );
    }

    /** @return array{probability:float, drivers:array<int,string>} */
    public function predictChurn(object $account): array
    {
        $data = $this->attrs($account, ['health_score', 'renewal_date', 'renewal_status']);

        return $this->run('account.churn', $account, $data,
            'Estimate churn probability (0-1) and key drivers. Return JSON {"probability": number, "drivers": string[]}. Account: '.json_encode($data),
            fn ($d) => ['probability' => (float) ($d['probability'] ?? 0), 'drivers' => array_slice((array) ($d['drivers'] ?? []), 0, 5)],
            ['probability' => 0.0, 'drivers' => []],
            json: true,
        );
    }

    public function scoreCustomerHealth(object $entity): int
    {
        $data = $this->attrs($entity, ['name', 'health_score', 'annual_revenue', 'industry']);

        return $this->run('health.score', $entity, $data,
            'Score this account\'s health 0-100. Return JSON {"score": int}. Account: '.json_encode($data),
            fn ($d) => (int) max(0, min(100, $d['score'] ?? 50)),
            50,
            json: true,
        );
    }

    // ── transport ───────────────────────────────────────────────────────────

    /**
     * Cache → toggle gate → Gemini call → parse → audit → (graceful) fallback.
     *
     * @template T
     *
     * @param  callable(mixed):T  $parse  receives decoded JSON (json:true) or raw string
     * @param  T  $fallback
     * @return T
     */
    protected function run(string $feature, mixed $cacheKeyData, mixed $entity, string $prompt, callable $parse, mixed $fallback, bool $json = false): mixed
    {
        $key = 'ai:'.$feature.':'.$this->hash([$cacheKeyData, $this->model, $json]);

        return Cache::remember($key, now()->addHours(6), function () use ($feature, $entity, $prompt, $parse, $fallback, $json) {
            if (! $this->enabledForCurrentTenant()) {
                return $fallback;
            }

            try {
                [$text, $tokens] = $this->callGemini($prompt, $json);
                $payload = $json ? (json_decode($text, true) ?? throw new \RuntimeException('Bad JSON from model')) : $text;
                $result = $parse($payload);

                $this->audit($feature, $entity, $text, $tokens);

                return $result;
            } catch (Throwable $e) {
                Log::warning("GeminiService[{$feature}] failed, using fallback", ['error' => $e->getMessage()]);

                return $fallback;
            }
        });
    }

    /**
     * @return array{0:string, 1:int} [text, tokenCount]
     */
    protected function callGemini(string $prompt, bool $json): array
    {
        $generationConfig = ['temperature' => 0.4];
        if ($json) {
            $generationConfig['response_mime_type'] = 'application/json';
        }

        $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
            ->timeout(25)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => $generationConfig,
            ])
            ->throw()
            ->json();

        $text = (string) data_get($response, 'candidates.0.content.parts.0.text', '');
        $tokens = (int) data_get($response, 'usageMetadata.totalTokenCount', 0);

        return [$text, $tokens];
    }

    protected function enabledForCurrentTenant(): bool
    {
        if (! $this->enabled || $this->apiKey === null || $this->apiKey === '') {
            return false;
        }

        // Per-tenant kill switch (Tenant.ai_enabled). Global config wins when central.
        $tenant = function_exists('tenant') ? tenant() : null;

        return $tenant === null || (bool) ($tenant->ai_enabled ?? true);
    }

    protected function audit(string $feature, mixed $entity, string $response, int $tokens): void
    {
        AiOutput::create([
            'tenant_id' => function_exists('tenant') && tenant() ? tenant()->getTenantKey() : null,
            'feature' => $feature,
            'entity_type' => is_object($entity) && method_exists($entity, 'getKey') ? $entity::class : null,
            'entity_id' => is_object($entity) && method_exists($entity, 'getKey') ? $entity->getKey() : null,
            'prompt_hash' => $this->hash($response),
            'response' => $response,
            'tokens' => $tokens,
        ]);
    }

    /**
     * @param  array<int,string>  $keys
     * @return array<string,mixed>
     */
    protected function attrs(object $model, array $keys): array
    {
        if (method_exists($model, 'only')) {
            return $model->only($keys);
        }

        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $model->{$k} ?? null;
        }

        return $out;
    }

    protected function hash(mixed $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_PARTIAL_OUTPUT_ON_ERROR) ?: serialize($payload));
    }
}
