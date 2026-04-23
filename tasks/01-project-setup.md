# Fase 1 — Project Setup

> **Tujuan:** Menyiapkan project Laravel 12 dengan semua dependencies yang dibutuhkan.
> **Referensi:** Dokumentasi Bag. 3 (Stack Teknologi)

---

## Task 1.1 — Inisialisasi Project Laravel 12

**Apa yang dilakukan:**
Buat project Laravel 12 baru menggunakan Laravel Installer atau Composer.

**Langkah:**
```bash
# Opsi 1: Pakai Laravel Installer
laravel new helpdesk-tiketing

# Opsi 2: Pakai Composer
composer create-project laravel/laravel helpdesk-tiketing
```

**Acceptance Criteria:**
- [ ] Folder project terbuat
- [ ] `php artisan serve` berjalan tanpa error
- [ ] Browser menampilkan halaman welcome Laravel

---

## Task 1.2 — Install Vue 3 + Inertia.js

**Apa yang dilakukan:**
Install dan konfigurasi Inertia.js sebagai bridge antara Laravel (backend) dan Vue 3 (frontend).

**Langkah:**
```bash
# 1. Install Inertia server-side (Laravel)
composer require inertiajs/inertia-laravel

# 2. Setup Inertia middleware
php artisan inertia:middleware

# 3. Daftarkan middleware di bootstrap/app.php

# 4. Install Inertia client-side + Vue 3
npm install @inertiajs/vue3 vue@3

# 5. Install Vite plugin
npm install @vitejs/plugin-vue
```

**File yang perlu diubah/buat:**
1. `bootstrap/app.php` — tambahkan `HandleInertiaRequests` middleware
2. `resources/views/app.blade.php` — buat root template Inertia
3. `resources/js/app.js` — setup Vue 3 + Inertia
4. `vite.config.js` — tambahkan plugin Vue

**Contoh `resources/views/app.blade.php`:**
```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

**Contoh `resources/js/app.js`:**
```js
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
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

**Acceptance Criteria:**
- [ ] Buat file `resources/js/Pages/Test.vue` dengan teks "Hello Inertia"
- [ ] Buat route: `Route::get('/test', fn() => inertia('Test'));`
- [ ] Browser di `/test` menampilkan "Hello Inertia"

---

## Task 1.3 — Install & Setup Tailwind CSS v4

**Apa yang dilakukan:**
Install Tailwind CSS v4 untuk styling.

**Langkah:**
```bash
npm install tailwindcss @tailwindcss/vite
```

**File yang diubah:**
1. `vite.config.js` — tambahkan plugin `@tailwindcss/vite`
2. `resources/css/app.css` — tambahkan `@import "tailwindcss";`

**Acceptance Criteria:**
- [ ] Class Tailwind (misal `text-red-500`, `bg-blue-100`) bekerja di Vue component
- [ ] Styling ter-render dengan benar di browser

---

## Task 1.4 — Install SweetAlert2

**Apa yang dilakukan:**
Install SweetAlert2 untuk dialog/alert UI.

**Langkah:**
```bash
npm install sweetalert2
```

**Acceptance Criteria:**
- [ ] Bisa import dan panggil `Swal.fire('Hello!')` di Vue component

---

## Task 1.5 — Install Spatie Permission

**Apa yang dilakukan:**
Install package Laravel Spatie Permission untuk manajemen role & permission.

**Langkah:**
```bash
# 1. Install package
composer require spatie/laravel-permission

# 2. Publish config & migration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 3. Jalankan migration (nanti di Fase 2, jangan jalankan dulu)
```

**File yang diubah:**
1. `app/Models/User.php` — tambahkan trait `HasRoles`

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    // ...
}
```

**Acceptance Criteria:**
- [ ] File config `config/permission.php` sudah ada
- [ ] File migration Spatie sudah ada di `database/migrations/`
- [ ] Model User punya trait `HasRoles`

---

## Task 1.6 — Setup Database MySQL

**Apa yang dilakukan:**
Konfigurasi koneksi database MySQL di file `.env`.

**Langkah:**
1. Buat database baru di MySQL: `helpdesk_tiketing`
2. Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdesk_tiketing
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Acceptance Criteria:**
- [ ] `php artisan migrate` berhasil tanpa error (jalankan migration default Laravel + Spatie)
- [ ] Tabel `users`, `roles`, `permissions`, `model_has_roles`, `role_has_permissions`, `model_has_permissions` sudah ada di database

---

## Task 1.7 — Setup Laravel Queue (Database Driver)

**Apa yang dilakukan:**
Konfigurasi Laravel Queue dengan database driver untuk menjalankan notifikasi secara async.

**Referensi:** Dokumentasi Bag. 14.2

**Langkah:**
```bash
# 1. Buat tabel jobs
php artisan queue:table
php artisan migrate

# 2. Edit .env
# QUEUE_CONNECTION=database
```

**File yang diubah:**
1. `.env` — set `QUEUE_CONNECTION=database`

**Acceptance Criteria:**
- [ ] Tabel `jobs`, `job_batches`, `failed_jobs` sudah ada di database
- [ ] `php artisan queue:work` berjalan tanpa error

---

## Task 1.8 — Setup Folder Structure

**Apa yang dilakukan:**
Buat struktur folder untuk frontend Vue agar rapi.

**Folder yang dibuat:**
```
resources/js/
├── Pages/               # Halaman Inertia (per modul)
│   ├── Auth/            # Login
│   ├── Dashboard/       # Dashboard pages
│   ├── Master/          # CRUD master data
│   │   ├── Category/
│   │   ├── Priority/
│   │   ├── WorkUnit/
│   │   └── User/
│   ├── Ticket/          # Ticket pages
│   ├── Notification/    # Notification inbox
│   └── Profile/         # Profile management
├── Components/          # Reusable Vue components
│   ├── Layout/          # App layout, sidebar, navbar
│   └── UI/              # Button, Modal, Badge, dll
├── Composables/         # Vue composables (reusable logic)
└── Enums/               # Frontend enums (mirror backend)
```

**Acceptance Criteria:**
- [ ] Semua folder sudah dibuat
- [ ] Bisa membuat Vue component di setiap folder tanpa error
