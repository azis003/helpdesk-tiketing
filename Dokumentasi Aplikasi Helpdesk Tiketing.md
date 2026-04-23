# Dokumentasi Aplikasi Helpdesk Tiketing

> **Versi Brief:** 6.0 (Revisi pasca-review keenam)
> **Tanggal Update:** 2026-04-23
> **Catatan:** Dokumen ini sudah melewati 6x review. Perbaikan di versi 6.0 meliputi: penambahan mekanisme reject tiket saat verifikasi (Verification → Closed), penambahan timeout dan auto-close untuk status Waiting for Info (reminder 3 hari + auto-close 14 hari), penambahan tabel `ticket_counters` untuk generate nomor tiket yang atomic, penambahan `updated_at` di tabel `ticket_time_logs` dan `ticket_approvals`, klarifikasi self-handling Helpdesk, eksplisitasi trigger klarifikasi, catatan visibilitas Teknisi pasca-reassign, tabel mapping action↔notification type, catatan PHP Enum untuk konsistensi status, dan catatan perilaku kategori/prioritas non-aktif. Total tabel: 18 (13 custom + 5 Spatie).

---

## 1. Deskripsi Project

Aplikasi berbasis website untuk manajemen tiket kendala TI yang ada di BP2KOMDIGI. Saat ini ketika ada kendala TI di BP2KOMDIGI, pegawai langsung chat personal melalui WhatsApp ke nomor helpdesk, setelah itu helpdesk mencatat semua laporan kendala TI ke dalam sebuah Google Sheet kendala TI. Aplikasi ini menggantikan alur manual tersebut menjadi sistem tiket yang terstruktur.

---

## 2. Roles

| No | Role | Keterangan |
|----|------|------------|
| 1 | Super Admin | Mengelola master data dan konfigurasi sistem. Tidak terlibat dalam operasional tiket |
| 2 | Pegawai | Pelapor utama kendala TI |
| 3 | Ketua Tim Kerja | Pelapor + bisa melihat tiket seluruh anggota timnya |
| 4 | Helpdesk (Tier 1) | Menerima, memverifikasi, dan menangani tiket |
| 5 | Teknisi (Tier 2) | Menangani tiket teknis yang di-assign oleh Helpdesk |
| 6 | Manager IT | Menyetujui/menolak request approval untuk kasus khusus |

---

## 3. Stack Teknologi

| No | Teknologi | Kegunaan |
|----|-----------|----------|
| 1 | Laravel 12 | Backend framework |
| 2 | Vue.js 3 (Composition API) + Inertia.js | Frontend SPA monolith |
| 3 | Tailwind CSS v4 | Styling |
| 4 | SweetAlert2 | Dialog/alert UI |
| 5 | MySQL | Database |
| 6 | Laravel Spatie Permission | Manajemen role & permission |
| 7 | Laravel Scheduler (built-in) | Menjalankan job terjadwal (auto-close tiket) |
| 8 | Laravel Queue | Menjalankan job async (kirim notifikasi) |

> **Catatan:** Laravel Sanctum tidak digunakan di fase ini. Autentikasi menggunakan session-based auth bawaan Laravel + Inertia.js. Sanctum akan dipertimbangkan nanti jika ada kebutuhan API untuk integrasi WhatsApp atau mobile app.

---

## 4. Fitur dan Matriks Aplikasi

> **Cara baca tabel:** **X** = punya akses, **-** = tidak punya akses.
> **Penting:** Fitur "Tutup tiket" dan "Re Open Tiket" berlaku untuk **Pelapor** (siapapun yang membuat tiket tersebut), bukan hanya role Pegawai.

| Fitur | Super Admin | Pegawai | Ketua Tim Kerja | Helpdesk | Teknisi | Manager IT |
| --- | --- | --- | --- | --- | --- | --- |
| **Manajemen Tiket** | | | | | | |
| Buat Tiket | **-** | **X** | **X** | **X** | **-** | **-** |
| Lihat daftar tiket | **X** | **X** | **X** | **X** | **X** | **X** |
| Lihat detail tiket | **X** | **X** | **X** | **X** | **X** | **X** |
| Tutup tiket (Closed) — hanya pelapor tiket tsb | **-** | **X** | **X** | **X** | **-** | **-** |
| Re Open Tiket — hanya pelapor tiket tsb | **-** | **X** | **X** | **X** | **-** | **-** |
| **Verifikasi & Assignment** | | | | | | |
| Verifikasi tiket | **-** | **-** | **-** | **X** | **-** | **-** |
| Ubah prioritas tiket | **-** | **-** | **-** | **X** | **-** | **-** |
| Ubah kategori tiket | **-** | **-** | **-** | **X** | **-** | **-** |
| Reject tiket saat verifikasi (invalid/duplikat) | **-** | **-** | **-** | **X** | **-** | **-** |
| Assign ke Teknisi | **-** | **-** | **-** | **X** | **-** | **-** |
| Reassign tiket | **-** | **-** | **-** | **X** | **-** | **-** |
| Kembalikan ke Helpdesk | **-** | **-** | **-** | **-** | **X** | **-** |
| **Pengerjaan Tiket** | | | | | | |
| Update progress tiket | **-** | **-** | **-** | **X** | **X** | **-** |
| Tandai selesai (Resolved) | **-** | **-** | **-** | **X** | **X** | **-** |
| Request approval Manager IT | **-** | **-** | **-** | **X** | **X** | **-** |
| Tandai butuh pihak ketiga | **-** | **-** | **-** | **X** | **X** | **-** |
| **Klarifikasi & Komunikasi** | | | | | | |
| Minta klarifikasi tiket | **-** | **-** | **-** | **X** | **X** | **-** |
| Balas klarifikasi tiket — hanya pelapor tiket tsb | **-** | **X** | **X** | **X** | **-** | **-** |
| Komentar di thread tiket | **-** | **X** | **X** | **X** | **X** | **X** |
| Upload attachment | **-** | **X** | **X** | **X** | **X** | **X** |
| **Approval Manager IT** | | | | | | |
| Approve / Reject tiket | **-** | **-** | **-** | **-** | **-** | **X** |
| **Dashboard & Laporan** | | | | | | |
| Dashboard personal | **-** | **X** | **X** | **X** | **X** | **-** |
| Dashboard tim | **-** | **-** | **X** | **-** | **-** | **-** |
| Dashboard operasional | **X** | **-** | **-** | **X** | **-** | **X** |
| Laporan & export | **X** | **-** | **-** | **-** | **-** | **X** |
| Laporan tiket pribadi | **-** | **X** | **X** | **X** | **X** | **X** |
| **Audit Trail** | | | | | | |
| Lihat riwayat tiket (Audit Trail) | **X** | **-** | **-** | **X** | **X** | **-** |
| **Master Data & Konfigurasi** | | | | | | |
| Kategori tiket | **X** | **-** | **-** | **-** | **-** | **-** |
| Prioritas | **X** | **-** | **-** | **-** | **-** | **-** |
| Unit Kerja | **X** | **-** | **-** | **-** | **-** | **-** |
| User & role | **X** | **-** | **-** | **-** | **-** | **-** |
| Permission per role | **X** | **-** | **-** | **-** | **-** | **-** |
| **Management Profile** | | | | | | |
| Lihat/Edit Profile | **X** | **X** | **X** | **X** | **X** | **X** |
| Edit Password | **X** | **X** | **X** | **X** | **X** | **X** |

### Catatan Penting Matriks

