<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Carbon\CarbonInterface;

class TicketDurationService
{
    public function calculateEffectiveSeconds(Ticket $ticket, ?CarbonInterface $referenceTime = null): int
    {
        if (! $ticket->started_at) {
            return 0;
        }

        $referenceTime ??= now();

        $endedAt = $ticket->closed_at
            ?? ($ticket->status === TicketStatus::Resolved ? ($ticket->resolved_at ?? $referenceTime) : $referenceTime);

        $totalSeconds = $ticket->started_at->diffInSeconds($endedAt);
        $pausedSeconds = $ticket->total_paused_seconds;

        if ($ticket->status->isPaused()) {
            $activePause = $ticket->relationLoaded('activePauseLog')
                ? $ticket->activePauseLog
                : $ticket->activePauseLog()->first();

            if ($activePause?->paused_at) {
                $pausedSeconds += $activePause->paused_at->diffInSeconds($referenceTime);
            }
        }

        return max(0, $totalSeconds - $pausedSeconds);
    }
}
