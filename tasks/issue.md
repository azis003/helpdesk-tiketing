# Issue: Fase 1 — Project Setup (Helpdesk Tiketing BP2KOMDIGI)

> **Prioritas:** High
> **Estimasi:** 1 hari kerja
> **Prasyarat:** PHP 8.2+, Composer, Node.js 18+, MySQL 8.0+, Git
> **Hasil akhir:** Project Laravel 12 yang bisa diakses di browser dengan Vue 3 + Inertia.js + Tailwind CSS v4 terkonfigurasi

---

## Konteks Singkat

Aplikasi Helpdesk Tiketing untuk manajemen tiket kendala TI di BP2KOMDIGI. Stack: **Laravel 12 + Vue 3 (Composition API) + Inertia.js + Tailwind CSS v4 + MySQL + Spatie Permission**. Autentikasi session-based (bukan API token). Queue pakai database driver.

---

## Urutan Pengerjaan

Kerjakan **berurutan** dari Task 1 sampai Task 8. Jangan skip atau paralel.

```
Task 1 (Laravel) → Task 2 (Vue+Inertia) → Task 3 (Tailwind) → Task 4 (SweetAlert2)
→ Task 5 (Spatie) → Task 6 (Database) → Task 7 (Queue) → Task 8 (Folder Structure)
```

---

## Task 1 — Inisialisasi Project Laravel 12

### Perintah

```bash
# Masuk ke direktori parent project (sesuaikan path)
cd C:\Users\Personal\Herd

# Buat project baru (jika folder sudah ada dari git clone, jalankan di dalam folder)
composer create-project laravel/laravel helpdesk-tiketing
```

> **⚠️ PENTING:** Jika folder `helpdesk-tiketing` sudah ada (misal dari git clone), jalankan dari dalam folder:
> ```bash
> cd helpdesk-tiketing
> composer create-project laravel/laravel .
> ```

### Validasi

```bash
cd helpdesk-tiketing
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser → harus tampil halaman welcome Laravel.

### Checklist
- [ ] Folder project terbuat dengan struktur Laravel lengkap
- [ ] `php artisan serve` berjalan tanpa error
- [ ] Browser menampilkan halaman welcome Laravel

---

## Task 2 — Install Vue 3 + Inertia.js

### 2a. Install Server-Side (Laravel)

```bash
composer require inertiajs/inertia-laravel
php artisan inertia:middleware
```

### 2b. Daftarkan Middleware

Buka file `bootstrap/app.php`, tambahkan middleware `HandleInertiaRequests`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### 2c. Install Client-Side

```bash
npm install @inertiajs/vue3 vue@3
npm install @vitejs/plugin-vue
```

### 2d. Buat Root Template Inertia

Buat/timpa file `resources/views/app.blade.php`:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Helpdesk Tiketing</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

### 2e. Setup Entry Point Vue + Inertia

Timpa file `resources/js/app.js`:

```js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
    title: (title) => title ? `${title} — Helpdesk Tiketing` : 'Helpdesk Tiketing',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
```

### 2f. Konfigurasi Vite

Timpa file `vite.config.js`:

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
})
```

### 2g. Buat Halaman Test

Buat file `resources/js/Pages/Test.vue`:

```vue
<script setup>
</script>

<template>
    <div style="display:flex;align-items:center;justify-content:center;min-height:100vh;">
        <h1>Hello Inertia — Helpdesk Tiketing</h1>
    </div>
</template>
```

### 2h. Buat Route Test

Buka file `routes/web.php`, ganti isinya:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Test');
});

Route::get('/test', function () {
    return inertia('Test');
});
```

### Validasi

```bash
# Terminal 1 — Jalankan Vite dev server
npm run dev

# Terminal 2 — Jalankan Laravel
php artisan serve
```

Buka `http://127.0.0.1:8000/test` → harus tampil teks "Hello Inertia — Helpdesk Tiketing".

> **⚠️ Troubleshooting:** Jika blank page, periksa console browser untuk error. Pastikan `npm run dev` berjalan di terminal terpisah.