1. **Tutup tiket & Re Open** bersifat **kontekstual terhadap pelapor**: siapapun role-nya, jika dia yang membuat tiket tersebut (`reporter_id`), maka dia bisa menutup dan membuka ulang tiketnya sendiri. Ini berlaku untuk Pegawai, Ketua Tim Kerja, dan Helpdesk.
2. **Balas klarifikasi** juga bersifat **kontekstual terhadap pelapor**: siapapun role-nya, jika dia adalah pelapor tiket tersebut (`reporter_id`) dan tiket sedang berstatus `waiting_for_info`, maka dia bisa membalas klarifikasi. Ini berlaku untuk Pegawai, Ketua Tim Kerja, dan Helpdesk. **Teknisi tidak bisa balas klarifikasi** karena Teknisi tidak bisa membuat tiket (`ticket.create` tidak dimiliki), sehingga Teknisi tidak pernah menjadi `reporter_id`.
3. **Laporan tiket pribadi** untuk **Manager IT**: berisi daftar tiket yang pernah mereka approve/reject, bukan tiket yang mereka buat.
4. **Laporan tiket pribadi** untuk **Teknisi**: berisi daftar tiket yang pernah di-assign dan mereka kerjakan. **Penting untuk implementor:** query laporan pribadi Teknisi harus menggunakan tabel `ticket_histories` (cari action `assigned` atau `reassigned`), **bukan** `tickets.handler_id`, karena `handler_id` berubah saat tiket di-reassign.
5. **Super Admin** bisa melihat daftar tiket dan detail tiket untuk keperluan dashboard operasional, audit trail, dan laporan. Super Admin **tidak bisa** melakukan aksi apapun terhadap tiket (tidak bisa verifikasi, assign, komentar, atau handle tiket).
6. **Komentar bersifat immutable**: Setelah dikirim, komentar tidak bisa diedit atau dihapus oleh siapapun.
7. **Self-handling oleh Helpdesk diperbolehkan**: Helpdesk yang membuat tiket (`reporter_id`) boleh juga memverifikasi dan menangani tiket tersebut sendiri (`handler_id` = `reporter_id`). Ini diperbolehkan karena ukuran tim IT yang terbatas. Jika di kemudian hari dibutuhkan segregation of duties, tambahkan validasi `reporter_id != current_user.id` pada aksi verifikasi.

---

## 5. Tabel Status

| Status | ENUM Value | Deskripsi |
| --- | --- | --- |
| **Open** | `open` | Tiket baru dibuat pelapor, belum ditangani Helpdesk |
| **Verification** | `verification` | Helpdesk memverifikasi kelengkapan tiket. Di tahap ini: set/ubah prioritas jika perlu, ubah kategori jika perlu, lalu handle sendiri atau assign ke Teknisi |
| **In Progress** | `in_progress` | Tiket aktif dikerjakan Helpdesk (tier 1) atau Teknisi (tier 2) |
| **Waiting for Info** | `waiting_for_info` | Helpdesk/Teknisi butuh klarifikasi dari pelapor. Timer pengerjaan di-**pause** sampai pelapor menjawab |
| **Waiting Third Party** | `waiting_third_party` | Pengerjaan tertahan menunggu vendor atau pihak ketiga. Timer pengerjaan di-**pause** |
| **Pending Approval** | `pending_approval` | Tiket butuh persetujuan Manager IT sebelum pengerjaan dilanjutkan |
| **Resolved** | `resolved` | Selesai dikerjakan, menunggu konfirmasi pelapor. Pelapor bisa ReOpen (kembali ke In Progress ke handler yang sama). **Auto-close dalam 3×24 jam (72 jam kalender)** jika tidak ada respons |
| **Closed** | `closed` | Tiket ditutup permanen. **Tidak bisa di-ReOpen**. Terjadi karena: pelapor konfirmasi, auto-close sistem 3 hari, otomatis setelah Rejected, ditolak Helpdesk saat verifikasi, atau auto-close 14 hari tanpa jawaban klarifikasi |
| **Rejected** | `rejected` | Ditolak Manager IT. Alasan penolakan wajib diisi. Otomatis pindah ke Closed. Pelapor mendapat notifikasi beserta alasan |

---

## 6. Tabel Transisi Status

> **Cara baca:** Setiap baris menunjukkan perpindahan status dari→ke, apa yang memicu perpindahan (trigger), siapa yang melakukan (aktor), dan catatan tambahan.

| Dari | Ke | Trigger | Aktor | Catatan |
| --- | --- | --- | --- | --- |
| **— Alur Utama** | | | | |
| — | Open | Submit tiket baru | Pegawai, Ketua Tim Kerja, atau Helpdesk | Tiket masuk sistem. Pelapor wajib memilih kategori dan prioritas awal |
| Open | Verification | Helpdesk klik "Verifikasi tiket" | Helpdesk | Helpdesk boleh ubah prioritas dan/atau kategori di tahap ini |
| Verification | In Progress | Helpdesk handle sendiri | Helpdesk | Tier 1 mulai mengerjakan. `handler_id` = Helpdesk. `started_at` diisi saat pertama kali masuk In Progress |
| Verification | In Progress | Helpdesk assign ke Teknisi | Helpdesk | Tier 2 mulai mengerjakan. `handler_id` = Teknisi yang di-assign. `started_at` diisi saat pertama kali masuk In Progress |
| In Progress | Resolved | Handler tandai selesai | Helpdesk atau Teknisi | Status → Resolved. `resolved_at` diisi. `auto_close_at` = resolved_at + 72 jam. Menunggu konfirmasi pelapor |
| Resolved | Closed | Pelapor konfirmasi selesai | Pelapor (Pegawai/Ketua Tim/Helpdesk) | Klik tombol "Tutup Tiket". `closed_at` diisi |
| Resolved | Closed | Tidak ada respons selama 3×24 jam | Sistem (Scheduler) | Auto-close. `closed_at` diisi. Action di audit trail = `auto_closed` |
| **— Reassign Tiket** | | | | |
| In Progress | In Progress | Helpdesk reassign ke Teknisi lain | Helpdesk | `handler_id` diupdate ke Teknisi baru. Timer tetap jalan (tidak pause). Action di audit trail = `reassigned` |
| **— Pengembalian ke Helpdesk** | | | | |
| In Progress | Verification | Teknisi kembalikan ke Helpdesk | Teknisi | Alasan wajib diisi. **`handler_id` di-reset ke NULL** (tiket menunggu Helpdesk untuk memproses ulang, Teknisi sudah tidak bertanggung jawab). Action di audit trail = `returned_to_helpdesk` |
| **— Reject Tiket (Invalid/Duplikat)** | | | | |
| Verification | Closed | Helpdesk menolak tiket (invalid/duplikat/bukan kendala TI) | Helpdesk | Alasan wajib diisi. `closed_at` diisi. `started_at` tetap NULL (belum pernah In Progress, timer tidak dihitung). Action = `rejected_by_helpdesk`. Pelapor mendapat notifikasi beserta alasan. **Catatan:** permission yang digunakan tetap `ticket.verify` karena reject adalah bagian dari proses verifikasi |
| **— Re Open Tiket** | | | | |
| Resolved | In Progress | Pelapor ReOpen tiket | Pelapor (Pegawai/Ketua Tim/Helpdesk) | Kembali ke handler terakhir (`handler_id` tidak berubah). `resolved_at` di-reset NULL. `auto_close_at` di-reset NULL. Action = `reopened` |
| **— Klarifikasi ke Pelapor** | | | | |
| In Progress | Waiting for Info | Handler butuh info tambahan dari pelapor | Helpdesk atau Teknisi | Timer di-**pause**. Insert `ticket_time_logs` (pause_reason = `waiting_for_info`). Action = `paused`. **Trigger implementasi:** Handler mengisi form klarifikasi yang akan insert `ticket_comments` (type = `clarification`, body = pertanyaan handler), lalu ubah status tiket ke `waiting_for_info` |
| Waiting for Info | In Progress | Pelapor menjawab pertanyaan | Pelapor (Pegawai/Ketua Tim/Helpdesk) | Timer **resume**. Update `ticket_time_logs.resumed_at`, hitung `duration_seconds`, tambahkan ke `tickets.total_paused_seconds`. Action = `resumed`. **Trigger implementasi:** pelapor mengirim komentar dengan `type = 'clarification_reply'` saat status tiket = `waiting_for_info` |
| Waiting for Info | Closed | Pelapor tidak menjawab selama 14 hari | Sistem (Scheduler) | Timer di-resume lalu langsung close. Update `ticket_time_logs.resumed_at`, hitung `duration_seconds`, set `closed_at = NOW()`. Action = `auto_closed_no_response`. Pelapor dan handler mendapat notifikasi |
| **— Pihak Ketiga** | | | | |
| In Progress | Waiting Third Party | Butuh intervensi vendor / pihak ketiga | Helpdesk atau Teknisi | Timer di-**pause**. Insert `ticket_time_logs` (pause_reason = `waiting_third_party`). Action = `paused` |
| Waiting Third Party | In Progress | Urusan dengan pihak ketiga selesai | Teknisi atau Helpdesk (klik manual) | Timer **resume**. Update `ticket_time_logs.resumed_at`, hitung `duration_seconds`, tambahkan ke `tickets.total_paused_seconds`. Action = `resumed` |
| **— Approval Manager IT** | | | | |
| In Progress | Pending Approval | Handler request approval Manager IT | Helpdesk atau Teknisi | Untuk kasus khusus saja. Insert `ticket_approvals`. Action = `approval_requested` |
| Pending Approval | In Progress | Manager IT menyetujui | Manager IT | Pengerjaan dilanjutkan. Update `ticket_approvals.status` = `approved`. Action = `approved` |
| Pending Approval | Rejected | Manager IT menolak | Manager IT | Alasan penolakan wajib diisi. Update `ticket_approvals.status` = `rejected`. Action = `rejected` |
| Rejected | Closed | Otomatis setelah ditolak | Sistem | `closed_at` diisi. Action = `rejected_closed`. Pelapor dapat notifikasi + alasan penolakan. **Implementasi:** kedua transisi (Pending Approval → Rejected → Closed) dilakukan dalam **satu database transaction**. Insert **2 record** `ticket_histories`: (1) action = `rejected`, (2) action = `rejected_closed`. Status akhir tiket langsung = `closed` |

