# Planning Implementasi Task 05 - Ticket Core

> Berdasarkan: `tasks/05-ticket-core.md`, `tasks/00-overview.md`, dan `Dokumentasi Aplikasi Helpdesk Tiketing.md`  
> Stack: Laravel 12 + Vue 3 + Inertia.js + Tailwind v4 + MySQL + Spatie Permission  
> Target fase: Fase 5 (Buat tiket, list tiket, detail tiket)

Dokumen ini disusun agar bisa dipakai programmer junior atau model AI berbiaya rendah untuk implementasi bertahap dengan risiko minim regresi.

## 0. Tujuan dan Batasan

### Tujuan fase 5
- Implementasi pembuatan tiket (create + store + upload attachment saat submit).
- Implementasi daftar tiket dengan scope visibilitas per role + filter + search + pagination.
- Implementasi halaman detail tiket (header tiket, deskripsi, attachment, thread komentar, audit trail kondisional).

### Out of scope (jangan dikerjakan di fase 5)
- Transisi workflow lanjutan (verify, assign, reassign, resolve, reopen, approval) -> Fase 6+.
- Komentar baru/aksi klarifikasi -> Fase 7.
- Notifikasi real async -> Fase 9 (hanya sisakan TODO/hook).

## 1. Audit Kondisi Codebase Saat Ini

Komponen fondasi sudah tersedia:
- Model: `Ticket`, `TicketAttachment`, `TicketHistory`, `TicketComment`, `TicketPriority`, `TicketCategory`, dst.
- Enum: `TicketStatus`, `HistoryAction`, `CommentType`.
- Service: `TicketNumberGenerator`, `TicketVisibilityScope`, `TicketDurationService`.
- Migration tabel tiket sudah ada (`tickets`, `ticket_counters`, `ticket_attachments`, `ticket_histories`, dst).

Gap yang belum ada:
- `TicketController` (method `create`, `store`, `index`, `show`).
- Halaman frontend ticket (`resources/js/Pages/Ticket/Create.vue`, `Index.vue`, `Show.vue`).
- Form request khusus tiket.
- Route ticket.
- Endpoint download attachment.
- Test otomatis untuk fitur ticket core.

Catatan penting struktur data saat ini:
- Scope Ketua Tim Kerja memakai `users.work_unit_id` (bukan `team_members`, karena tabel ini sudah di-drop).

## 2. Deliverables Akhir

### File baru
- `app/Http/Controllers/TicketController.php`
- `app/Http/Requests/Ticket/StoreTicketRequest.php`
- `app/Http/Requests/Ticket/IndexTicketRequest.php` (opsional tapi direkomendasikan)
- `app/Http/Requests/Ticket/ShowTicketRequest.php` (opsional)
- `resources/js/Pages/Ticket/Create.vue`
- `resources/js/Pages/Ticket/Index.vue`
- `resources/js/Pages/Ticket/Show.vue`
- `tests/Feature/Ticket/CreateTicketTest.php`
- `tests/Feature/Ticket/TicketVisibilityTest.php`
- `tests/Feature/Ticket/TicketDetailTest.php`
- `tests/Unit/Services/TicketNumberGeneratorTest.php`

### File diubah
- `routes/web.php`
- `app/Services/TicketVisibilityScope.php` (jika perlu penyempurnaan minor)
- `resources/js/Components/Layout/Sidebar.vue` (opsional: route menu tiket ke halaman list ticket)

## 3. Urutan Implementasi (Milestone)

## Milestone 1 - Routing dan Guard Dasar

1. Tambahkan route ticket di dalam grup `auth`.
2. Gunakan middleware permission per endpoint:
- `GET /tickets` -> `permission:ticket.view`
- `GET /tickets/{ticket}` -> `permission:ticket.view`
- `GET /tickets/create` -> `permission:ticket.create`
- `POST /tickets` -> `permission:ticket.create`
- `GET /tickets/attachments/{attachment}` -> `permission:ticket.view` (download)
3. Gunakan nama route konsisten:
- `tickets.index`, `tickets.show`, `tickets.create`, `tickets.store`, `tickets.attachments.download`

