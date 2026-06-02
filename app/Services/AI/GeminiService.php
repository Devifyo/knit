<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Single entry point for every Gemini-backed AI feature in the platform.
 *
 * Contract (see docs/ARCHITECTURE.md §AI): each method is cache-keyed by a
 * content hash, degrades gracefully to a safe fallback when AI is disabled or
 * the upstream call fails, and persists results to `ai_outputs` for auditing
 * (table + queue/retry wiring lands in Phase 7).
 *
 * NO raw Gemini HTTP calls may live anywhere else in the codebase.
 *
 * Phase 0 ships the interface + graceful fallbacks; Phase 7 wires the real
 * Gemini transport, queueing, token accounting and rate-limit handling.
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
        return $this->remember('lead.score', $lead, fn () => [
            'score' => 0,
            'reasons' => ['AI scoring not yet enabled (Phase 7).'],
        ]);
    }

    /** @return array{action:string, rationale:string} */
    public function recommendNextAction(object $deal): array
    {
        return $this->remember('deal.next_action', $deal, fn () => [
            'action' => 'review',
            'rationale' => 'AI recommendations not yet enabled (Phase 7).',
        ]);
    }

    /** @return array{risk:string, factors:array<int,string>} */
    public function predictDealRisk(object $deal): array
    {
        return $this->remember('deal.risk', $deal, fn () => [
            'risk' => 'low',
            'factors' => [],
        ]);
    }

    /**
     * @param  array<int,object>  $deals
     * @return array{by_month:array<string,int>, confidence:float}
     */
    public function forecastRevenue(array $deals): array
    {
        return $this->remember('revenue.forecast', $deals, fn () => [
            'by_month' => [],
            'confidence' => 0.0,
        ]);
    }

    public function summarizeTicket(object $ticket): string
    {
        return $this->remember('ticket.summary', $ticket, fn () => '');
    }

    /** @return array<int,string> */
    public function suggestReply(object $ticket): array
    {
        return $this->remember('ticket.reply', $ticket, fn () => []);
    }

    public function analyzeSentiment(string $text): string
    {
        return $this->remember('sentiment', $text, fn () => 'neu');
    }

    /** @param array<string,mixed> $context */
    public function chatbotReply(string $message, array $context = []): string
    {
        return $this->remember('chatbot', [$message, $context], fn () => '');
    }

    /** @param array<string,mixed> $brief */
    public function generateEmail(array $brief): string
    {
        return $this->remember('email.generate', $brief, fn () => '');
    }

    /** @param array<string,mixed> $options */
    public function generateProposal(object $deal, array $options = []): string
    {
        return $this->remember('proposal.generate', [$deal, $options], fn () => '');
    }

    /** @return array{summary:string, action_items:array<int,string>, crm_updates:array<int,mixed>} */
    public function summarizeMeeting(string $transcript): array
    {
        return $this->remember('meeting.summary', $transcript, fn () => [
            'summary' => '',
            'action_items' => [],
            'crm_updates' => [],
        ]);
    }

    /** @return array{probability:float, drivers:array<int,string>} */
    public function predictChurn(object $account): array
    {
        return $this->remember('account.churn', $account, fn () => [
            'probability' => 0.0,
            'drivers' => [],
        ]);
    }

    public function scoreCustomerHealth(object $entity): int
    {
        return $this->remember('health.score', $entity, fn () => 50);
    }

    /**
     * Cache by content hash and degrade gracefully. The real Gemini call is
     * injected in Phase 7; until then (or on any failure) the fallback wins.
     *
     * @template T
     *
     * @param  callable():T  $fallback
     * @return T
     */
    protected function remember(string $feature, mixed $payload, callable $fallback): mixed
    {
        $key = 'ai:'.$feature.':'.$this->hash($payload);

        return Cache::remember($key, now()->addHours(6), function () use ($fallback, $feature) {
            if (! $this->enabled || $this->apiKey === null) {
                return $fallback();
            }

            try {
                // Phase 7: dispatch the queued Gemini call here.
                return $fallback();
            } catch (Throwable $e) {
                Log::warning("GeminiService[{$feature}] failed, using fallback", ['error' => $e->getMessage()]);

                return $fallback();
            }
        });
    }

    protected function hash(mixed $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_PARTIAL_OUTPUT_ON_ERROR) ?: serialize($payload));
    }
}