---

## 7. Tabel Scope Visibilitas Tiket

> **Tabel ini menjelaskan tiket mana saja yang bisa DILIHAT oleh masing-masing role.** Permission untuk melakukan aksi (tutup, edit, dll) tetap ditentukan oleh matriks fitur di bagian 4.

| Role | Bisa lihat tiket milik siapa |
| --- | --- |
| Pegawai | Hanya tiket milik sendiri (`reporter_id` = user login) |
| Ketua Tim Kerja | Tiket milik sendiri + seluruh anggota timnya (via tabel `team_members`) |
| Helpdesk | Semua tiket yang masuk ke sistem |
| Teknisi | Hanya tiket yang di-assign ke dirinya (`handler_id` = user login) |
| Manager IT | Semua tiket yang masuk ke sistem |
| Super Admin | Semua tiket yang masuk ke sistem |

> **Catatan visibilitas Teknisi setelah reassign:** Setelah tiket di-reassign, Teknisi lama langsung kehilangan akses karena `handler_id` sudah berubah. Ini *by-design* — Teknisi hanya bertanggung jawab atas tiket yang aktif di-assign ke dirinya. Namun, di **Laporan Tiket Pribadi** (bagian 15.5), Teknisi tetap bisa melihat histori semua tiket yang pernah di-assign ke dirinya (via `ticket_histories`).

---

## 8. Waktu Pengerjaan

Sistem mencatat waktu pengerjaan tiket mulai dari status **In Progress** hingga **Closed**. Timer di-pause saat status **Waiting for Info** dan **Waiting Third Party**. Ini **bukan SLA** dengan batas waktu, hanya pencatatan durasi pengerjaan untuk keperluan monitoring dan laporan.

- **Format tampilan:** X hari Y jam Z menit
- **Ditampilkan di:** halaman detail tiket dan halaman laporan
- **Field yang digunakan di database:**
  - `tickets.started_at` — timestamp pertama kali tiket masuk In Progress
  - `tickets.total_paused_seconds` — akumulasi total detik pause (diupdate setiap resume)
  - `tickets.closed_at` — timestamp tiket ditutup
  - Detail per-pause disimpan di tabel `ticket_time_logs`

**Rumus kalkulasi durasi efektif:**
```
durasi_efektif_detik = (closed_at - started_at) - total_paused_seconds
Lalu konversi ke format: X hari Y jam Z menit
```

**Perilaku timer saat ReOpen:**
- Jika tiket di-ReOpen dari Resolved → In Progress, timer melanjutkan dari posisi terakhir (bukan reset ke 0). `started_at` tidak berubah.
- `resolved_at` dan `auto_close_at` di-reset ke NULL.

**Guard validasi ReOpen (safety check):**
- Sebelum memproses ReOpen, pastikan **tidak ada** record di `ticket_time_logs` yang `resumed_at = NULL` (masih dalam kondisi pause). Jika ada, tolak aksi ReOpen karena data tidak konsisten.
- Secara logika bisnis, tiket tidak bisa masuk Resolved kalau masih pause, jadi kondisi ini seharusnya tidak terjadi — validasi ini bersifat **safety guard** untuk mencegah data korup.

**Perilaku timer saat Pending Approval:**
- Timer **tetap berjalan** saat status `Pending Approval` (tidak di-pause). Waktu menunggu approval Manager IT tetap dihitung sebagai durasi pengerjaan karena handler sudah selesai mengerjakan tugasnya.
- Hanya status `Waiting for Info` dan `Waiting Third Party` yang mem-pause timer (karena pengerjaan benar-benar tertahan oleh pihak lain).

---

## 9. Autentikasi

- Login menggunakan **username & password** (bukan email)
- Hashing password dengan **bcrypt**
- **Rate limiting** pada endpoint login (contoh: maks 5 percobaan per menit)
- User dibuat oleh **Super Admin** saja (tidak ada fitur registrasi mandiri)
- Semua role memiliki fitur "Lihat/Edit profil sendiri" dan "Edit Password"
- Autentikasi menggunakan **session-based auth** bawaan Laravel (bukan API token)

**Perilaku user nonaktif (`is_active = 0`):**
- User dengan `is_active = 0` **tidak bisa login**. Cek kolom `is_active` di Login Controller sebelum mengizinkan login.
- Jika user sedang login lalu di-nonaktifkan oleh Super Admin, **session dihentikan** pada request berikutnya (tambahkan middleware yang cek `is_active` di setiap request).
- **Sebelum menonaktifkan user**, Super Admin harus memastikan user tersebut tidak memiliki tiket aktif yang sedang dikerjakan (status `in_progress`, `waiting_for_info`, `waiting_third_party`, atau `pending_approval`). Jika ada, reassign tiket terlebih dahulu.
- User yang `is_active = 0` **tidak bisa dihapus dari database** jika masih punya tiket terkait (karena FK constraint `RESTRICT` pada `reporter_id` dan `uploaded_by`).

---

## 10. Notifikasi (In-App)

- Fitur **Inbox Notifikasi** dengan status read/unread
- Notifikasi disimpan di tabel `notifications` (lihat ERD bagian 14)
- Pengiriman notifikasi diproses via **Laravel Queue** agar tidak memblok request utama
- Integrasi notifikasi ke WhatsApp di **fase berikutnya** (tidak termasuk scope saat ini)

### Tabel Trigger Notifikasi

| Event | Penerima Notif |
| --- | --- |
| Tiket baru dibuat | Semua user dengan role Helpdesk |
| Tiket di-assign ke Teknisi | Teknisi yang bersangkutan |
| Tiket di-reassign ke Teknisi lain | Teknisi baru yang di-assign |
| Tiket dikembalikan ke Helpdesk | Semua user dengan role Helpdesk |
| Ada klarifikasi masuk (Waiting for Info) | Pelapor tiket (`reporter_id`) |
| Pelapor menjawab klarifikasi | Handler aktif (`handler_id`) |
| Tiket Resolved | Pelapor tiket (`reporter_id`) |
| Tiket ditutup oleh pelapor (Closed) | Handler aktif (`handler_id`) |
| Tiket di-ReOpen oleh pelapor | Handler aktif (`handler_id`) |
| Request approval dikirim | Semua user dengan role Manager IT |
| Tiket Approved oleh Manager IT | Handler yang mengajukan request (`ticket_approvals.requested_by`) |
| Tiket Rejected oleh Manager IT | Pelapor (`reporter_id`) + Handler yang request (`ticket_approvals.requested_by`) |
| Tiket auto-closed sistem (3 hari) | Pelapor tiket (`reporter_id`) |
| Tiket masuk Waiting Third Party | Pelapor tiket (`reporter_id`) |
| Tiket ditolak Helpdesk saat verifikasi | Pelapor tiket (`reporter_id`) |
| Reminder klarifikasi (3 hari tanpa jawaban) | Pelapor tiket (`reporter_id`) |
| Tiket auto-closed (14 hari tanpa jawaban klarifikasi) | Pelapor tiket (`reporter_id`) + Handler aktif (`handler_id`) |
| Ada komentar baru di tiket | Semua user yang pernah berkomentar di tiket tersebut + pelapor + handler aktif, **kecuali user yang menulis komentar itu sendiri** (jangan kirim notifikasi ke diri sendiri) |

