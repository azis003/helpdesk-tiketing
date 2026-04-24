# Planning Implementasi Task 04 - Master Data

> **Berdasarkan:** Dokumentasi Aplikasi Helpdesk Tiketing v6.0 & tasks/04-master-data.md
> **Stack:** Laravel 12 + Vue 3 + Inertia.js + Tailwind v4 + MySQL + Spatie Permission

Dokumen ini berisi tahapan-tahapan detail (step-by-step) untuk mengimplementasikan Fase 4 — Master Data CRUD (Super Admin). Dokumen ini disusun sedemikian rupa agar programmer junior atau model AI lain dapat dengan mudah mengimplementasikan fungsionalitas ini.

## Prasyarat
- Fase 1, Fase 2, dan Fase 3 sudah selesai diimplementasikan.
- Struktur dasar Vue, Tailwind, dan Inertia.js sudah berfungsi.
- Route middleware `auth` sudah terpasang.
- Base layout dengan sidebar/navbar sudah siap untuk menampung menu Master Data.
- Model, tabel dan seeders untuk data awal telah tersedia.

---

## 1. Persiapan Route & Middleware

Tambahkan route group khusus untuk Master Data di `routes/web.php` yang diproteksi oleh middleware `auth` dan middleware permission (bawaan Spatie).

```php
// routes/web.php
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\PriorityController;
use App\Http\Controllers\Master\WorkUnitController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\PermissionController;

Route::middleware(['auth'])->prefix('master')->name('master.')->group(function () {
    // 1. Kategori Tiket
    Route::resource('categories', CategoryController::class)->except(['show', 'destroy'])
        ->middleware('permission:master.category');
    Route::patch('categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])
        ->name('categories.toggle-active')->middleware('permission:master.category');

    // 2. Prioritas Tiket
    Route::resource('priorities', PriorityController::class)->except(['show', 'destroy'])
        ->middleware('permission:master.priority');
    Route::patch('priorities/{priority}/toggle-active', [PriorityController::class, 'toggleActive'])
        ->name('priorities.toggle-active')->middleware('permission:master.priority');

    // 3. Unit Kerja
    Route::resource('work-units', WorkUnitController::class)->except(['show', 'destroy'])
        ->middleware('permission:master.work-unit');
    Route::patch('work-units/{work_unit}/toggle-active', [WorkUnitController::class, 'toggleActive'])
        ->name('work-units.toggle-active')->middleware('permission:master.work-unit');
    // Kelola Anggota Unit Kerja
    Route::get('work-units/{work_unit}/members', [WorkUnitController::class, 'members'])
        ->name('work-units.members')->middleware('permission:master.work-unit');
    Route::post('work-units/{work_unit}/members', [WorkUnitController::class, 'storeMember'])
        ->name('work-units.members.store')->middleware('permission:master.work-unit');
    Route::delete('work-units/{work_unit}/members/{user}', [WorkUnitController::class, 'destroyMember'])
        ->name('work-units.members.destroy')->middleware('permission:master.work-unit');

    // 4. User & Role
    Route::resource('users', UserController::class)->except(['show', 'destroy'])
        ->middleware('permission:master.user');
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])
        ->name('users.toggle-active')->middleware('permission:master.user');

    // 5. Permission per Role
    Route::get('permissions', [PermissionController::class, 'index'])
        ->name('permissions.index')->middleware('permission:master.permission');
    Route::post('permissions', [PermissionController::class, 'update'])
        ->name('permissions.update')->middleware('permission:master.permission');
});
```

---

## 2. Implementasi Task 4.1 — CRUD Kategori Tiket

**Tujuan:** Mengelola tabel `ticket_categories` (List, Tambah, Edit, Toggle Aktif). Tanpa hapus karena adanya FK RESTRICT.

### 2.1 Buat Controller
Jalankan command: `php artisan make:controller Master/CategoryController`

Di dalam `CategoryController`:
- **`index`**: Eksekusi query `$categories = TicketCategory::withCount('tickets')->paginate(10);`, kemudian return view melalui Inertia `return Inertia::render('Master/Category/Index', ['categories' => $categories]);`.
- **`create`**: Return halaman tambah data `return Inertia::render('Master/Category/Create');`.
- **`store`**: 
  - Lakukan validasi input: `name` (required, string, max:100) dan `description` (nullable, string).
  - Simpan ke database menggunakan Eloquent.
  - Redirect ke route `master.categories.index` dengan membawa flash message sukses.
- **`edit`**: Ambil detail kategori tertentu, lalu pass ke view `return Inertia::render('Master/Category/Edit', ['category' => $category]);`.
- **`update`**: Validasi field seperti metode `store`, lakukan update pada data terkait, kemudian redirect.
- **`toggleActive`**: Metode terpisah (PATCH). Update hanya pada field `is_active` (`$category->update(['is_active' => !$category->is_active]);`), lalu kembalikan menggunakan redirect back atau ke halaman index.