Output milestone: route terdaftar, endpoint belum penuh namun sudah bisa dipanggil.

## Milestone 2 - Implementasi Create Ticket (Task 5.1 + 5.2)

Catatan:
- Service `TicketNumberGenerator` sudah ada di codebase, jadi fokus Task 5.1 adalah verifikasi perilaku + test, bukan menulis ulang dari nol.

### 2.1 Request validation
Buat `StoreTicketRequest`:
- `title`: required|string|max:255
- `description`: required|string
- `category_id`: required|exists:ticket_categories,id
- `priority_id`: required|exists:ticket_priorities,id
- `attachments`: nullable|array|max:5
- `attachments.*`: file|max:10240

Validasi tambahan extension blocked (application layer):
- tolak: `exe`, `bat`, `sh`, `msi`, `apk`

### 2.2 Controller `create`
- Ambil kategori aktif: `TicketCategory::active()->orderBy('name')`.
- Ambil prioritas aktif: `TicketPriority::active()->orderBy('level')`.
- Tentukan default prioritas "Medium" (by `level = 2`, fallback prioritas aktif pertama).
- Return inertia page `Ticket/Create`.

### 2.3 Controller `store` (wajib transactional)
Urutan dalam 1 transaction:
1. Generate nomor tiket pakai `TicketNumberGenerator`.
2. Insert `tickets` dengan status `TicketStatus::Open`.
3. Simpan attachment (jika ada) ke storage + insert `ticket_attachments` (`comment_id = null`).
4. Insert `ticket_histories` action `HistoryAction::Created` (`from_status = null`, `to_status = open`).
5. Sisakan hook TODO notifikasi (jangan implement queue penuh sekarang).

Catatan robust:
- Simpan list file path yang sukses diupload.
- Jika exception setelah upload, hapus file yang terlanjur tersimpan (cleanup di `catch`).
- Jangan pernah pakai string literal status/action; wajib enum.

### 2.4 Frontend `Ticket/Create.vue`
- Form fields sesuai task.
- Multi-file upload UI + validasi client-side sederhana (jumlah file, ukuran).
- Error handling per field + loading state submit.
- Setelah sukses: redirect ke `tickets.show` atau `tickets.index` (pilih salah satu dan konsisten).

Output milestone: user dengan `ticket.create` bisa buat tiket valid + history created tercatat.

## Milestone 3 - Implementasi Daftar Tiket (Task 5.3)

### 3.1 Query dan scope visibilitas
Di `TicketController@index`:
- Base query:
  - eager load: `reporter`, `handler`, `category`, `priority`
  - default order: terbaru (`latest('created_at')`)
- Terapkan `TicketVisibilityScope::apply($query, auth()->user())`.
- Filter query params:
  - `search` (ticket_number / title)
  - `status`
  - `category_id`
  - `priority_id`
- Pagination 10 + `withQueryString()`.

### 3.2 Data untuk filter
- Kirim list status dari enum `TicketStatus::cases()`.
- Kirim kategori dan prioritas (disarankan aktif + masih relevan di tiket existing).

### 3.3 Frontend `Ticket/Index.vue`
- Tabel kolom: Nomor, Judul, Pelapor, Handler, Kategori, Prioritas, Status, Tanggal.
- Search bar + filter status/kategori/prioritas.
- Badge warna status/prioritas.
- Pagination komponen existing.
- Link baris ke detail tiket.

Output milestone: semua role melihat tiket sesuai scope masing-masing.

## Milestone 4 - Implementasi Detail Tiket (Task 5.4)

### 4.1 Access guard show
Di `show(Ticket $ticket)`:
- Tetap cek visibility scope terhadap ticket terpilih.
- Jika tiket tidak termasuk scope user -> 403.

Implementasi aman:
- Cek dengan query scoped:
  - buat query `Ticket::query()->whereKey($ticket->id)`, lalu panggil `TicketVisibilityScope::apply($query, $user)`, lalu `exists()`.