### Mapping ticket_histories.action → notifications.type

> Tabel referensi silang untuk memudahkan implementor memahami hubungan antara action di audit trail dengan type notifikasi yang harus dikirim.

| History Action | Notification Type | Penerima |
|---|---|---|
| `created` | `ticket_created` | Semua Helpdesk |
| `verified` | *(tidak ada notifikasi)* | — |
| `assigned` | `ticket_assigned` | Teknisi target |
| `reassigned` | `ticket_reassigned` | Teknisi baru |
| `returned_to_helpdesk` | `ticket_returned` | Semua Helpdesk |
| `rejected_by_helpdesk` | `ticket_rejected_by_helpdesk` | Pelapor |
| `paused` (waiting_for_info) | `clarification_requested` | Pelapor |
| `paused` (waiting_third_party) | `ticket_waiting_third_party` | Pelapor |
| `resumed` (dari waiting_for_info) | `clarification_replied` | Handler |
| `resumed` (dari waiting_third_party) | *(tidak ada notifikasi)* | — |
| `resolved` | `ticket_resolved` | Pelapor |
| `closed` | `ticket_closed` | Handler |
| `auto_closed` | `ticket_auto_closed` | Pelapor |
| `auto_closed_no_response` | `ticket_auto_closed_no_response` | Pelapor + Handler |
| `reopened` | `ticket_reopened` | Handler |
| `approval_requested` | `approval_requested` | Semua Manager IT |
| `approved` | `ticket_approved` | Handler yang request |
| `rejected` + `rejected_closed` | `ticket_rejected` | Pelapor + Handler yang request |
| *(komentar baru)* | `comment_added` | Semua partisipan kecuali penulis |
| *(scheduler reminder)* | `ticket_reminder_waiting` | Pelapor |

---

## 11. Attachment

- **Tipe file yang diizinkan:** semua tipe kecuali executable (diblokir: `.exe`, `.bat`, `.sh`, `.msi`, `.apk`)
- **Ukuran maksimal:** 10 MB per file
- **Maksimal 5 file** per upload saat submit tiket baru
- **Maksimal 3 file** per upload saat menambahkan lampiran pada komentar
- Attachment bisa ditambahkan saat submit tiket (`comment_id` = NULL) atau saat input komentar (`comment_id` terisi)

---

## 12. Form Buat Tiket

> **Bagian ini menjelaskan field apa saja yang harus diisi pelapor saat membuat tiket baru.**

| Field | Wajib | Keterangan |
|-------|-------|------------|
| Judul (title) | Ya | Judul singkat kendala |
| Deskripsi (description) | Ya | Penjelasan detail kendala |
| Kategori (category_id) | Ya | Dipilih dari dropdown `ticket_categories` yang `is_active = 1` |
| Prioritas (priority_id) | Ya | Dipilih dari dropdown `ticket_priorities` yang `is_active = 1`. Default: Medium |
| Attachment | Tidak | Upload file pendukung (maks 5 file, maks 10 MB per file) |

> **Catatan:** Pelapor memilih kategori dan prioritas awal. Helpdesk bisa mengubah keduanya di tahap Verification jika dirasa kurang tepat.

> **Perilaku saat kategori/prioritas di-nonaktifkan (`is_active = 0`):**
> - Tiket existing yang sudah menggunakan kategori/prioritas tersebut **tetap menampilkan** nama kategori/prioritas lamanya di halaman detail dan laporan.
> - Kategori/prioritas non-aktif **tidak muncul** di dropdown saat: pelapor membuat tiket baru, atau Helpdesk mengubah kategori/prioritas saat verifikasi.
> - Kategori/prioritas **tidak bisa dihapus** jika masih digunakan oleh tiket (FK constraint RESTRICT pada `tickets.category_id` dan `tickets.priority_id`).

---

## 13. Nomor Tiket

- **Format:** `TKT-YYYYMMDD-XXXX` (contoh: `TKT-20260421-0001`)
- **Counter (`XXXX`):** Reset setiap hari mulai dari `0001`
- **Race condition handling:** Gunakan tabel `ticket_counters` (lihat ERD bagian 7b) dengan atomic increment:

```sql
-- Atomic increment: aman dari race condition tanpa SELECT ... FOR UPDATE
INSERT INTO ticket_counters (date, last_number) VALUES (CURDATE(), 1)
ON DUPLICATE KEY UPDATE last_number = last_number + 1;

-- Ambil nilai last_number yang baru
SELECT last_number FROM ticket_counters WHERE date = CURDATE();

-- Format: TKT-{YYYYMMDD}-{zero-padded 4 digit dari last_number}
```

> **Catatan:** Pendekatan `ON DUPLICATE KEY UPDATE` bersifat atomic di MySQL sehingga aman dari race condition.

---

## 14. Scheduler & Queue Jobs

### 14.1 Auto-Close Resolved Tickets (Scheduler)

| Item | Detail |
|------|--------|
| **Tujuan** | Menutup tiket yang sudah Resolved selama 3×24 jam (72 jam kalender) tanpa respons pelapor |
| **Job Class** | `AutoCloseResolvedTickets` |
| **Frekuensi** | Setiap jam (`->hourly()`) via `php artisan schedule:run`. **Catatan akurasi:** karena scheduler berjalan per jam, auto-close bisa terjadi antara 72–73 jam setelah resolved (toleransi ±1 jam). Ini bukan masalah karena bukan SLA ketat |
| **Query** | `SELECT * FROM tickets WHERE status = 'resolved' AND auto_close_at <= NOW()` |
| **Aksi** | Set `status = 'closed'`, set `closed_at = NOW()`, insert `ticket_histories` (action = `auto_closed`, actor_id = NULL), kirim notifikasi ke pelapor |
| **Idempotent** | Ya — tiket yang sudah closed tidak akan diproses ulang |

### 14.2 Notifikasi (Queue)

| Item | Detail |
|------|--------|
| **Tujuan** | Mengirim notifikasi in-app secara async agar tidak memblok request utama |
| **Queue Name** | `notifications` |
| **Driver** | Database (tabel `jobs` bawaan Laravel) |
| **Retry** | 3 kali, delay 60 detik antar retry |

### 14.3 Auto-Reminder Waiting for Info (Scheduler)

| Item | Detail |
|------|--------|
| **Tujuan** | Mengirim notifikasi pengingat ke pelapor jika tiket sudah 3 hari di status `waiting_for_info` tanpa jawaban |
| **Job Class** | `RemindWaitingForInfoTickets` |
| **Frekuensi** | Setiap hari pukul 08:00 (`->dailyAt('08:00')`) via `php artisan schedule:run` |
| **Query** | `SELECT * FROM tickets WHERE status = 'waiting_for_info' AND updated_at <= NOW() - INTERVAL 3 DAY` |
| **Aksi** | Kirim notifikasi reminder ke pelapor (`reporter_id`). **Tidak mengubah status tiket.** Untuk menghindari spam, cek selisih hari dari `ticket_time_logs.paused_at` — hanya kirim jika kelipatan 3 hari |
| **Idempotent** | Ya — menggunakan pengecekan waktu untuk menghindari pengiriman berulang di hari yang sama |

### 14.4 Auto-Close Stale Waiting for Info Tickets (Scheduler)

| Item | Detail |
|------|--------|
| **Tujuan** | Menutup tiket yang sudah 14 hari di status `waiting_for_info` tanpa jawaban pelapor |
| **Job Class** | `AutoCloseStaleWaitingTickets` |
| **Frekuensi** | Setiap hari pukul 09:00 (`->dailyAt('09:00')`) via `php artisan schedule:run` |
| **Query** | `SELECT * FROM tickets WHERE status = 'waiting_for_info' AND updated_at <= NOW() - INTERVAL 14 DAY` |
| **Aksi** | Resume timer (update `ticket_time_logs.resumed_at`, hitung `duration_seconds`, tambahkan ke `total_paused_seconds`), set `status = 'closed'`, set `closed_at = NOW()`, insert `ticket_histories` (action = `auto_closed_no_response`, actor_id = NULL), kirim notifikasi ke pelapor + handler |
| **Idempotent** | Ya — tiket yang sudah closed tidak akan diproses ulang |

