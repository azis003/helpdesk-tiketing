# Fase 6 — Ticket Workflow: Status Transitions

> **Tujuan:** Implementasi semua transisi status tiket sesuai alur bisnis.
> **Referensi:** Dokumentasi Bag. 6 (Tabel Transisi Status)
> **Prasyarat:** Fase 5 selesai

---

## Panduan Umum

Setiap aksi transisi status **wajib melakukan 3 hal:**
1. **Update status tiket** di tabel `tickets`
2. **Insert audit trail** di tabel `ticket_histories`
3. **Kirim notifikasi** (implementasi di Fase 9, sementara buat TODO comment)

Buat semua aksi di **satu controller** atau pisahkan per service:
- `app/Services/TicketWorkflowService.php` — rekomendasi: 1 method per aksi

---

## Task 6.1 — Verifikasi Tiket (Open → Verification)

**Permission:** `ticket.verify`
**Aktor:** Helpdesk
**Referensi:** Dokumentasi Bag. 6, baris "Open → Verification"

**Logika:**
```
1. Cek status tiket saat ini = 'open'
2. Cek user punya permission 'ticket.verify'
3. Update tickets.status = 'verification'
4. Insert ticket_histories (action = 'verified', from = 'open', to = 'verification')
```

**Di tahap verifikasi, Helpdesk juga bisa:**
- Ubah prioritas → cek permission `ticket.change-priority`
- Ubah kategori → cek permission `ticket.change-category`
- Ini bisa dijadikan tombol/form terpisah di halaman detail tiket

**Acceptance Criteria:**
- [ ] Helpdesk bisa klik "Verifikasi" pada tiket Open
- [ ] Status berubah ke Verification
- [ ] Audit trail tercatat
- [ ] Helpdesk bisa ubah prioritas/kategori saat verifikasi

---

## Task 6.2 — Assign ke Handler (Verification → In Progress)

**Permission:** `ticket.assign`
**Aktor:** Helpdesk
**Referensi:** Dokumentasi Bag. 6, baris "Verification → In Progress"

**Dua opsi:**
1. **Handle sendiri:** `handler_id` = Helpdesk yang login
2. **Assign ke Teknisi:** `handler_id` = Teknisi yang dipilih dari dropdown

**Logika:**
```
1. Cek status tiket = 'verification'
2. Cek user punya permission 'ticket.assign'
3. Update tickets:
   - status = 'in_progress'
   - handler_id = selected handler
   - started_at = NOW() (hanya jika started_at masih NULL)
4. Insert ticket_histories:
   - action = 'assigned'
   - new_handler_id = handler yang di-assign
5. Kirim notifikasi ke handler (jika assign ke Teknisi)
```

**⚠️ PENTING:** `started_at` hanya diisi **pertama kali** tiket masuk In Progress. Jangan timpa jika sudah terisi (kasus return dari Teknisi lalu assign ulang).

**Acceptance Criteria:**
- [ ] Helpdesk bisa handle sendiri (jadi handler)
- [ ] Helpdesk bisa assign ke Teknisi (pilih dari dropdown)
- [ ] `started_at` terisi saat pertama kali In Progress
- [ ] `started_at` tidak berubah jika sudah terisi sebelumnya
- [ ] `new_handler_id` terisi di audit trail

---

## Task 6.3 — Reject Tiket saat Verifikasi (Verification → Closed)

**Permission:** `ticket.verify` (reuse)
**Aktor:** Helpdesk
**Referensi:** Dokumentasi Bag. 6, baris "Verification → Closed" (Reject)

**Logika:**
```
1. Cek status tiket = 'verification'
2. Cek user punya permission 'ticket.verify'
3. Validasi: note (alasan) wajib diisi
4. Update tickets:
   - status = 'closed'
   - closed_at = NOW()
   - started_at tetap NULL
5. Insert ticket_histories:
   - action = 'rejected_by_helpdesk'
   - from = 'verification', to = 'closed'
   - note = alasan reject (wajib)
6. Kirim notifikasi ke pelapor + alasan
```

**Acceptance Criteria:**
- [ ] Helpdesk bisa reject tiket saat verifikasi
- [ ] Alasan wajib diisi (validasi)
- [ ] Status langsung ke Closed
- [ ] `started_at` tetap NULL (timer tidak dihitung)
- [ ] Audit trail mencatat `rejected_by_helpdesk`

---

## Task 6.4 — Reassign Tiket (In Progress → In Progress)

**Permission:** `ticket.reassign`
**Aktor:** Helpdesk
**Referensi:** Dokumentasi Bag. 6, baris "Reassign Tiket"

**Logika:**
```
1. Cek status tiket = 'in_progress'
2. Cek user punya permission 'ticket.reassign'
3. Update tickets.handler_id = Teknisi baru
4. Insert ticket_histories:
   - action = 'reassigned'
   - new_handler_id = Teknisi baru
   - from = 'in_progress', to = 'in_progress'
5. Kirim notifikasi ke Teknisi baru
```