### 4.2 Data detail
Load relasi:
- Header: reporter, handler, category, priority.
- Attachment submit awal: `attachments` dengan `comment_id = null`.
- Komentar: order by `created_at asc`, include `user.roles`, `attachments`.
- Histories: order by `created_at asc`, include `actor` (dan `newHandler` jika perlu).

### 4.3 Audit trail conditional
- Hanya kirim/tampilkan timeline audit jika user punya permission `ticket.view-audit-trail`.
- Jika tidak punya permission, jangan kirim data histories yang tidak dibutuhkan.

### 4.4 Download attachment
- Tambahkan endpoint download.
- Validasi attachment belongs to ticket yang visible untuk user.
- Gunakan response download dari storage disk.

### 4.5 Frontend `Ticket/Show.vue`
- Header tiket lengkap + badge.
- Section deskripsi dan attachment awal.
- Section thread komentar dengan type badge (`comment`, `clarification`, `clarification_reply`).
- Relative time: bisa dari backend `diffForHumans()` agar frontend sederhana.
- Placeholder area action buttons untuk fase berikutnya (tanpa implement action workflow).

Output milestone: detail tiket lengkap, secure, dan siap dikembangkan untuk workflow fase 6.

## Milestone 5 - Testing dan QA

### 5.1 Unit test
`TicketNumberGeneratorTest`:
- generate urut per hari.
- reset nomor di hari berbeda.
- no duplicate pada banyak pemanggilan berurutan.

### 5.2 Feature test - create
- user berizin `ticket.create` berhasil create ticket.
- record `ticket_histories` action `created` terinsert.
- attachment tersimpan + row `ticket_attachments` benar.
- user tanpa `ticket.create` -> 403.

### 5.3 Feature test - visibility
Sediakan fixture role:
- pegawai hanya tiket reporter_id sendiri.
- ketua_tim_kerja melihat tiket 1 unit kerja (`work_unit_id` sama).
- teknisi hanya handler_id sendiri.
- helpdesk/manager_it/super_admin melihat semua.

### 5.4 Feature test - detail dan audit
- user tanpa akses ke ticket tertentu -> 403 pada show.
- audit trail hanya muncul saat punya `ticket.view-audit-trail`.

### 5.5 Manual QA checklist
- Buat tiket dengan/without attachment.
- Coba upload extension terblokir.
- Uji filter/search/pagination daftar tiket.
- Uji direct URL detail tiket milik user lain.
- Uji download attachment.

## 4. Kontrak Data Backend -> Frontend (Agar Implementasi Konsisten)

### `tickets.index`
Kirim props minimal:
- `tickets` (paginated)
- `filters` (search/status/category_id/priority_id)
- `statusOptions`
- `categories`
- `priorities`

### `tickets.show`
Kirim props minimal:
- `ticket` (header + relasi)
- `initialAttachments`
- `comments`
- `canViewAuditTrail` (boolean)
- `histories` (hanya jika `canViewAuditTrail = true`)

### `tickets.create`
Kirim props minimal:
- `categories`
- `priorities`
- `defaultPriorityId`

## 5. Risiko Implementasi dan Mitigasi

- Risiko: file upload tersimpan tapi transaksi DB gagal.
  - Mitigasi: cleanup file path pada `catch`.
- Risiko: bypass akses via URL detail/download.
  - Mitigasi: re-check scope visibilitas di endpoint show/download.
- Risiko: ketua_tim_kerja scope salah karena ikut dokumen lama `team_members`.
  - Mitigasi: gunakan `users.work_unit_id` sesuai struktur DB aktual.
- Risiko: enum tidak konsisten.
  - Mitigasi: semua status/action wajib dari enum (`TicketStatus`, `HistoryAction`).

## 6. Definition of Done Fase 5

- Semua acceptance criteria di `tasks/05-ticket-core.md` terpenuhi.
- Tidak ada error otorisasi/scope pada create, list, show, download.
- Test minimal unit+feature utama lulus.
- Tidak ada penggunaan string literal status/action di logic inti.
- Dokumen phase 5 dapat dilanjutkan ke phase 6 tanpa refactor besar.