---

## 15. Dashboard

### 15.1 Dashboard Personal (Pegawai, Ketua Tim, Helpdesk, Teknisi)

| Widget | Deskripsi |
|--------|-----------|
| Total tiket saya | Jumlah tiket berdasarkan scope role |
| Breakdown per status | Card/badge jumlah tiket per status (Open, In Progress, Resolved, dll) |
| Tiket terbaru | Daftar 5-10 tiket terbaru yang relevan |
| Tiket butuh perhatian | Tiket yang butuh aksi dari user (belum dijawab klarifikasi, belum ditutup, dll) |

### 15.2 Dashboard Tim (Ketua Tim Kerja)

| Widget | Deskripsi |
|--------|-----------|
| Total tiket tim | Jumlah tiket dari seluruh anggota tim |
| Breakdown per anggota | Jumlah tiket per anggota tim |
| Breakdown per status | Jumlah tiket tim per status |
| Tiket yang butuh perhatian | Tiket anggota tim yang butuh follow-up |

### 15.3 Dashboard Operasional (Super Admin, Helpdesk, Manager IT)

| Widget | Deskripsi |
|--------|-----------|
| Total tiket sistem | Jumlah seluruh tiket di sistem |
| Breakdown per status | Card jumlah tiket per status |
| Breakdown per kategori | Jumlah tiket per kategori |
| Breakdown per prioritas | Jumlah tiket per prioritas |
| Rata-rata waktu pengerjaan | Rata-rata durasi efektif tiket yang sudah Closed |
| Tiket yang di-assign per handler | Jumlah tiket yang sedang dikerjakan per Helpdesk/Teknisi |

### 15.4 Laporan & Export (Super Admin, Manager IT)

| Item | Detail |
|------|--------|
| Format export | Excel (.xlsx) |
| Filter | Rentang tanggal, status, kategori, prioritas, handler |
| Isi laporan | Daftar tiket dengan kolom: nomor tiket, judul, pelapor, handler, kategori, prioritas, status, durasi efektif, tanggal dibuat, tanggal ditutup |

### 15.5 Laporan Tiket Pribadi

| Role | Isi Laporan | Cara Query di Database |
|------|-------------|------------------------|
| Pegawai | Tiket-tiket yang pernah mereka buat (sebagai pelapor) | `tickets.reporter_id = user.id` |
| Ketua Tim Kerja | Tiket-tiket yang pernah mereka buat (sebagai pelapor) | `tickets.reporter_id = user.id` |
| Helpdesk | Tiket-tiket yang pernah mereka tangani (sebagai handler) | `ticket_histories` WHERE `action IN ('assigned', 'verified', 'reassigned')` AND `actor_id = user.id` |
| Teknisi | Tiket-tiket yang pernah di-assign ke mereka (sebagai handler) | `ticket_histories` WHERE `action IN ('assigned', 'reassigned')` AND `new_handler_id = user.id`. **Jangan** pakai `tickets.handler_id` karena nilainya berubah saat reassign. **Catatan:** kolom `new_handler_id` khusus dibuat untuk keperluan ini — lihat ERD tabel `ticket_histories` |
| Manager IT | Tiket-tiket yang pernah mereka approve/reject | `ticket_approvals.reviewed_by = user.id` |

---

# ERD — Helpdesk Tiketing BP2KOMDIGI

**Versi:** 6.0 (Revisi pasca-review keenam)
**Stack:** Laravel 12 + MySQL
**Total Tabel:** 18 (13 tabel custom + 5 tabel dari Spatie Permission)

---

## Diagram Relasi

```
work_units ────────────< team_members >──────────── users
                                                      │
                         ┌────────────────────────────┤
                         │                            │
                    reporter_id                  handler_id
                         │                            │
                         └──────────────┬─────────────┘
                                        │
       ticket_categories <── tickets ──> ticket_priorities
                                │
          ┌─────────────────────┼──────────────────────┐
          │                     │                      │
  ticket_time_logs      ticket_comments         ticket_approvals
          │              ticket_attachments
          │
  ticket_histories (time_log_id → ticket_time_logs)

notifications ──> users (user_id)
notifications ──> tickets (ticket_id)

roles ──< model_has_roles >── users   (Spatie Permission)
permissions ──< role_has_permissions >── roles   (Spatie Permission)
permissions ──< model_has_permissions >── users   (Spatie Permission — opsional)
```

---

## 1. work_units

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL | Nama unit kerja |
| code | VARCHAR(20) | NOT NULL, UNIQUE | Kode singkat |
| is_active | TINYINT(1) | NOT NULL, DEFAULT 1 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

---

## 2. users

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| username | VARCHAR(50) | NOT NULL, UNIQUE, INDEX | Digunakan untuk login |
| name | VARCHAR(100) | NOT NULL | Nama lengkap |
| email | VARCHAR(100) | NULL, UNIQUE | Opsional |
| password | VARCHAR(255) | NOT NULL | bcrypt |
| avatar | VARCHAR(255) | NULL | |
| work_unit_id | BIGINT UNSIGNED | NULL, FK → work_units.id SET NULL | Unit kerja administratif user (untuk display di profil). Nullable: SA/Helpdesk/Teknisi/Manager tidak wajib punya unit |
| is_active | TINYINT(1) | NOT NULL, DEFAULT 1 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Index:** `username`, `work_unit_id`

---

## 3. team_members

> Memetakan keanggotaan tim secara eksplisit agar scope visibilitas Ketua Tim Kerja bisa dilakukan dengan JOIN yang bersih.
>
> **Perbedaan dengan `users.work_unit_id`:**
> - `users.work_unit_id` = unit kerja administratif (untuk informasi profil)
> - `team_members` = keanggotaan tim untuk menentukan scope visibilitas Ketua Tim Kerja
> - Saat menambah user ke unit kerja, isi KEDUA kolom ini (set `work_unit_id` DAN insert ke `team_members`)

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| work_unit_id | BIGINT UNSIGNED | NOT NULL, FK → work_units.id CASCADE | |
| user_id | BIGINT UNSIGNED | NOT NULL, FK → users.id CASCADE | |
| joined_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |

**Index:** UNIQUE(work_unit_id, user_id), `user_id`

**Aturan keanggotaan tim:**
- Satu user **boleh menjadi anggota di beberapa unit kerja** (constraint UNIQUE hanya mencegah duplikasi user di unit yang sama, bukan membatasi user hanya di 1 unit). Jika Ketua Tim Kerja terdaftar di 2 unit kerja, dia bisa melihat tiket dari kedua unit tersebut.
- Jika bisnis menginginkan 1 user hanya boleh punya 1 unit kerja, tambahkan UNIQUE constraint pada kolom `user_id` saja (bukan composite).

**Panduan query anggota tim untuk scope visibilitas Ketua Tim Kerja:**

```sql
-- Langkah 1: Ambil work_unit_id milik Ketua Tim Kerja yang login
-- Langkah 2: Ambil semua user_id di unit kerja tersebut
SELECT DISTINCT tm2.user_id
FROM team_members tm1
JOIN team_members tm2 ON tm1.work_unit_id = tm2.work_unit_id
WHERE tm1.user_id = :ketua_tim_user_id;

-- Langkah 3: Gunakan hasil di atas untuk filter tiket
SELECT * FROM tickets
WHERE reporter_id IN (
    SELECT DISTINCT tm2.user_id
    FROM team_members tm1
    JOIN team_members tm2 ON tm1.work_unit_id = tm2.work_unit_id
    WHERE tm1.user_id = :ketua_tim_user_id
);
```

> **Catatan Laravel:** Di Eloquent, bisa menggunakan `whereIn('reporter_id', $teamMemberIds)` setelah mengambil daftar user_id anggota tim.

---

## 4. roles *(Spatie Permission)*

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(125) | NOT NULL | super_admin, pegawai, ketua_tim_kerja, helpdesk, teknisi, manager_it |
| guard_name | VARCHAR(125) | NOT NULL, DEFAULT 'web' | |

**Index:** UNIQUE(name, guard_name)

---

