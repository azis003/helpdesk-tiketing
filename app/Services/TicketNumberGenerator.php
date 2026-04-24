<?php

namespace App\Services;

use App\Models\TicketCounter;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class TicketNumberGenerator
{
    public function generate(?CarbonInterface $referenceTime = null): string
    {
        $referenceTime ??= now();
        $date = $referenceTime->toDateString();

        $sequence = DB::transaction(function () use ($date): int {
            $counter = TicketCounter::query()
                ->lockForUpdate()
                ->where('date', $date)
                ->first();

            if (! $counter) {
                $counter = TicketCounter::query()->create([
                    'date' => $date,
                    'last_number' => 1,
                ]);

                return $counter->last_number;
            }

            $counter->increment('last_number');

            return (int) $counter->fresh()->last_number;
        });

        return sprintf('TKT-%s-%04d', $referenceTime->format('Ymd'), $sequence);
    }
}