### Checklist
- [ ] `composer require inertiajs/inertia-laravel` sukses
- [ ] `HandleInertiaRequests` middleware terdaftar di `bootstrap/app.php`
- [ ] File `resources/views/app.blade.php` sudah dibuat
- [ ] File `resources/js/app.js` sudah dikonfigurasi
- [ ] File `vite.config.js` sudah ada plugin Vue
- [ ] Halaman `/test` menampilkan "Hello Inertia — Helpdesk Tiketing"

---

## Task 3 — Install & Setup Tailwind CSS v4

### 3a. Install

```bash
npm install tailwindcss @tailwindcss/vite
```

### 3b. Tambah Plugin Tailwind di Vite

Update `vite.config.js` — tambahkan plugin tailwindcss:

```js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
})
```

### 3c. Update CSS

Timpa file `resources/css/app.css`:

```css
@import "tailwindcss";
```

### 3d. Test Tailwind

Update file `resources/js/Pages/Test.vue` untuk menggunakan class Tailwind:

```vue
<script setup>
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-blue-600 mb-4">
                Hello Inertia — Helpdesk Tiketing
            </h1>
            <p class="text-lg text-gray-600">
                Tailwind CSS v4 berhasil terkonfigurasi! ✅
            </p>
        </div>
    </div>
</template>
```

### Validasi

Refresh browser di `/test`. Teks harus berwarna biru (`text-blue-600`) dengan background abu-abu (`bg-gray-100`).

### Checklist
- [ ] `npm install tailwindcss @tailwindcss/vite` sukses
- [ ] Plugin `tailwindcss()` ditambahkan di `vite.config.js`
- [ ] `resources/css/app.css` berisi `@import "tailwindcss"`
- [ ] Class Tailwind bekerja dan ter-render di browser

---

## Task 4 — Install SweetAlert2

### 4a. Install

```bash
npm install sweetalert2
```

### 4b. Test SweetAlert2

Update `resources/js/Pages/Test.vue`:

```vue
<script setup>
import Swal from 'sweetalert2'

const showAlert = () => {
    Swal.fire({
        title: 'Berhasil!',
        text: 'SweetAlert2 berfungsi dengan baik.',
        icon: 'success',
        confirmButtonText: 'OK'
    })
}
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-blue-600 mb-4">
                Hello Inertia — Helpdesk Tiketing
            </h1>
            <p class="text-lg text-gray-600 mb-6">
                Tailwind CSS v4 berhasil terkonfigurasi! ✅
            </p>
            <button
                @click="showAlert"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                Test SweetAlert2
            </button>
        </div>
    </div>
</template>
```

### Validasi

Klik tombol "Test SweetAlert2" → harus muncul dialog SweetAlert2 dengan ikon sukses.

### Checklist
- [ ] `npm install sweetalert2` sukses
- [ ] Klik tombol menampilkan dialog SweetAlert2

---

## Task 5 — Install Spatie Permission

### 5a. Install Package

```bash
composer require spatie/laravel-permission
```

### 5b. Publish Config & Migration

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Ini akan membuat:
- `config/permission.php`
- File migration di `database/migrations/` (biasanya `xxxx_xx_xx_create_permission_tables.php`)

### 5c. Tambah Trait HasRoles di Model User

Buka file `app/Models/User.php`, tambahkan trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

> **⚠️ JANGAN jalankan `php artisan migrate` di task ini.** Migrasi akan dijalankan di Task 6 bersama setup database.

### Checklist
- [ ] `composer require spatie/laravel-permission` sukses
- [ ] File `config/permission.php` ada
- [ ] File migration Spatie ada di `database/migrations/`
- [ ] `app/Models/User.php` punya `use HasRoles`

---

## Task 6 — Setup Database MySQL

### 6a. Buat Database

Buka MySQL client (phpMyAdmin, MySQL CLI, atau tool lain), buat database:

```sql
CREATE DATABASE helpdesk_tiketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6b. Konfigurasi `.env`

Buka file `.env` di root project, edit bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_tiketing
DB_USERNAME=root
DB_PASSWORD=
```

> **⚠️** Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan konfigurasi MySQL lokal kamu. Jika pakai Laravel Herd, biasanya `root` tanpa password.