## 5. model_has_roles *(Spatie Permission — pivot)*

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| role_id | BIGINT UNSIGNED | NOT NULL, FK → roles.id CASCADE | |
| model_type | VARCHAR(255) | NOT NULL | App\Models\User |
| model_id | BIGINT UNSIGNED | NOT NULL, FK → users.id CASCADE | |

**Primary Key:** (role_id, model_id, model_type)

---

## 5a. permissions *(Spatie Permission — otomatis)*

> Tabel ini **otomatis dibuat oleh Spatie Permission** saat menjalankan `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"` dan `php artisan migrate`. Didokumentasikan di sini agar implementor tahu struktur dan daftar permission yang perlu di-seed.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(125) | NOT NULL | Nama permission, contoh: `ticket.create`, `ticket.verify`, `master.category` |
| guard_name | VARCHAR(125) | NOT NULL, DEFAULT 'web' | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Index:** UNIQUE(name, guard_name)

**Daftar permission yang perlu di-seed:**

| Permission | Keterangan |
|------------|------------|
| `ticket.create` | Buat tiket baru |
| `ticket.view` | Lihat daftar & detail tiket (scope visibilitas tetap per role — lihat bagian 7) |
| `ticket.close` | Tutup tiket (kontekstual: hanya pelapor via `reporter_id`) |
| `ticket.reopen` | ReOpen tiket (kontekstual: hanya pelapor via `reporter_id`) |
| `ticket.verify` | Verifikasi tiket (Open → Verification). Hanya Helpdesk |
| `ticket.change-priority` | Ubah prioritas tiket saat verifikasi |
| `ticket.change-category` | Ubah kategori tiket saat verifikasi |
| `ticket.assign` | Assign tiket ke Teknisi |
| `ticket.reassign` | Reassign tiket ke Teknisi lain |
| `ticket.return` | Kembalikan tiket ke Helpdesk (Teknisi only) |
| `ticket.update-progress` | Update progress tiket |
| `ticket.resolve` | Tandai tiket selesai (Resolved) |
| `ticket.request-approval` | Request approval ke Manager IT |
| `ticket.mark-third-party` | Tandai butuh pihak ketiga |
| `ticket.clarify` | Minta klarifikasi ke pelapor |
| `ticket.reply-clarification` | Balas klarifikasi (kontekstual: hanya pelapor via `reporter_id`) |
| `ticket.comment` | Komentar di thread tiket |
| `ticket.upload-attachment` | Upload attachment |
| `ticket.approve` | Approve / Reject tiket (Manager IT) |
| `ticket.view-audit-trail` | Lihat riwayat/audit trail tiket |
| `dashboard.personal` | Akses dashboard personal |
| `dashboard.team` | Akses dashboard tim |
| `dashboard.operational` | Akses dashboard operasional |
| `report.export` | Akses laporan & export |
| `report.personal` | Akses laporan tiket pribadi |
| `master.category` | Kelola master kategori tiket |
| `master.priority` | Kelola master prioritas |
| `master.work-unit` | Kelola master unit kerja |
| `master.user` | Kelola user & role |
| `master.permission` | Kelola permission per role |
| `profile.manage` | Lihat/edit profil & ubah password |

**Tabel Mapping Permission × Role (Panduan Seeder):**

> **Cara baca:** ✅ = permission diberikan ke role tersebut, **-** = tidak diberikan. Gunakan tabel ini sebagai acuan saat membuat seeder `role_has_permissions`. Permission bertanda **(kontekstual)** artinya secara teknis dimiliki role tersebut, tapi saat runtime masih dicek kondisi tambahan (misal: harus `reporter_id` = user login).

| Permission | super_admin | pegawai | ketua_tim_kerja | helpdesk | teknisi | manager_it |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| `ticket.create` | - | ✅ | ✅ | ✅ | - | - |
| `ticket.view` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `ticket.close` **(kontekstual)** | - | ✅ | ✅ | ✅ | - | - |
| `ticket.reopen` **(kontekstual)** | - | ✅ | ✅ | ✅ | - | - |
| `ticket.verify` | - | - | - | ✅ | - | - |
| `ticket.change-priority` | - | - | - | ✅ | - | - |
| `ticket.change-category` | - | - | - | ✅ | - | - |
| `ticket.assign` | - | - | - | ✅ | - | - |
| `ticket.reassign` | - | - | - | ✅ | - | - |
| `ticket.return` | - | - | - | - | ✅ | - |
| `ticket.update-progress` | - | - | - | ✅ | ✅ | - |
| `ticket.resolve` | - | - | - | ✅ | ✅ | - |
| `ticket.request-approval` | - | - | - | ✅ | ✅ | - |
| `ticket.mark-third-party` | - | - | - | ✅ | ✅ | - |
| `ticket.clarify` | - | - | - | ✅ | ✅ | - |
| `ticket.reply-clarification` **(kontekstual)** | - | ✅ | ✅ | ✅ | - | - |
| `ticket.comment` | - | ✅ | ✅ | ✅ | ✅ | ✅ |
| `ticket.upload-attachment` | - | ✅ | ✅ | ✅ | ✅ | ✅ |
| `ticket.approve` | - | - | - | - | - | ✅ |
| `ticket.view-audit-trail` | ✅ | - | - | ✅ | ✅ | - |
| `dashboard.personal` | - | ✅ | ✅ | ✅ | ✅ | - |
| `dashboard.team` | - | - | ✅ | - | - | - |
| `dashboard.operational` | ✅ | - | - | ✅ | - | ✅ |
| `report.export` | ✅ | - | - | - | - | ✅ |
| `report.personal` | - | ✅ | ✅ | ✅ | ✅ | ✅ |
| `master.category` | ✅ | - | - | - | - | - |
| `master.priority` | ✅ | - | - | - | - | - |
| `master.work-unit` | ✅ | - | - | - | - | - |
| `master.user` | ✅ | - | - | - | - | - |
| `master.permission` | ✅ | - | - | - | - | - |
| `profile.manage` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

> **Total:** Super Admin = 10 permission, Pegawai = 10 permission, Ketua Tim Kerja = 11 permission, Helpdesk = 22 permission, Teknisi = 13 permission, Manager IT = 8 permission.

---

## 5b. role_has_permissions *(Spatie Permission — otomatis, pivot)*

> Tabel pivot yang menghubungkan role dengan permission. **Otomatis dibuat oleh Spatie Permission.** Fitur "Permission per role" di Super Admin bekerja dengan tabel ini — Super Admin mencentang permission mana yang dimiliki setiap role.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| permission_id | BIGINT UNSIGNED | NOT NULL, FK → permissions.id CASCADE | |
| role_id | BIGINT UNSIGNED | NOT NULL, FK → roles.id CASCADE | |

**Primary Key:** (permission_id, role_id)

---

## 5c. model_has_permissions *(Spatie Permission — otomatis, pivot, opsional)*

> Tabel untuk memberikan permission langsung ke user tertentu **tanpa melalui role**. **Otomatis dibuat oleh Spatie Permission.** Di aplikasi ini kemungkinan tidak dipakai di fase awal, tapi tetap akan dibuat saat migrasi Spatie.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| permission_id | BIGINT UNSIGNED | NOT NULL, FK → permissions.id CASCADE | |
| model_type | VARCHAR(255) | NOT NULL | App\Models\User |
| model_id | BIGINT UNSIGNED | NOT NULL, FK → users.id CASCADE | |

**Primary Key:** (permission_id, model_id, model_type)

---

## 6. ticket_categories

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL | |
| description | TEXT | NULL | |
| is_active | TINYINT(1) | NOT NULL, DEFAULT 1 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

---

## 7. ticket_priorities

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| name | VARCHAR(50) | NOT NULL | Low / Medium / High |
| level | TINYINT UNSIGNED | NOT NULL, UNIQUE | 1=Low, 2=Medium, 3=High |
| color | VARCHAR(7) | NULL | Hex color untuk badge UI, misal #F59E0B |
| is_active | TINYINT(1) | NOT NULL, DEFAULT 1 | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

---

## 7b. ticket_counters

> Menyimpan counter harian untuk generate nomor tiket (lihat bagian 13). Menggantikan pendekatan `SELECT MAX(ticket_number)` yang lebih rawan race condition.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| date | DATE | PK | Tanggal (YYYY-MM-DD) |
| last_number | INT UNSIGNED | NOT NULL, DEFAULT 0 | Counter terakhir untuk hari tersebut |

