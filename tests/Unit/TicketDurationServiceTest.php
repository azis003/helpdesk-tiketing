<?php

namespace Tests\Unit;

use App\Enums\PauseReason;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketTimeLog;
use App\Models\User;
use App\Services\TicketDurationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketDurationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_duration_mengurangi_total_pause_dan_pause_aktif(): void
    {
        $reporter = User::factory()->create();
        $category = TicketCategory::query()->create(['name' => 'Network']);
        $priority = TicketPriority::query()->create(['name' => 'Low', 'level' => 1]);

        $ticket = Ticket::query()->create([
            'ticket_number' => 'TKT-20260424-0005',
            'reporter_id' => $reporter->id,
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'title' => 'Uji Durasi',
            'description' => 'Hitung durasi efektif',
            'status' => TicketStatus::WaitingForInfo,
            'started_at' => CarbonImmutable::parse('2026-04-24 08:00:00'),
            'total_paused_seconds' => 1800,
        ]);

        TicketTimeLog::query()->create([
            'ticket_id' => $ticket->id,
            'pause_reason' => PauseReason::WaitingForInfo,
            'paused_at' => CarbonImmutable::parse('2026-04-24 09:30:00'),
        ]);

        $service = new TicketDurationService();
        $seconds = $service->calculateEffectiveSeconds(
            $ticket->load('activePauseLog'),
            CarbonImmutable::parse('2026-04-24 10:00:00'),
        );

        $this->assertSame(3600, $seconds);
    }
}
