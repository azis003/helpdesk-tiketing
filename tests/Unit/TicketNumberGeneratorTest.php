<?php

namespace Tests\Unit;

use App\Services\TicketNumberGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_nomor_tiket_bertambah_pada_hari_yang_sama(): void
    {
        $generator = new TicketNumberGenerator();
        $time = CarbonImmutable::parse('2026-04-24 08:00:00');

        $first = $generator->generate($time);
        $second = $generator->generate($time->addMinute());

        $this->assertSame('TKT-20260424-0001', $first);
        $this->assertSame('TKT-20260424-0002', $second);
    }

    public function test_nomor_tiket_reset_pada_hari_berbeda(): void
    {
        $generator = new TicketNumberGenerator();

        $firstDay = $generator->generate(CarbonImmutable::parse('2026-04-24 08:00:00'));
        $nextDay = $generator->generate(CarbonImmutable::parse('2026-04-25 08:00:00'));

        $this->assertSame('TKT-20260424-0001', $firstDay);
        $this->assertSame('TKT-20260425-0001', $nextDay);
    }
}