---

## 8. tickets *(Tabel Utama)*

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| ticket_number | VARCHAR(30) | NOT NULL, UNIQUE, INDEX | Format: TKT-YYYYMMDD-XXXX. Counter reset harian via tabel `ticket_counters` (lihat bagian 13). Atomic increment untuk menghindari duplikat |
| reporter_id | BIGINT UNSIGNED | NOT NULL, FK → users.id RESTRICT | Pelapor (Pegawai/Ketua Tim/Helpdesk yang membuat tiket) |
| handler_id | BIGINT UNSIGNED | NULL, FK → users.id SET NULL | Helpdesk/Teknisi yang terakhir aktif mengerjakan |
| category_id | BIGINT UNSIGNED | NOT NULL, FK → ticket_categories.id RESTRICT | Dipilih pelapor saat submit, bisa diubah Helpdesk saat verifikasi |
| priority_id | BIGINT UNSIGNED | NOT NULL, FK → ticket_priorities.id RESTRICT | Dipilih pelapor saat submit (default: Medium), bisa diubah Helpdesk saat verifikasi |
| title | VARCHAR(255) | NOT NULL | |
| description | TEXT | NOT NULL | |
| status | ENUM | NOT NULL, DEFAULT 'open', INDEX | open, verification, in_progress, waiting_for_info, waiting_third_party, pending_approval, resolved, closed, rejected |
| started_at | TIMESTAMP | NULL | Diisi pertama kali tiket masuk in_progress. Tidak di-reset saat ReOpen |
| total_paused_seconds | INT UNSIGNED | NOT NULL, DEFAULT 0 | Cache akumulasi durasi pause, diupdate setiap resume dari ticket_time_logs |
| resolved_at | TIMESTAMP | NULL | Diisi saat status → resolved. Di-reset NULL saat ReOpen |
| closed_at | TIMESTAMP | NULL | Diisi saat status → closed |
| auto_close_at | TIMESTAMP | NULL | resolved_at + 72 jam. Di-set saat status → Resolved. Di-reset NULL saat ReOpen |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Index:** `reporter_id`, `handler_id`, `status`, `category_id`, `priority_id`, `auto_close_at`

**Kalkulasi durasi efektif:**

```
durasi_efektif_detik = (closed_at - started_at) - total_paused_seconds
Konversi ke format tampilan: X hari Y jam Z menit
```

---

## 9. ticket_time_logs

> Setiap baris = satu segmen pause. Menggantikan kolom `paused_at` tunggal di tabel `tickets` agar multi-pause tercatat dengan benar. Kolom `note` menggantikan `third_party_note` yang sebelumnya ada di `tickets`.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| ticket_id | BIGINT UNSIGNED | NOT NULL, FK → tickets.id CASCADE | |
| pause_reason | ENUM | NOT NULL | waiting_for_info, waiting_third_party |
| note | TEXT | NULL | Catatan alasan pause (pihak ketiga, info yang dibutuhkan, dsb) |
| paused_at | TIMESTAMP | NOT NULL | Waktu masuk status pause |
| resumed_at | TIMESTAMP | NULL | NULL = masih dalam kondisi pause |
| duration_seconds | INT UNSIGNED | NULL | Diisi otomatis saat resumed_at terisi: `duration_seconds = resumed_at - paused_at` |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Index:** `ticket_id`, `resumed_at`

**Relasi:** `tickets` (1) ──< (many) `ticket_time_logs`

**Saat resume:** Update `resumed_at`, hitung `duration_seconds`, lalu tambahkan nilai `duration_seconds` ke `tickets.total_paused_seconds`.

---

## 10. ticket_comments

> Komentar biasa, permintaan klarifikasi, dan balasan klarifikasi ditampung dalam satu tabel. Kolom `type` membedakan jenisnya. Tidak ada `parent_id` — semua komentar flat dan urut by `created_at`.
>
> **Penting:** Komentar bersifat **immutable** — setelah dikirim, komentar **tidak bisa diedit atau dihapus**. Oleh karena itu tabel ini hanya memiliki `created_at` tanpa `updated_at`.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| ticket_id | BIGINT UNSIGNED | NOT NULL, FK → tickets.id CASCADE | |
| user_id | BIGINT UNSIGNED | NOT NULL, FK → users.id RESTRICT | |
| body | TEXT | NOT NULL | |
| type | ENUM | NOT NULL, DEFAULT 'comment' | comment, clarification, clarification_reply |
| created_at | TIMESTAMP | | |

**Index:** `ticket_id`, `user_id`, `type`

**Relasi:** `tickets` (1) ──< (many) `ticket_comments`

---

## 11. ticket_attachments

> File yang diupload. Bisa saat submit tiket (`comment_id` NULL) atau saat menambah komentar (`comment_id` terisi).

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| ticket_id | BIGINT UNSIGNED | NOT NULL, FK → tickets.id CASCADE | |
| comment_id | BIGINT UNSIGNED | NULL, FK → ticket_comments.id SET NULL | NULL = diupload saat submit tiket |
| uploaded_by | BIGINT UNSIGNED | NOT NULL, FK → users.id RESTRICT | |
| original_name | VARCHAR(255) | NOT NULL | Nama file asli dari user |
| stored_name | VARCHAR(255) | NOT NULL | Nama file di storage (UUID-based) |
| file_path | VARCHAR(500) | NOT NULL | Relative path di storage |
| file_size | INT UNSIGNED | NOT NULL | Bytes, maks 10.485.760 (10 MB) |
| mime_type | VARCHAR(127) | NOT NULL | |
| created_at | TIMESTAMP | | |

**Index:** `ticket_id`, `comment_id`, `uploaded_by`

**Aturan (enforced di application layer):**
- Maks 10 MB per file
- Maks 5 file per sekali upload saat submit tiket
- Maks 3 file per sekali upload saat komentar
- Ekstensi diblokir: `.exe` `.bat` `.sh` `.msi` `.apk`

---

## 12. ticket_histories *(Audit Trail — Append Only)*

> Tidak pernah di-update atau di-delete. Kolom `time_log_id` hanya terisi saat action `paused` atau `resumed`. Kolom `new_handler_id` hanya terisi saat action `assigned` atau `reassigned`, menyimpan ID handler yang menjadi target assign — dibutuhkan untuk query laporan pribadi Teknisi.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| ticket_id | BIGINT UNSIGNED | NOT NULL, FK → tickets.id CASCADE | |
| actor_id | BIGINT UNSIGNED | NULL, FK → users.id SET NULL | NULL = aksi otomatis sistem (Scheduler). Untuk action `assigned`/`reassigned`, ini adalah Helpdesk yang melakukan assign |
| new_handler_id | BIGINT UNSIGNED | NULL, FK → users.id SET NULL | Terisi **hanya** saat action = `assigned` atau `reassigned`. Menyimpan ID Teknisi/Helpdesk yang menjadi handler baru. Digunakan untuk query laporan pribadi Teknisi |
| time_log_id | BIGINT UNSIGNED | NULL, FK → ticket_time_logs.id SET NULL | Terisi hanya saat action paused atau resumed |
| from_status | VARCHAR(30) | NULL | NULL jika tiket baru dibuat |
| to_status | VARCHAR(30) | NOT NULL | |
| action | VARCHAR(100) | NOT NULL | Lihat daftar action di bawah |
| note | TEXT | NULL | Catatan tambahan atau alasan (wajib diisi untuk: returned_to_helpdesk, rejected, rejected_closed, rejected_by_helpdesk) |
| created_at | TIMESTAMP | | |

**Daftar Action yang Valid:**

