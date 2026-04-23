# Fase 4 — Master Data CRUD (Super Admin)

> **Tujuan:** Buat halaman CRUD untuk master data yang dikelola Super Admin.
> **Referensi:** Dokumentasi Bag. 4 (Master Data & Konfigurasi)
> **Prasyarat:** Fase 3 selesai

---

## Task 4.1 — CRUD Kategori Tiket

**Permission:** `master.category`
**Tabel:** `ticket_categories` (ERD Bag. 6)

**File yang dibuat:**
1. `app/Http/Controllers/Master/CategoryController.php`
2. `resources/js/Pages/Master/Category/Index.vue` — List + search + filter aktif/nonaktif
3. `resources/js/Pages/Master/Category/Create.vue` — Form tambah
4. `resources/js/Pages/Master/Category/Edit.vue` — Form edit

**Fitur:**
- List kategori dengan pagination (10 per halaman)
- Tambah kategori baru (nama wajib, deskripsi opsional)
- Edit kategori existing
- Aktifkan/nonaktifkan kategori (toggle `is_active`)
- **TIDAK ADA fitur hapus** — karena FK RESTRICT dari `tickets.category_id`
- Tampilkan jumlah tiket yang menggunakan kategori ini (counter)

**Validasi:**
```php
'name' => 'required|string|max:100',
'description' => 'nullable|string',
```

**Acceptance Criteria:**
- [ ] Super Admin bisa list, tambah, edit kategori
- [ ] Bisa toggle aktif/nonaktif
- [ ] Tidak ada tombol hapus
- [ ] Role selain Super Admin → 403 Forbidden

---

## Task 4.2 — CRUD Prioritas Tiket

**Permission:** `master.priority`
**Tabel:** `ticket_priorities` (ERD Bag. 7)

**File yang dibuat:**
1. `app/Http/Controllers/Master/PriorityController.php`
2. `resources/js/Pages/Master/Priority/Index.vue`
3. `resources/js/Pages/Master/Priority/Create.vue`
4. `resources/js/Pages/Master/Priority/Edit.vue`

**Fitur:**
- List prioritas (biasanya hanya 3: Low, Medium, High)
- Tambah/edit prioritas (nama, level, color hex)
- Toggle aktif/nonaktif
- **TIDAK ADA hapus** — karena FK RESTRICT
- Tampilkan badge warna sesuai field `color`

**Validasi:**
```php
'name' => 'required|string|max:50',
'level' => 'required|integer|min:1|unique:ticket_priorities,level,' . $id,
'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
```

**Acceptance Criteria:**
- [ ] Super Admin bisa kelola prioritas
- [ ] Badge warna tampil sesuai hex color
- [ ] Level unique (tidak boleh duplikat)

---

## Task 4.3 — CRUD Unit Kerja

**Permission:** `master.work-unit`
**Tabel:** `work_units` (ERD Bag. 1) + `team_members` (ERD Bag. 3)

**File yang dibuat:**
1. `app/Http/Controllers/Master/WorkUnitController.php`
2. `resources/js/Pages/Master/WorkUnit/Index.vue`
3. `resources/js/Pages/Master/WorkUnit/Create.vue`
4. `resources/js/Pages/Master/WorkUnit/Edit.vue`
5. `resources/js/Pages/Master/WorkUnit/Members.vue` — Kelola anggota tim

**Fitur Unit Kerja:**
- List unit kerja dengan pagination
- Tambah/edit unit kerja (nama, kode)
- Toggle aktif/nonaktif
- Kode harus unique

**Fitur Anggota Tim (halaman Members):**
- Tampilkan daftar anggota unit kerja (dari `team_members`)
- Tambah anggota (pilih user dari dropdown)
- Hapus anggota
- **⚠️ PENTING:** Saat menambah user ke unit kerja, update JUGA `users.work_unit_id`

**Validasi:**
```php
// Unit Kerja
'name' => 'required|string|max:100',
'code' => 'required|string|max:20|unique:work_units,code,' . $id,
```

**Acceptance Criteria:**
- [ ] CRUD unit kerja berfungsi
- [ ] Bisa kelola anggota tim
- [ ] Saat tambah anggota: `team_members` + `users.work_unit_id` terupdate
- [ ] Duplikasi anggota di unit yang sama → error (unique constraint)

---

## Task 4.4 — CRUD User & Role

**Permission:** `master.user`
**Tabel:** `users` (ERD Bag. 2) + `model_has_roles` (Spatie)

**File yang dibuat:**
1. `app/Http/Controllers/Master/UserController.php`
2. `resources/js/Pages/Master/User/Index.vue`
3. `resources/js/Pages/Master/User/Create.vue`
4. `resources/js/Pages/Master/User/Edit.vue`

**Fitur:**
- List user dengan pagination, search by nama/username, filter by role & status aktif
- Tambah user baru (username, nama, email, password, role, unit kerja)
- Edit user (nama, email, role, unit kerja)
- Reset password user
- Toggle aktif/nonaktif

**⚠️ PENTING — Sebelum nonaktifkan user (Dokumentasi Bag. 9):**
```php
// Cek apakah user punya tiket aktif
$activeTickets = Ticket::where('handler_id', $userId)
    ->whereIn('status', [
        TicketStatus::InProgress,
        TicketStatus::WaitingForInfo,
        TicketStatus::WaitingThirdParty,
        TicketStatus::PendingApproval,
    ])
    ->count();

if ($activeTickets > 0) {
    return back()->withErrors([
        'is_active' => "User ini masih punya {$activeTickets} tiket aktif. Reassign tiket terlebih dahulu."
    ]);
}
```

**Validasi:**
```php
// Create
'username' => 'required|string|max:50|unique:users,username',
'name' => 'required|string|max:100',
'email' => 'nullable|email|max:100|unique:users,email',
'password' => 'required|string|min:8',
'role' => 'required|exists:roles,name',
'work_unit_id' => 'nullable|exists:work_units,id',
```

**Acceptance Criteria:**
- [ ] Super Admin bisa CRUD user
- [ ] Setiap user punya tepat 1 role
- [ ] User tidak bisa dinonaktifkan jika punya tiket aktif
- [ ] Password di-hash dengan bcrypt
- [ ] Username unique

---

## Task 4.5 — Kelola Permission per Role

**Permission:** `master.permission`
**Referensi:** Dokumentasi Bag. 5a (Mapping Permission × Role)

**File yang dibuat:**
1. `app/Http/Controllers/Master/PermissionController.php`
2. `resources/js/Pages/Master/Permission/Index.vue`

**Fitur:**
- Tampilkan tabel: baris = permissions, kolom = roles
- Setiap sel adalah checkbox (centang = role punya permission)
- Super Admin bisa mencentang/uncentang permission untuk setiap role
- Tombol "Save" untuk menyimpan perubahan

**⚠️ PENTING:** Gunakan `$role->syncPermissions($permissions)` dari Spatie.

**Acceptance Criteria:**
- [ ] Tampilkan matriks permission × role
- [ ] Checkbox bisa dicentang/uncentang
- [ ] Perubahan tersimpan ke tabel `role_has_permissions`
- [ ] Clear permission cache setelah save: `app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions()`
