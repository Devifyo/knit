<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Deal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast to the pipeline channel so every connected board updates live when
 * a deal moves between stages (Reverb). See docs/ARCHITECTURE.md §Real-time.
 */
class DealStageChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Deal $deal, public int $fromStageId, public int $toStageId) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('tenant.'.$this->deal->tenant_id.'.pipeline.'.$this->deal->pipeline_id);
    }

    public function broadcastAs(): string
    {
        return 'DealStageChanged';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'deal_id' => $this->deal->id,
            'from_stage_id' => $this->fromStageId,
            'to_stage_id' => $this->toStageId,
            'board_order' => $this->deal->board_order,
        ];
    }
}