### 6c. Jalankan Migration

```bash
php artisan migrate
```

### Validasi

Cek di database, tabel-tabel berikut harus sudah ada:
- `users` (bawaan Laravel)
- `password_reset_tokens` (bawaan Laravel)
- `sessions` (bawaan Laravel)
- `cache`, `cache_locks` (bawaan Laravel)
- `roles` (Spatie)
- `permissions` (Spatie)
- `model_has_roles` (Spatie)
- `role_has_permissions` (Spatie)
- `model_has_permissions` (Spatie)
- `migrations` (tracking Laravel)

```bash
php artisan migrate:status
```

Semua migration harus berstatus `Ran`.

### Checklist
- [ ] Database `helpdesk_tiketing` sudah dibuat
- [ ] `.env` sudah dikonfigurasi
- [ ] `php artisan migrate` sukses tanpa error
- [ ] Tabel `users`, `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions` ada di database

---

## Task 7 — Setup Laravel Queue (Database Driver)

### 7a. Buat Tabel Queue

```bash
php artisan queue:table
php artisan migrate
```

### 7b. Konfigurasi `.env`

Buka file `.env`, ubah `QUEUE_CONNECTION`:

```env
QUEUE_CONNECTION=database
```

### Validasi

```bash
# Cek tabel queue sudah ada
php artisan migrate:status

# Test queue worker bisa jalan (Ctrl+C untuk stop)
php artisan queue:work
```

Output yang diharapkan saat `queue:work`:
```
INFO  Processing jobs from the [default] queue.
```

Tekan `Ctrl+C` untuk menghentikan worker.

### Checklist
- [ ] Tabel `jobs`, `job_batches`, `failed_jobs` ada di database
- [ ] `.env` — `QUEUE_CONNECTION=database`
- [ ] `php artisan queue:work` berjalan tanpa error

---

## Task 8 — Setup Folder Structure Frontend

### 8a. Buat Semua Folder

Jalankan perintah berikut untuk membuat folder structure:

**Windows (PowerShell):**
```powershell
# Pages
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Auth"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Dashboard"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Master/Category"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Master/Priority"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Master/WorkUnit"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Master/User"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Ticket"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Notification"
New-Item -ItemType Directory -Force -Path "resources/js/Pages/Profile"

# Components
New-Item -ItemType Directory -Force -Path "resources/js/Components/Layout"
New-Item -ItemType Directory -Force -Path "resources/js/Components/UI"

# Composables & Enums
New-Item -ItemType Directory -Force -Path "resources/js/Composables"
New-Item -ItemType Directory -Force -Path "resources/js/Enums"
```

### 8b. Buat File `.gitkeep` di Setiap Folder Kosong

Agar Git melacak folder kosong, buat `.gitkeep` di setiap folder:

**Windows (PowerShell):**
```powershell
$folders = @(
    "resources/js/Pages/Auth",
    "resources/js/Pages/Dashboard",
    "resources/js/Pages/Master/Category",
    "resources/js/Pages/Master/Priority",
    "resources/js/Pages/Master/WorkUnit",
    "resources/js/Pages/Master/User",
    "resources/js/Pages/Ticket",
    "resources/js/Pages/Notification",
    "resources/js/Pages/Profile",
    "resources/js/Components/Layout",
    "resources/js/Components/UI",
    "resources/js/Composables",
    "resources/js/Enums"
)

foreach ($folder in $folders) {
    New-Item -ItemType File -Force -Path "$folder/.gitkeep"
}
```

### 8c. Struktur Akhir yang Diharapkan

```
resources/js/
├── app.js                    ← sudah ada (Task 2)
├── Pages/
│   ├── Test.vue              ← sudah ada (Task 2, boleh dihapus nanti)
│   ├── Auth/                 ← Login page (Fase 3)
│   ├── Dashboard/            ← Dashboard pages (Fase 11)
│   ├── Master/               ← CRUD master data (Fase 4)
│   │   ├── Category/
│   │   ├── Priority/
│   │   ├── WorkUnit/
│   │   └── User/
│   ├── Ticket/               ← Ticket pages (Fase 5-8)
│   ├── Notification/         ← Notification inbox (Fase 9)
│   └── Profile/              ← Profile management (Fase 3)
├── Components/
│   ├── Layout/               ← App layout, sidebar, navbar
│   └── UI/                   ← Button, Modal, Badge, dll
├── Composables/              ← Vue composables (reusable logic)
└── Enums/                    ← Frontend enums (mirror backend)
```