**⚠️ Timer TIDAK di-pause saat reassign.**

**Acceptance Criteria:**
- [ ] Helpdesk bisa reassign ke Teknisi lain
- [ ] Handler lama kehilangan akses (handler_id berubah)
- [ ] Timer tetap jalan

---

## Task 6.5 — Kembalikan ke Helpdesk (In Progress → Verification)

**Permission:** `ticket.return`
**Aktor:** Teknisi
**Referensi:** Dokumentasi Bag. 6, baris "Pengembalian ke Helpdesk"

**Logika:**
```
1. Cek status tiket = 'in_progress'
2. Cek user punya permission 'ticket.return'
3. Cek user = handler_id (hanya handler aktif yg bisa return)
4. Validasi: note (alasan) wajib diisi
5. Update tickets:
   - status = 'verification'
   - handler_id = NULL
6. Insert ticket_histories:
   - action = 'returned_to_helpdesk'
   - note = alasan (wajib)
7. Kirim notifikasi ke semua Helpdesk
```

**Acceptance Criteria:**
- [ ] Teknisi bisa kembalikan tiket ke Helpdesk
- [ ] Alasan wajib diisi
- [ ] `handler_id` di-reset ke NULL
- [ ] Status kembali ke Verification

---

## Task 6.6 — Tandai Selesai (In Progress → Resolved)

**Permission:** `ticket.resolve`
**Aktor:** Helpdesk atau Teknisi (handler aktif)
**Referensi:** Dokumentasi Bag. 6, baris "In Progress → Resolved"

**Logika:**
```
1. Cek status tiket = 'in_progress'
2. Cek user punya permission 'ticket.resolve'
3. Cek user = handler_id
4. Update tickets:
   - status = 'resolved'
   - resolved_at = NOW()
   - auto_close_at = NOW() + 72 jam
5. Insert ticket_histories (action = 'resolved')
6. Kirim notifikasi ke pelapor
```

**Acceptance Criteria:**
- [ ] Handler bisa tandai selesai
- [ ] `resolved_at` dan `auto_close_at` terisi
- [ ] Pelapor dapat notifikasi

---

## Task 6.7 — Tutup Tiket (Resolved → Closed)

**Permission:** `ticket.close` (kontekstual: hanya pelapor)
**Aktor:** Pelapor
**Referensi:** Dokumentasi Bag. 6, baris "Resolved → Closed (pelapor)"

**Logika:**
```
1. Cek status tiket = 'resolved'
2. Cek user punya permission 'ticket.close'
3. Cek user.id = tiket.reporter_id (WAJIB — hanya pelapor)
4. Update tickets:
   - status = 'closed'
   - closed_at = NOW()
5. Insert ticket_histories (action = 'closed')
6. Kirim notifikasi ke handler
```

**Acceptance Criteria:**
- [ ] Hanya pelapor yang bisa tutup tiket
- [ ] User lain dengan permission `ticket.close` tapi bukan pelapor → 403
- [ ] `closed_at` terisi

---

## Task 6.8 — ReOpen Tiket (Resolved → In Progress)

**Permission:** `ticket.reopen` (kontekstual: hanya pelapor)
**Aktor:** Pelapor
**Referensi:** Dokumentasi Bag. 6 + Bag. 8 (Guard validasi ReOpen)

**Logika:**
```
1. Cek status tiket = 'resolved'
2. Cek user.id = tiket.reporter_id
3. SAFETY GUARD: Cek tidak ada ticket_time_logs dengan resumed_at = NULL
4. Update tickets:
   - status = 'in_progress'
   - resolved_at = NULL
   - auto_close_at = NULL
   - (handler_id TIDAK berubah — kembali ke handler terakhir)
   - (started_at TIDAK berubah)
5. Insert ticket_histories (action = 'reopened')
6. Kirim notifikasi ke handler
```

**Acceptance Criteria:**
- [ ] Pelapor bisa reopen tiket Resolved
- [ ] Kembali ke handler yang sama
- [ ] `resolved_at` dan `auto_close_at` di-reset NULL
- [ ] `started_at` TIDAK berubah (timer lanjut)
- [ ] Safety guard: jika ada time_log tanpa resume → tolak reopen

---

## Task 6.9 — Update Progress

**Permission:** `ticket.update-progress`
**Aktor:** Helpdesk atau Teknisi (handler aktif)

**Apa yang dilakukan:**
Handler bisa menambahkan catatan progress ke tiket. Ini sebenarnya adalah menambah komentar biasa (type = `comment`) di thread tiket.

**Implementasi:** Cukup gunakan fitur komentar dari Fase 7 (Task 7.1). Task ini hanya memastikan handler punya akses.

**Acceptance Criteria:**
- [ ] Handler bisa menambahkan komentar progress saat tiket In Progress
