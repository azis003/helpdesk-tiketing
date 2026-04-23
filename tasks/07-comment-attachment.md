# Fase 7 — Komentar, Klarifikasi, Attachment

> **Tujuan:** Implementasi thread komentar, alur klarifikasi, dan upload attachment pada komentar.
> **Referensi:** Dokumentasi Bag. 4 (Klarifikasi & Komunikasi), Bag. 6 (Klarifikasi), Bag. 11 (Attachment)
> **Prasyarat:** Fase 6 selesai

---

## Task 7.1 — Komentar Biasa di Thread Tiket

**Permission:** `ticket.comment`
**Referensi:** ERD Bag. 10 (ticket_comments), Dokumentasi Bag. 4 catatan 6

**File yang dibuat/diubah:**
1. `app/Http/Controllers/CommentController.php`
2. Komponen komentar di `resources/js/Pages/Ticket/Show.vue`

**Logika:**
```
1. Cek user punya permission 'ticket.comment'
2. Cek user punya akses ke tiket ini (scope visibilitas)
3. Insert ticket_comments (type = 'comment')
4. Jika ada attachment → simpan file, insert ticket_attachments (comment_id terisi)
5. Kirim notifikasi ke semua partisipan kecuali penulis
```

**⚠️ PENTING: Komentar IMMUTABLE — tidak ada fitur edit/delete.**

**Validasi:**
```php
'body' => 'required|string',
'attachments' => 'array|max:3',
'attachments.*' => 'file|max:10240',
```

**Acceptance Criteria:**
- [ ] User bisa tulis komentar di thread tiket
- [ ] Komentar tampil urut by `created_at`
- [ ] Tidak ada tombol edit/delete
- [ ] Attachment per komentar maks 3 file, maks 10MB

---

## Task 7.2 — Minta Klarifikasi (In Progress → Waiting for Info)

**Permission:** `ticket.clarify`
**Aktor:** Helpdesk atau Teknisi (handler aktif)
**Referensi:** Dokumentasi Bag. 6, baris "In Progress → Waiting for Info"

**Logika (dalam 1 transaction):**
```
1. Cek status tiket = 'in_progress'
2. Cek user punya permission 'ticket.clarify'
3. Cek user = handler_id

4. Insert ticket_comments:
   - type = 'clarification'
   - body = pertanyaan handler
   - user_id = handler

5. Insert ticket_time_logs:
   - pause_reason = 'waiting_for_info'
   - note = pertanyaan (opsional, bisa sama dengan body)
   - paused_at = NOW()
   - resumed_at = NULL

6. Update tickets:
   - status = 'waiting_for_info'

7. Insert ticket_histories:
   - action = 'paused'
   - from = 'in_progress', to = 'waiting_for_info'
   - time_log_id = ID dari step 5

8. Kirim notifikasi ke pelapor
```

**UI:** Form khusus "Minta Klarifikasi" di halaman detail tiket, bukan form komentar biasa.

**Acceptance Criteria:**
- [ ] Handler bisa kirim klarifikasi
- [ ] Status berubah ke Waiting for Info
- [ ] Timer di-pause (ticket_time_logs terinsert)
- [ ] Komentar type `clarification` muncul di thread
- [ ] Pelapor dapat notifikasi

---

## Task 7.3 — Balas Klarifikasi (Waiting for Info → In Progress)

**Permission:** `ticket.reply-clarification` (kontekstual: hanya pelapor)
**Aktor:** Pelapor
**Referensi:** Dokumentasi Bag. 6, baris "Waiting for Info → In Progress"

**Logika (dalam 1 transaction):**
```
1. Cek status tiket = 'waiting_for_info'
2. Cek user punya permission 'ticket.reply-clarification'
3. Cek user.id = tiket.reporter_id (hanya pelapor)

4. Insert ticket_comments:
   - type = 'clarification_reply'
   - body = jawaban pelapor

5. Update ticket_time_logs (yang resumed_at = NULL):
   - resumed_at = NOW()
   - duration_seconds = resumed_at - paused_at (dalam detik)

6. Update tickets:
   - status = 'in_progress'
   - total_paused_seconds += duration_seconds dari step 5

7. Insert ticket_histories:
   - action = 'resumed'
   - from = 'waiting_for_info', to = 'in_progress'
   - time_log_id = ID dari step 5

8. Kirim notifikasi ke handler
```

**⚠️ Cara hitung duration_seconds:**
```php
$timeLog = TicketTimeLog::where('ticket_id', $ticket->id)
    ->whereNull('resumed_at')
    ->latest('paused_at')
    ->first();

$timeLog->update([
    'resumed_at' => now(),
    'duration_seconds' => now()->diffInSeconds($timeLog->paused_at),
]);

$ticket->increment('total_paused_seconds', $timeLog->duration_seconds);
```

**Acceptance Criteria:**
- [ ] Pelapor bisa balas klarifikasi
- [ ] Komentar type `clarification_reply` muncul di thread
- [ ] Timer di-resume (ticket_time_logs terupdate)
- [ ] `total_paused_seconds` terupdate
- [ ] Status kembali ke In Progress
- [ ] Handler dapat notifikasi

---

## Task 7.4 — Tandai Butuh Pihak Ketiga (In Progress → Waiting Third Party)

**Permission:** `ticket.mark-third-party`
**Aktor:** Helpdesk atau Teknisi (handler aktif)
**Referensi:** Dokumentasi Bag. 6, baris "In Progress → Waiting Third Party"

**Logika:** Mirip Task 7.2 tapi:
- `pause_reason` = `waiting_third_party`
- Status = `waiting_third_party`
- Kirim notifikasi ke **pelapor**

**UI:** Tombol "Tandai Butuh Pihak Ketiga" + form catatan.

**Acceptance Criteria:**
- [ ] Handler bisa tandai butuh pihak ketiga
- [ ] Timer di-pause
- [ ] Status = Waiting Third Party
- [ ] Pelapor dapat notifikasi

---

## Task 7.5 — Resume dari Pihak Ketiga (Waiting Third Party → In Progress)

**Aktor:** Helpdesk atau Teknisi (handler aktif, klik manual)
**Referensi:** Dokumentasi Bag. 6, baris "Waiting Third Party → In Progress"

**Logika:** Mirip Task 7.3 tapi:
- Trigger = handler klik tombol "Selesai Pihak Ketiga"
- Tidak ada insert komentar clarification_reply
- Kirim notifikasi: **tidak ada** (sesuai mapping)

**Acceptance Criteria:**
- [ ] Handler bisa resume dari pihak ketiga
- [ ] Timer di-resume
- [ ] Status kembali ke In Progress

---

## Task 7.6 — Download Attachment

**Apa yang dilakukan:**
Buat endpoint untuk download file attachment.

**File:** `app/Http/Controllers/AttachmentController.php`

```php
public function download(TicketAttachment $attachment)
{
    // Cek user punya akses ke tiket ini
    $this->authorize('view', $attachment->ticket);

    return Storage::download(
        $attachment->file_path,
        $attachment->original_name
    );
}
```

**Acceptance Criteria:**
- [ ] User yang punya akses ke tiket bisa download attachment
- [ ] File terdownload dengan nama asli (original_name)
- [ ] User tanpa akses → 403
