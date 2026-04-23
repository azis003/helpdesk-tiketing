# Fase 5 — Ticket Core: Buat, List, Detail

> **Tujuan:** Implementasi buat tiket, daftar tiket (dengan scope visibilitas), dan halaman detail tiket.
> **Referensi:** Dokumentasi Bag. 4, 7, 12, 13
> **Prasyarat:** Fase 4 selesai

---

## Task 5.1 — Service: Generate Nomor Tiket

**Apa yang dilakukan:**
Buat service class untuk generate nomor tiket yang aman dari race condition.

**Referensi:** Dokumentasi Bag. 13 (Nomor Tiket) + ERD Bag. 7b (ticket_counters)

**File:** `app/Services/TicketNumberGenerator.php`

```php
class TicketNumberGenerator
{
    public function generate(): string
    {
        $today = now()->format('Y-m-d');

        // Atomic increment — aman dari race condition
        DB::statement(
            "INSERT INTO ticket_counters (date, last_number) VALUES (?, 1)
             ON DUPLICATE KEY UPDATE last_number = last_number + 1",
            [$today]
        );

        $counter = DB::table('ticket_counters')
            ->where('date', $today)
            ->value('last_number');

        // Format: TKT-YYYYMMDD-0001
        return sprintf('TKT-%s-%04d', now()->format('Ymd'), $counter);
    }
}
```

**Acceptance Criteria:**
- [ ] Nomor tiket pertama hari ini: `TKT-YYYYMMDD-0001`
- [ ] Nomor tiket kedua: `TKT-YYYYMMDD-0002`
- [ ] Hari berbeda → counter reset ke 0001
- [ ] Dua request bersamaan → nomor tidak duplikat

---

## Task 5.2 — Halaman Buat Tiket

**Permission:** `ticket.create`
**Referensi:** Dokumentasi Bag. 12 (Form Buat Tiket) + Bag. 11 (Attachment)

**File yang dibuat:**
1. `app/Http/Controllers/TicketController.php` (method: `create`, `store`)
2. `resources/js/Pages/Ticket/Create.vue`

**Form fields:**
- Judul (`title`) — wajib, max 255
- Deskripsi (`description`) — wajib, textarea
- Kategori (`category_id`) — dropdown, hanya `is_active = 1`
- Prioritas (`priority_id`) — dropdown, hanya `is_active = 1`, default "Medium"
- Attachment — file upload, maks 5 file, maks 10MB per file

**Saat submit (dalam 1 database transaction):**
1. Generate nomor tiket via `TicketNumberGenerator`
2. Insert ke tabel `tickets` (status = `open`)
3. Jika ada attachment → simpan file ke storage, insert ke `ticket_attachments` (comment_id = NULL)
4. Insert ke `ticket_histories` (action = `created`, from_status = NULL, to_status = `open`)
5. Kirim notifikasi ke semua Helpdesk (nanti di Fase 9)

**Validasi attachment:**
```php
'attachments.*' => 'file|max:10240', // max 10MB
'attachments' => 'array|max:5',
```

**Blokir ekstensi:**
```php
$blocked = ['exe', 'bat', 'sh', 'msi', 'apk'];
$extension = $file->getClientOriginalExtension();
if (in_array(strtolower($extension), $blocked)) {
    // tolak file
}
```

**Acceptance Criteria:**
- [ ] Pelapor (Pegawai/Ketua Tim/Helpdesk) bisa buat tiket
- [ ] Nomor tiket auto-generate
- [ ] Attachment berhasil diupload ke storage
- [ ] Record `ticket_histories` (action = created) terinsert
- [ ] Tiket muncul di daftar tiket setelah dibuat
- [ ] Role tanpa permission `ticket.create` → 403

---

## Task 5.3 — Halaman Daftar Tiket (dengan Scope Visibilitas)

**Permission:** `ticket.view`
**Referensi:** Dokumentasi Bag. 7 (Scope Visibilitas)

**File yang dibuat:**
1. `app/Http/Controllers/TicketController.php` (method: `index`)
2. `resources/js/Pages/Ticket/Index.vue`
3. `app/Services/TicketVisibilityScope.php` — helper untuk filter per role

**⚠️ INI BAGIAN KRITIS — Scope visibilitas harus benar per role:**

```php
// Di TicketVisibilityScope.php
class TicketVisibilityScope
{
    public static function apply($query, User $user): void
    {
        $role = $user->getRoleNames()->first();

        match ($role) {
            'pegawai' => $query->where('reporter_id', $user->id),

            'ketua_tim_kerja' => $query->whereIn('reporter_id',
                self::getTeamMemberIds($user->id)
            ),

            'helpdesk', 'manager_it', 'super_admin' => null, // semua tiket

            'teknisi' => $query->where('handler_id', $user->id),
        };
    }

    private static function getTeamMemberIds(int $userId): array
    {
        return TeamMember::query()
            ->whereIn('work_unit_id',
                TeamMember::where('user_id', $userId)->pluck('work_unit_id')
            )
            ->pluck('user_id')
            ->unique()
            ->toArray();
    }
}
```

**Fitur halaman:**
- Tabel tiket dengan kolom: Nomor, Judul, Pelapor, Handler, Kategori, Prioritas, Status, Tanggal
- Pagination (10 per halaman)
- Filter: status, kategori, prioritas
- Search: nomor tiket atau judul
- Sorting: tanggal terbaru (default)
- Badge warna untuk status dan prioritas

**Acceptance Criteria:**
- [ ] Pegawai hanya lihat tiket milik sendiri
- [ ] Ketua Tim lihat tiket anggota timnya
- [ ] Helpdesk, Manager IT, Super Admin lihat semua
- [ ] Teknisi hanya lihat tiket yang di-assign ke dia
- [ ] Filter dan search berfungsi
- [ ] Pagination berfungsi

---

## Task 5.4 — Halaman Detail Tiket

**Permission:** `ticket.view` + scope visibilitas tetap berlaku

**File yang dibuat:**
1. `app/Http/Controllers/TicketController.php` (method: `show`)
2. `resources/js/Pages/Ticket/Show.vue`

**Informasi yang ditampilkan:**

**Header tiket:**
- Nomor tiket, judul, status (badge warna)
- Pelapor (nama), Handler (nama atau "Belum di-assign")
- Kategori, Prioritas (badge warna)
- Tanggal dibuat, durasi pengerjaan efektif (jika sudah closed)

**Deskripsi tiket** — dengan attachment saat submit

**Thread komentar** — urut by `created_at`, tampilkan:
- Nama user + role + avatar
- Body komentar
- Type badge (clarification = warna berbeda, clarification_reply = warna berbeda)
- Attachment per komentar
- Waktu (relative: "2 jam yang lalu")

**Audit Trail** (hanya untuk role dengan permission `ticket.view-audit-trail`):
- Timeline vertikal dari `ticket_histories`
- Setiap entry: waktu, aktor, action, note

**Action buttons** (tampilkan sesuai permission + konteks):
- Tombol-tombol ini dibuat di fase selanjutnya, tapi tempatkan posisinya di UI sekarang

**Acceptance Criteria:**
- [ ] Detail tiket tampil lengkap
- [ ] Komentar urut by waktu
- [ ] Attachment bisa di-download
- [ ] Audit trail hanya muncul untuk role yang punya permission
- [ ] User yang tidak punya akses ke tiket ini → 403
