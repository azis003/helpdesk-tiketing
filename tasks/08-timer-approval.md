# Fase 8 — Timer Pengerjaan & Approval Manager IT

> **Tujuan:** Implementasi kalkulasi durasi efektif dan alur approval Manager IT.
> **Referensi:** Dokumentasi Bag. 8 (Waktu Pengerjaan), Bag. 6 (Approval Manager IT)
> **Prasyarat:** Fase 7 selesai

---

## Task 8.1 — Tampilkan Durasi Pengerjaan Efektif

**Referensi:** Dokumentasi Bag. 8 (Rumus kalkulasi)

**File:** `app/Services/TicketDurationService.php`

**Rumus:**
```
durasi_efektif_detik = (closed_at - started_at) - total_paused_seconds
```

**Jika tiket belum closed (masih in_progress):**
```
durasi_sementara_detik = (NOW - started_at) - total_paused_seconds
```

**Jika tiket masih pause (waiting_for_info / waiting_third_party):**
```
pause_aktif_detik = NOW - paused_at (dari time_log yang resumed_at = NULL)
durasi_sementara = (NOW - started_at) - total_paused_seconds - pause_aktif_detik
```

**Format tampilan:** `X hari Y jam Z menit`

```php
class TicketDurationService
{
    public static function calculate(Ticket $ticket): ?string
    {
        if (!$ticket->started_at) return null;

        $endTime = $ticket->closed_at ?? now();
        $totalSeconds = $endTime->diffInSeconds($ticket->started_at);
        $pausedSeconds = $ticket->total_paused_seconds;

        // Jika masih dalam pause aktif
        $activePause = $ticket->timeLogs()
            ->whereNull('resumed_at')
            ->first();
        if ($activePause) {
            $pausedSeconds += now()->diffInSeconds($activePause->paused_at);
        }

        $effectiveSeconds = max(0, $totalSeconds - $pausedSeconds);

        return self::formatDuration($effectiveSeconds);
    }

    private static function formatDuration(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        $parts = [];
        if ($days > 0) $parts[] = "{$days} hari";
        if ($hours > 0) $parts[] = "{$hours} jam";
        $parts[] = "{$minutes} menit";

        return implode(' ', $parts);
    }
}
```

**Tampilkan di:**
- Halaman detail tiket (selalu)
- Halaman laporan (untuk tiket yang sudah Closed)

**Acceptance Criteria:**
- [ ] Durasi efektif ditampilkan di halaman detail tiket
- [ ] Format: "X hari Y jam Z menit"
- [ ] Pause time tidak dihitung (dikurangi)
- [ ] Tiket yang belum di-handle (started_at NULL) → tampil "-"
- [ ] Tiket yang masih in_progress → tampil durasi berjalan (live)

---

## Task 8.2 — Request Approval Manager IT (In Progress → Pending Approval)

**Permission:** `ticket.request-approval`
**Aktor:** Helpdesk atau Teknisi (handler aktif)
**Referensi:** Dokumentasi Bag. 6, baris "In Progress → Pending Approval"

**Logika (dalam 1 transaction):**
```
1. Cek status tiket = 'in_progress'
2. Cek user punya permission 'ticket.request-approval'
3. Cek user = handler_id

4. Set semua ticket_approvals lama untuk tiket ini:
   - is_current = 0

5. Insert ticket_approvals:
   - ticket_id, requested_by = user login
   - status = 'pending', is_current = 1
   - note = alasan request (opsional)

6. Update tickets.status = 'pending_approval'

7. Insert ticket_histories:
   - action = 'approval_requested'
   - from = 'in_progress', to = 'pending_approval'

8. Kirim notifikasi ke semua Manager IT
```

**⚠️ Timer TETAP berjalan saat Pending Approval (tidak di-pause).**

**Acceptance Criteria:**
- [ ] Handler bisa request approval
- [ ] Status berubah ke Pending Approval
- [ ] Record ticket_approvals terinsert
- [ ] Approval lama di-set is_current = 0
- [ ] Semua Manager IT dapat notifikasi
- [ ] Timer tetap jalan (tidak pause)

---

## Task 8.3 — Manager IT Approve (Pending Approval → In Progress)

**Permission:** `ticket.approve`
**Aktor:** Manager IT
**Referensi:** Dokumentasi Bag. 6, baris "Pending Approval → In Progress (approved)"

**Logika (dalam 1 transaction):**
```
1. Cek status tiket = 'pending_approval'
2. Cek user punya permission 'ticket.approve'

3. Update ticket_approvals (yang is_current = 1):
   - status = 'approved'
   - reviewed_by = user login
   - reviewed_at = NOW()

4. Update tickets.status = 'in_progress'

5. Insert ticket_histories:
   - action = 'approved'
   - from = 'pending_approval', to = 'in_progress'

6. Kirim notifikasi ke handler yang request (ticket_approvals.requested_by)
```

**Acceptance Criteria:**
- [ ] Manager IT bisa approve
- [ ] Status kembali ke In Progress
- [ ] Handler yang request dapat notifikasi

---

## Task 8.4 — Manager IT Reject (Pending Approval → Rejected → Closed)

**Permission:** `ticket.approve`
**Aktor:** Manager IT
**Referensi:** Dokumentasi Bag. 6, baris "Pending Approval → Rejected → Closed"

**⚠️ PENTING:** Kedua transisi dilakukan dalam **satu database transaction**. Insert **2 record** ticket_histories.

**Logika (dalam 1 transaction):**
```
1. Cek status tiket = 'pending_approval'
2. Cek user punya permission 'ticket.approve'
3. Validasi: note (alasan penolakan) WAJIB diisi

4. Update ticket_approvals (is_current = 1):
   - status = 'rejected'
   - reviewed_by = user login
   - reviewed_at = NOW()
   - note = alasan

5. Update tickets:
   - status = 'closed'  (langsung closed, bukan rejected)
   - closed_at = NOW()

6. Insert ticket_histories PERTAMA:
   - action = 'rejected'
   - from = 'pending_approval', to = 'rejected'
   - note = alasan penolakan

7. Insert ticket_histories KEDUA:
   - action = 'rejected_closed'
   - from = 'rejected', to = 'closed'

8. Kirim notifikasi ke:
   - Pelapor (reporter_id) + alasan penolakan
   - Handler yang request (ticket_approvals.requested_by)
```

**Acceptance Criteria:**
- [ ] Manager IT bisa reject dengan alasan
- [ ] Alasan wajib diisi
- [ ] Status akhir = Closed (bukan Rejected)
- [ ] 2 record ticket_histories terinsert (rejected + rejected_closed)
- [ ] Pelapor dan handler dapat notifikasi + alasan