### 2.2 Buat View (Vue)
Buat folder `resources/js/Pages/Master/Category/` lalu buat 3 file Vue berikut:
1. `Index.vue`: Menampilkan tabel list data dari controller dengan pagination, badge untuk is_active, jumlah tiket (`tickets_count`), tombol menuju hal Edit, serta tombol spesifik untuk toggle mengaktifkan/menonaktifkan kategori. **TIDAK ADA TOMBOL HAPUS.**
2. `Create.vue`: Menyediakan form sederhana untuk input name dan description. Gunakan Inertia `useForm`.
3. `Edit.vue`: Form serupa untuk update data. Manfaatkan prop data bawaan kategori yang dipilih untuk prepopulate form.

---

## 3. Implementasi Task 4.2 — CRUD Prioritas Tiket

**Tujuan:** Mengelola tabel `ticket_priorities` dan visual badge yang berhubungan dengan level warna.

### 3.1 Buat Controller
Jalankan command: `php artisan make:controller Master/PriorityController`

Di dalam `PriorityController`:
- Struktur controllernya sangat mirip dengan `CategoryController`, perbedaannya terdapat pada validasi:
  - `name`: required, string, max:50.
  - `level`: required, integer, min:1, serta **unique** terhadap seluruh record yang ada di `ticket_priorities` agar levelnya tidak duplikat (saat update perlu exclude ID yang sedang diedit).
  - `color`: nullable, string, dan butuh validasi regex `/^#[0-9A-Fa-f]{6}$/` untuk format heksadesimal warna.
- Siapkan fungsi metode standar (`index`, `create`, `store`, `edit`, `update`, `toggleActive`). Sama halnya kategori, **TIDAK ADA FUNGSI HAPUS.**

### 3.2 Buat View (Vue)
Buat folder `resources/js/Pages/Master/Priority/` lalu lengkapi 3 file Vue:
1. `Index.vue`: Sajikan tabel data yang menampilkan nama, level, kotak atau badge warna dari kode hexadecimal (bisa via inline style `background-color:`), link Edit, serta toggle status aktif.
2. `Create.vue`: Sediakan input text untuk name dan level, dan integrasikan input spesifik bawaan browser `type="color"` untuk input warna.
3. `Edit.vue`: Form pengeditan yang menggunakan prop dan `useForm` untuk submit data.

---

## 4. Implementasi Task 4.3 — CRUD Unit Kerja

**Tujuan:** Mengelola `work_units` bersamaan dengan detail anggota `team_members`.

### 4.1 Buat Controller
Jalankan command: `php artisan make:controller Master/WorkUnitController`

Di dalam `WorkUnitController`:
- Siapkan fungsi CRUD biasa (`index`, `create`, `store`, `edit`, `update`, `toggleActive`) untuk tabel `work_units`.
  - Aturan validasinya adalah: `name` (required, string, max:100), `code` (required, string, max:20, secara spesifik menggunakan unique di tabel).
- **`members(WorkUnit $workUnit)`**: Sebuah halaman khusus menampilkan anggota tim untuk unit tertentu.
  - Lakukan load relation ke `team_members` yang membawa data detail dari tabel `users`.
  - Berikan juga daftar seluruh `$users` ke front-end agar dapat dipilih via dropdown dropdown select user saat penambahan.
  - Return `Inertia::render('Master/WorkUnit/Members')`.
- **`storeMember(Request $request, WorkUnit $workUnit)`**:
  - Validasi bahwa `user_id` memang exists/terdapat di tabel `users`.
  - Hindari agar tidak ada duplikasi record bagi satu user pada unit kerja yang sama (unique key atau syncWithoutDetaching).
  - Lakukan insert ke `team_members`.
  - **KRITIKAL (Berdasarkan dokumen instruksi 4.3):** Begitu selesai insert ke tabel pivot anggota tim, wajib melakukan update tabel users untuk admin: `User::where('id', $request->user_id)->update(['work_unit_id' => $workUnit->id]);`.
- **`destroyMember(WorkUnit $workUnit, User $user)`**:
  - Lepaskan / copot keanggotaan pengguna tersebut pada relasinya. Hapus baris dari `team_members`.

### 4.2 Buat View (Vue)
Buat folder `resources/js/Pages/Master/WorkUnit/`.
Di dalamnya siapkan: `Index.vue`, `Create.vue`, `Edit.vue`, dan satu view unik bernama `Members.vue`.
- Pada `Members.vue`, desain tabel khusus untuk menampilkan relasi pengguna di unit kerja, beserta opsi copot / Remove. Berikan juga satu form kecil / modal berisi `<select>` data seluruh user untuk menambahkan ke tim kerja.

---

## 5. Implementasi Task 4.4 — CRUD User & Role

**Tujuan:** Mengelola akun user `users`, melakukan reset password, mengaktifkan / nonaktifkan dan mapping tabel `model_has_roles`.

### 5.1 Buat Controller
Jalankan command: `php artisan make:controller Master/UserController`