| Action | Kapan Digunakan |
|--------|----------------|
| `created` | Tiket baru dibuat |
| `verified` | Open → Verification |
| `assigned` | Verification → In Progress (assign ke handler) |
| `reassigned` | In Progress → In Progress (ganti handler) |
| `returned_to_helpdesk` | In Progress → Verification (Teknisi kembalikan) |
| `paused` | In Progress → Waiting for Info / Waiting Third Party |
| `resumed` | Waiting for Info / Waiting Third Party → In Progress |
| `resolved` | In Progress → Resolved |
| `closed` | Resolved → Closed (pelapor konfirmasi) |
| `auto_closed` | Resolved → Closed (sistem auto-close 3 hari) |
| `rejected_closed` | Rejected → Closed (otomatis setelah Manager IT reject) |
| `reopened` | Resolved → In Progress (pelapor reopen) |
| `approval_requested` | In Progress → Pending Approval |
| `approved` | Pending Approval → In Progress (Manager IT setujui) |
| `rejected` | Pending Approval → Rejected (Manager IT tolak) |
| `rejected_by_helpdesk` | Verification → Closed (Helpdesk tolak tiket invalid/duplikat) |
| `auto_closed_no_response` | Waiting for Info → Closed (sistem auto-close 14 hari tanpa jawaban pelapor) |

**Index:** `ticket_id`, `actor_id`, `time_log_id`, `new_handler_id`

**Relasi:**
- `tickets` (1) ──< (many) `ticket_histories`
- `ticket_time_logs` (1) ──< (many, maks 2) `ticket_histories`

> **Catatan implementasi:** Kolom `from_status` dan `to_status` sengaja menggunakan VARCHAR (bukan ENUM) agar append-only table tidak perlu di-ALTER saat ada penambahan status di masa depan. **Untuk mencegah typo**, gunakan PHP Enum class yang sama dengan yang dipakai untuk `tickets.status`:
>
> ```php
> enum TicketStatus: string {
>     case Open = 'open';
>     case Verification = 'verification';
>     case InProgress = 'in_progress';
>     case WaitingForInfo = 'waiting_for_info';
>     case WaitingThirdParty = 'waiting_third_party';
>     case PendingApproval = 'pending_approval';
>     case Resolved = 'resolved';
>     case Closed = 'closed';
>     case Rejected = 'rejected';
> }
> ```
>
> Pastikan semua insert ke `ticket_histories` menggunakan enum ini, bukan string literal.

---

## 13. ticket_approvals

> Kolom `is_current` memastikan query selalu mendapat approval terbaru/aktif dengan aman tanpa perlu ORDER BY + LIMIT.

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| ticket_id | BIGINT UNSIGNED | NOT NULL, FK → tickets.id CASCADE | |
| requested_by | BIGINT UNSIGNED | NOT NULL, FK → users.id RESTRICT | Helpdesk / Teknisi yang mengajukan |
| reviewed_by | BIGINT UNSIGNED | NULL, FK → users.id SET NULL | Manager IT, NULL = belum direview |
| status | ENUM | NOT NULL, DEFAULT 'pending' | pending, approved, rejected |
| is_current | TINYINT(1) | NOT NULL, DEFAULT 1 | 1 = approval aktif/terbaru, 0 = sudah digantikan. Saat insert approval baru, set semua approval lama untuk tiket yang sama ke `is_current = 0` |
| note | TEXT | NULL | Alasan approval atau rejection (wajib diisi saat rejected) |
| reviewed_at | TIMESTAMP | NULL | |
| created_at | TIMESTAMP | | |
| updated_at | TIMESTAMP | | |

**Index:** `ticket_id`, `status`, `is_current`

---

## 14. notifications *(Tabel Baru)*

> Menyimpan notifikasi in-app untuk setiap user. Append-only (tidak pernah di-update selain kolom `is_read` dan `read_at`).

| Kolom | Tipe | Constraint | Keterangan |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | |
| user_id | BIGINT UNSIGNED | NOT NULL, FK → users.id CASCADE | Penerima notifikasi |
| ticket_id | BIGINT UNSIGNED | NULL, FK → tickets.id SET NULL | Tiket terkait (NULL jika notifikasi umum/sistem) |
| type | VARCHAR(100) | NOT NULL | Jenis event, contoh: ticket_created, assigned, resolved, auto_closed, comment_added, dll |
| title | VARCHAR(255) | NOT NULL | Judul notifikasi (contoh: "Tiket Baru: TKT-20260421-0001") |
| body | TEXT | NULL | Isi detail notifikasi |
| is_read | TINYINT(1) | NOT NULL, DEFAULT 0 | 0 = belum dibaca, 1 = sudah dibaca |
| read_at | TIMESTAMP | NULL | Waktu notifikasi dibaca |
| created_at | TIMESTAMP | | |

**Index:** `user_id`, `is_read`, `ticket_id`

**Daftar Nilai `type` yang Valid:**

| type | Event |
|------|-------|
| `ticket_created` | Tiket baru dibuat |
| `ticket_assigned` | Tiket di-assign ke Teknisi |
| `ticket_reassigned` | Tiket di-reassign ke Teknisi lain |
| `ticket_returned` | Tiket dikembalikan ke Helpdesk |
| `clarification_requested` | Ada klarifikasi masuk dari handler |
| `clarification_replied` | Pelapor menjawab klarifikasi |
| `ticket_resolved` | Tiket ditandai selesai |
| `approval_requested` | Request approval dikirim ke Manager IT |
| `ticket_approved` | Tiket diapprove Manager IT |
| `ticket_rejected` | Tiket ditolak Manager IT |
| `ticket_auto_closed` | Tiket auto-closed setelah 3 hari |
| `ticket_waiting_third_party` | Tiket masuk status Waiting Third Party (notif ke pelapor) |
| `ticket_closed` | Tiket ditutup oleh pelapor (konfirmasi selesai) |
| `ticket_reopened` | Tiket di-ReOpen oleh pelapor |
| `comment_added` | Ada komentar baru di tiket |
| `ticket_rejected_by_helpdesk` | Tiket ditolak Helpdesk saat verifikasi (invalid/duplikat) |
| `ticket_reminder_waiting` | Reminder ke pelapor: tiket menunggu jawaban klarifikasi (3 hari) |
| `ticket_auto_closed_no_response` | Tiket auto-closed karena 14 hari tanpa jawaban klarifikasi |

---

## Ringkasan Semua Relasi

| Tabel | Kolom FK | Ke Tabel | Tipe Relasi | ON DELETE |
| --- | --- | --- | --- | --- |
| users | work_unit_id | work_units.id | Many-to-one | SET NULL |
| team_members | work_unit_id | work_units.id | Many-to-one | CASCADE |
| team_members | user_id | users.id | Many-to-one | CASCADE |
| model_has_roles | role_id | roles.id | Many-to-one | CASCADE |
| model_has_roles | model_id | users.id | Many-to-one | CASCADE |
| role_has_permissions | permission_id | permissions.id | Many-to-one | CASCADE |
| role_has_permissions | role_id | roles.id | Many-to-one | CASCADE |
| model_has_permissions | permission_id | permissions.id | Many-to-one | CASCADE |
| model_has_permissions | model_id | users.id | Many-to-one | CASCADE |
| tickets | reporter_id | users.id | Many-to-one | RESTRICT |
| tickets | handler_id | users.id | Many-to-one | SET NULL |
| tickets | category_id | ticket_categories.id | Many-to-one | RESTRICT |
| tickets | priority_id | ticket_priorities.id | Many-to-one | RESTRICT |
| ticket_time_logs | ticket_id | tickets.id | Many-to-one | CASCADE |
| ticket_comments | ticket_id | tickets.id | Many-to-one | CASCADE |
| ticket_comments | user_id | users.id | Many-to-one | RESTRICT |
| ticket_attachments | ticket_id | tickets.id | Many-to-one | CASCADE |
| ticket_attachments | comment_id | ticket_comments.id | Many-to-one | SET NULL |
| ticket_attachments | uploaded_by | users.id | Many-to-one | RESTRICT |
| ticket_histories | ticket_id | tickets.id | Many-to-one | CASCADE |
| ticket_histories | actor_id | users.id | Many-to-one | SET NULL |
| ticket_histories | time_log_id | ticket_time_logs.id | Many-to-one | SET NULL |
| ticket_histories | new_handler_id | users.id | Many-to-one | SET NULL |
| ticket_approvals | ticket_id | tickets.id | Many-to-one | CASCADE |
| ticket_approvals | requested_by | users.id | Many-to-one | RESTRICT |
| ticket_approvals | reviewed_by | users.id | Many-to-one | SET NULL |
| notifications | user_id | users.id | Many-to-one | CASCADE |
| notifications | ticket_id | tickets.id | Many-to-one | SET NULL |