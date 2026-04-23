# Fase 10 — Scheduler Jobs

> **Tujuan:** Implementasi 3 scheduler job untuk auto-close dan reminder.
> **Referensi:** Dokumentasi Bag. 14 (Scheduler & Queue Jobs)
> **Prasyarat:** Fase 9 selesai

---

## Task 10.1 — Auto-Close Resolved Tickets (72 jam)

**Referensi:** Dokumentasi Bag. 14.1

**File:** `app/Console/Commands/AutoCloseResolvedTickets.php`

```bash
php artisan make:command AutoCloseResolvedTickets
```

**Logika:**
```php
class AutoCloseResolvedTickets extends Command
{
    protected $signature = 'tickets:auto-close-resolved';
    protected $description = 'Close resolved tickets after 72 hours without response';

    public function handle(): void
    {
        $tickets = Ticket::where('status', TicketStatus::Resolved)
            ->where('auto_close_at', '<=', now())
            ->get();

        foreach ($tickets as $ticket) {
            DB::transaction(function () use ($ticket) {
                $ticket->update([
                    'status' => TicketStatus::Closed,
                    'closed_at' => now(),
                ]);

                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'actor_id' => null, // sistem
                    'from_status' => TicketStatus::Resolved->value,
                    'to_status' => TicketStatus::Closed->value,
                    'action' => HistoryAction::AutoClosed->value,
                ]);

                NotificationService::send(
                    $ticket->reporter_id,
                    $ticket->id,
                    'ticket_auto_closed',
                    "Tiket {$ticket->ticket_number} ditutup otomatis",
                    'Tiket ditutup karena tidak ada respons selama 3 hari.'
                );
            });
        }

        $this->info("Auto-closed {$tickets->count()} tickets.");
    }
}
```

**Daftarkan di `routes/console.php`:**
```php
Schedule::command('tickets:auto-close-resolved')->hourly();
```

**Acceptance Criteria:**
- [ ] Tiket Resolved yang sudah 72+ jam → auto-close
- [ ] `closed_at` terisi
- [ ] Audit trail: action = `auto_closed`, actor_id = NULL
- [ ] Pelapor dapat notifikasi
- [ ] Tiket yang sudah Closed tidak diproses ulang (idempotent)

---

## Task 10.2 — Auto-Reminder Waiting for Info (3 hari)

**Referensi:** Dokumentasi Bag. 14.3

**File:** `app/Console/Commands/RemindWaitingForInfoTickets.php`

**Logika:**
```php
protected $signature = 'tickets:remind-waiting-info';

public function handle(): void
{
    $tickets = Ticket::where('status', TicketStatus::WaitingForInfo)
        ->where('updated_at', '<=', now()->subDays(3))
        ->get();

    foreach ($tickets as $ticket) {
        // Cek apakah sudah dikirim hari ini (hindari spam)
        $alreadySentToday = Notification::where('ticket_id', $ticket->id)
            ->where('type', 'ticket_reminder_waiting')
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySentToday) continue;

        NotificationService::send(
            $ticket->reporter_id,
            $ticket->id,
            'ticket_reminder_waiting',
            "Pengingat: Tiket {$ticket->ticket_number} menunggu jawaban Anda",
            'Mohon segera menjawab pertanyaan klarifikasi.'
        );
    }
}
```

**Daftarkan:**
```php
Schedule::command('tickets:remind-waiting-info')->dailyAt('08:00');
```

**Acceptance Criteria:**
- [ ] Reminder terkirim ke pelapor tiket yang 3+ hari di Waiting for Info
- [ ] Tidak kirim berulang di hari yang sama
- [ ] Status tiket TIDAK berubah (hanya notifikasi)

---

## Task 10.3 — Auto-Close Stale Waiting for Info (14 hari)

**Referensi:** Dokumentasi Bag. 14.4 + Bag. 6 (transisi Waiting for Info → Closed)

**File:** `app/Console/Commands/AutoCloseStaleWaitingTickets.php`

**Logika:**
```php
protected $signature = 'tickets:auto-close-stale-waiting';

public function handle(): void
{
    $tickets = Ticket::where('status', TicketStatus::WaitingForInfo)
        ->where('updated_at', '<=', now()->subDays(14))
        ->get();

    foreach ($tickets as $ticket) {
        DB::transaction(function () use ($ticket) {
            // 1. Resume timer terlebih dahulu
            $timeLog = TicketTimeLog::where('ticket_id', $ticket->id)
                ->whereNull('resumed_at')
                ->latest('paused_at')
                ->first();

            if ($timeLog) {
                $durationSeconds = now()->diffInSeconds($timeLog->paused_at);
                $timeLog->update([
                    'resumed_at' => now(),
                    'duration_seconds' => $durationSeconds,
                ]);
                $ticket->increment('total_paused_seconds', $durationSeconds);
            }

            // 2. Close tiket
            $ticket->update([
                'status' => TicketStatus::Closed,
                'closed_at' => now(),
            ]);

            // 3. Audit trail
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id' => null,
                'from_status' => TicketStatus::WaitingForInfo->value,
                'to_status' => TicketStatus::Closed->value,
                'action' => HistoryAction::AutoClosedNoResponse->value,
                'time_log_id' => $timeLog?->id,
            ]);

            // 4. Notifikasi ke pelapor + handler
            NotificationService::send(
                [$ticket->reporter_id, $ticket->handler_id],
                $ticket->id,
                'ticket_auto_closed_no_response',
                "Tiket {$ticket->ticket_number} ditutup otomatis",
                'Tiket ditutup karena tidak ada jawaban selama 14 hari.'
            );
        });
    }
}
```

**Daftarkan:**
```php
Schedule::command('tickets:auto-close-stale-waiting')->dailyAt('09:00');
```

**Acceptance Criteria:**
- [ ] Tiket WFI yang 14+ hari tanpa jawaban → auto-close
- [ ] Timer di-resume dulu (duration_seconds dihitung)
- [ ] `total_paused_seconds` terupdate
- [ ] Status = Closed, `closed_at` terisi
- [ ] Audit trail: action = `auto_closed_no_response`
- [ ] Pelapor + handler dapat notifikasi

---

## Task 10.4 — Test Scheduler

**Apa yang dilakukan:**
Test ketiga command secara manual.

```bash
# Test satu per satu
php artisan tickets:auto-close-resolved
php artisan tickets:auto-close-stale-waiting
php artisan tickets:remind-waiting-info

# Cek schedule list
php artisan schedule:list
```

**⚠️ Untuk production, jalankan scheduler via cron:**
```
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

**Acceptance Criteria:**
- [ ] Ketiga command bisa dijalankan manual tanpa error
- [ ] `php artisan schedule:list` menampilkan 3 scheduled commands