### Validasi

Pastikan semua folder sudah ada:

```powershell
Get-ChildItem -Path "resources/js" -Recurse -Directory | Select-Object FullName
```

### Checklist
- [ ] Semua folder sudah dibuat sesuai struktur
- [ ] File `.gitkeep` ada di setiap folder kosong
- [ ] Tidak ada error saat membuat Vue component di folder manapun

---

## Validasi Akhir Fase 1

Setelah semua task selesai, lakukan validasi menyeluruh:

### 1. Jalankan Server

```bash
# Terminal 1
npm run dev

# Terminal 2
php artisan serve
```

### 2. Cek Browser

- Buka `http://127.0.0.1:8000/test` → tampil halaman dengan styling Tailwind + tombol SweetAlert2
- Klik tombol → dialog SweetAlert2 muncul

### 3. Cek Database

```bash
php artisan migrate:status
```

Semua migration berstatus `Ran`.

### 4. Cek Queue

```bash
php artisan queue:work --stop-when-empty
```

Tidak ada error.

### 5. Cek Dependencies

```bash
composer show inertiajs/inertia-laravel
composer show spatie/laravel-permission
npm list vue @inertiajs/vue3 tailwindcss sweetalert2
```

Semua package terinstall.

---

## Ringkasan File yang Dibuat/Diubah

| File | Aksi | Task |
|------|------|------|
| `bootstrap/app.php` | Diubah — tambah HandleInertiaRequests middleware | Task 2 |
| `resources/views/app.blade.php` | Dibuat baru — root template Inertia | Task 2 |
| `resources/js/app.js` | Diubah — setup Vue 3 + Inertia | Task 2 |
| `vite.config.js` | Diubah — tambah plugin Vue + Tailwind | Task 2, 3 |
| `resources/css/app.css` | Diubah — import Tailwind | Task 3 |
| `resources/js/Pages/Test.vue` | Dibuat baru — halaman test | Task 2, 3, 4 |
| `routes/web.php` | Diubah — tambah route test | Task 2 |
| `app/Models/User.php` | Diubah — tambah trait HasRoles | Task 5 |
| `.env` | Diubah — database + queue config | Task 6, 7 |
| `resources/js/Pages/*/` | Dibuat baru — folder structure | Task 8 |
| `resources/js/Components/*/` | Dibuat baru — folder structure | Task 8 |
| `resources/js/Composables/` | Dibuat baru — folder structure | Task 8 |
| `resources/js/Enums/` | Dibuat baru — folder structure | Task 8 |

---

## Catatan untuk Implementor

1. **Jangan hapus file `Test.vue`** setelah Fase 1 selesai — akan diganti nanti di Fase 3 (Auth) dengan halaman login
2. **Jangan jalankan migrasi custom** — migrasi tabel bisnis (tickets, dll) akan dibuat di Fase 2
3. **Simpan file `.env`** di `.gitignore` (sudah default Laravel) — jangan commit credentials
4. **Pastikan `npm run dev` tetap berjalan** saat development — Vite perlu aktif untuk hot-reload
5. **Setelah Fase 1 selesai**, lanjut ke Fase 2 (`tasks/02-database.md`) untuk membuat migration, model, enum, dan seeder

---

## Dependency Versions (Referensi)

| Package | Minimum Version |
|---------|----------------|
| PHP | 8.2+ |
| Laravel | 12.x |
| Vue | 3.x |
| @inertiajs/vue3 | 2.x |
| Tailwind CSS | 4.x |
| SweetAlert2 | 11.x |
| spatie/laravel-permission | 6.x |
| Node.js | 18+ |
| MySQL | 8.0+ |