Di dalam `UserController`:
- **`index`**: Muat list user disertai relasi `$users = User::with(['roles', 'workUnit'])->paginate(10);`. Sebaiknya sediakan query sederhana pencarian username / name.
- **`create`**: Load data master relasional via `$roles = Role::all()` serta `$workUnits = WorkUnit::where('is_active', 1)->get()` untuk populating dropdown opsi di halaman front-end form.
- **`store`**:
  - Validasikan field krusial: `username` (unique), `name`, `email` (unique, nullable), `password` (min:8), `role` (exists pada spati roles), `work_unit_id` (nullable, exists pada work_unit).
  - Gunakan `Hash::make()` (bcrypt) untuk password.
  - Simpan model User baru.
  - Hubungkan Role dengan memanggil metode Spatie: `$user->assignRole($request->role);`.
- **`edit`**: Analog dengan create, tetapi prepopulate seluruh object user termasuk password sebagai nullable agar bisa membiarkan user tak perlu mengganti password setiap kali edit profile detail yang tidak bersangkutan.
- **`update`**:
  - Bila input `$request->password` terisi, lakukan hash ulang, jika kosong, singkirkan saja isian `password` dari validasi.
  - Tetapkan relasi role dengan metode update: `$user->syncRoles([$request->role]);`.
- **`toggleActive`**:
  - **ATURAN LOGIKA KRUSIAL:** Ketika `$user->is_active == 1` akan dinonaktifkan menuju `0`, sistem WAJIB memeriksa apakah ia memegang tiket yang menuntut atensi (alias menjadi `handler_id` pada ticket).
  - Lakukan kueri `Ticket::where('handler_id', $user->id)->whereIn('status', ['in_progress', 'waiting_for_info', 'waiting_third_party', 'pending_approval'])->count();`
  - Apabila count kueri tersebut menghasilkan `> 0`, tampilkan validasi error Inertia / return flash redirect bahwa "User ini masih memegang n tiket aktif. Lakukan reassign tiket terlebih dulu sebelum mematikan status".
  - Bila aman, ubah flagnya kemudian sukses redirect.

### 5.2 Buat View (Vue)
Buat folder `resources/js/Pages/Master/User/`.
Di dalamnya siapkan: `Index.vue` dengan perwakilan tabel data akun user.
Dan dua form views berupa `Create.vue` dan `Edit.vue` dengan drop-down select option (unit kerja dan roles) serta field krusial password/username.

---

## 6. Implementasi Task 4.5 — Kelola Permission per Role

**Tujuan:** Bulk permission mapping dari Matrix Permission antar setiap roles secara dinamis.

### 6.1 Buat Controller
Jalankan command: `php artisan make:controller Master/PermissionController`

Di dalam `PermissionController`:
- **`index`**:
  - `$roles = Role::with('permissions')->get();`
  - `$permissions = Permission::all();`
  - Pass ke view `Inertia::render('Master/Permission/Index', ['roles' => $roles, 'permissions' => $permissions])`.
- **`update(Request $request)`**:
  - Endpoint ini menerima input berupa array nested structure. Misal `[role_id_1 => [permission_id_A, permission_id_B], role_id_2 => [...]]`.
  - Lewati looping setiap mapping id role, gunakan `$role->syncPermissions($arrayOfPermissionIds);` (fungsionalitas Spatie Permission).
  - **PENTING:** Sesudah mengubah role, Spatie cache WAJIB direset untuk mereflect hak akses terkini. Gunakan: `app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();`
  - Redirect kembali ke halaman Index permission.

### 6.2 Buat View (Vue)
Buat file tunggal `resources/js/Pages/Master/Permission/Index.vue`.
- Bangun layout Grid / Tabel Matriks 2 dimensi.
- Baris Vertikal = Nama Permission. Kolom Horizontal = Nama-nama Roles.
- Didalam isi selnya (di pertemuan row & cell) render input `<input type="checkbox">` yang mewakili ID Permission untuk target Role. Set secara prop nilai v-model awalnya.
- Saat melakukan Submit, paketkan struktur permission tersebut kembali ke controller dengan inertia `useForm`.

---

## 7. Quality Assurance (Checklist Developer)
[ ] Test login pakai `super_admin`. Menu tab Master Data harus nampak secara eksplisit di Sidebar navigasi.
[ ] Lakukan login pakai `pegawai`. Navigasi link maupun route `master.*` HARUS terblokir/403 karena middleware pengecek Spatie role permissions.
[ ] Usahakan form validation untuk string name yang duplicate dan code unit kerja yang duplicate melempar validasi Vue form dengan rapi.
[ ] Secara aktif menguji rule nonaktif user yang mempunyai status aktif memegang tiket, pastikan rule safety guard ini jalan dan memberikan peringatan sebelum is_active dibuang.
[ ] Setelah Permission Matriks disimpan via tombol save, jangan lupa cache telah tereset secepatnya sehingga policy access control sistem termutakhir.
