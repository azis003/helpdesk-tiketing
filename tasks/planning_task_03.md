# Planning: Fase 3 — Auth, Middleware, Layout, Profile

> **Estimasi:** 1 hari kerja
> **Prasyarat:** Fase 2 selesai (`php artisan migrate:fresh --seed` sukses, 6 roles + 31 permissions + 1 superadmin ada di DB)
> **Kode referensi:** Semua kode PHP/Vue ada di `tasks/03-auth-middleware.md` — salin persis dari sana.
> **Referensi dokumentasi:** Dokumentasi Bag. 9 (Autentikasi), Bag. 4 (Management Profile)

---

## Urutan Pengerjaan

```
Step 1 (LoginController) → Step 2 (Login.vue) → Step 3 (CheckActiveUser Middleware)
→ Step 4 (Logout) → Step 5 (HandleInertiaRequests Shared Data)
→ Step 6 (AppLayout + Sidebar + Navbar) → Step 7 (Profile + Ganti Password)
```

---

## Step 1 — Buat LoginController (Backend)

Buat controller untuk menangani login dengan **username & password** (bukan email).

```powershell
New-Item -ItemType Directory -Force -Path app/Http/Controllers/Auth
php artisan make:controller Auth/LoginController
```

File: `app/Http/Controllers/Auth/LoginController.php`

**Isi controller dengan 2 method:**

### 1a. Method `showLoginForm()`
- Return Inertia render ke `Auth/Login`
- Jika user sudah login (`Auth::check()`), redirect ke `/dashboard`

### 1b. Method `login(Request $request)`
Salin kode persis dari `03-auth-middleware.md` Task 3.1. Logika step-by-step:

1. Validate: `username` required string, `password` required string
2. Cari user: `User::where('username', $request->username)->first()`
3. Cek user ada → jika tidak: `back()->withErrors(['username' => 'Username tidak ditemukan'])`
4. **Cek `is_active` SEBELUM cek password** → jika `!$user->is_active`: `back()->withErrors(['username' => 'Akun Anda telah dinonaktifkan'])`
5. Cek password: `Hash::check($request->password, $user->password)` → jika salah: `back()->withErrors(['password' => 'Password salah'])`
6. `Auth::login($user)` + `$request->session()->regenerate()`
7. `return redirect()->intended('/dashboard')`

### 1c. Tambah routes di `routes/web.php`

```php
use App\Http\Controllers\Auth\LoginController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

// Redirect root ke login atau dashboard
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});
```

**⚠️ Hapus route `/test` dan `/` yang lama** (lihat `routes/web.php` saat ini yang masih return `inertia('Test')`).

**Import yang dibutuhkan di LoginController:**
```
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
```

### Validasi Step 1
```powershell
php artisan route:list --path=login
# Harus muncul: GET /login dan POST /login
```

---

## Step 2 — Buat Halaman Login (Frontend Vue)

File: `resources/js/Pages/Auth/Login.vue`

**Hapus file `.gitkeep`** di folder `resources/js/Pages/Auth/` sebelum membuat file baru.

**Komponen yang harus dibuat:**

Form login dengan 2 field:
- Input **username** (type text, autofocus)
- Input **password** (type password)
- Tombol **Login**

**Implementasi menggunakan Inertia.js `useForm`:**

```javascript
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    username: '',
    password: '',
})

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
```

**Fitur yang harus ada:**
- Tampilkan error per-field dari `form.errors.username` dan `form.errors.password`
- Tombol login disabled saat `form.processing`
- Loading indicator saat submit
- Desain yang bersih dan profesional (gunakan Tailwind CSS v4)
- Layout **full-page centered** (tidak menggunakan AppLayout karena belum login)

**Catatan styling:**
- Gunakan dark theme atau gradient background
- Card login centered di tengah layar
- Logo/judul "Helpdesk Tiketing BP2KOMDIGI" di atas form
- Responsive (mobile-friendly)

### Validasi Step 2
```powershell
php artisan serve
# Buka http://localhost:8000/login di browser
# - Form muncul dengan username + password
# - Login dengan superadmin / password → redirect ke /dashboard (404 OK, dashboard belum ada)
# - Login username salah → error "Username tidak ditemukan"
# - Login password salah → error "Password salah"
# - Login 6x gagal dalam 1 menit → throttled (429)
```

---

## Step 3 — Buat Middleware CheckActiveUser

File: `app/Http/Middleware/CheckActiveUser.php`

```powershell
php artisan make:middleware CheckActiveUser
```

**Salin kode persis dari `03-auth-middleware.md` Task 3.2.** Logika:
1. Jika `Auth::check()` dan `!Auth::user()->is_active`:
   - `Auth::logout()`
   - `$request->session()->invalidate()`
   - `$request->session()->regenerateToken()`
   - Redirect ke `/login` dengan error `'Akun Anda telah dinonaktifkan'`
2. Jika user aktif: `return $next($request)`

**Daftarkan di `bootstrap/app.php`:**

Edit file `bootstrap/app.php`, tambahkan `CheckActiveUser` di bawah `HandleInertiaRequests`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \App\Http\Middleware\CheckActiveUser::class,
    ]);
})
```

**⚠️ Urutan penting:** `HandleInertiaRequests` harus SEBELUM `CheckActiveUser`.

### Validasi Step 3
```powershell
# 1. Login sebagai superadmin
# 2. Di terminal, nonaktifkan user:
php artisan tinker --execute="App\Models\User::where('username','superadmin')->update(['is_active'=>0]);"
# 3. Refresh halaman → harus di-redirect ke /login dengan pesan "Akun dinonaktifkan"
# 4. Aktifkan kembali:
php artisan tinker --execute="App\Models\User::where('username','superadmin')->update(['is_active'=>1]);"
```

---

## Step 4 — Tambah Fitur Logout

**Tambah method `logout` di `app/Http/Controllers/Auth/LoginController.php`:**

Salin kode dari `03-auth-middleware.md` Task 3.3. Logika:
1. `Auth::logout()`
2. `$request->session()->invalidate()`
3. `$request->session()->regenerateToken()`
4. `return redirect('/login')`

**Tambah route di `routes/web.php`:**

```php
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
```

### Validasi Step 4
```powershell
# Login → klik logout → harus redirect ke /login
# Setelah logout → akses /dashboard → redirect ke /login
```

---

## Step 5 — Update HandleInertiaRequests (Shared Data)

Edit file: `app/Http/Middleware/HandleInertiaRequests.php`

Update method `share()` untuk mengirim data auth ke frontend. Salin kode dari `03-auth-middleware.md` Task 3.4.

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'username' => $request->user()->username,
                'avatar' => $request->user()->avatar,
                'role' => $request->user()->getRoleNames()->first(),
                'permissions' => $request->user()->getAllPermissions()->pluck('name'),
            ] : null,
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ];
}
```

**⚠️ Import tambahan:** Tidak perlu import baru, `Request` sudah di-import.

**Catatan:** `getRoleNames()` dan `getAllPermissions()` berasal dari trait `HasRoles` Spatie yang sudah ada di Model `User`.

### Validasi Step 5
```powershell
# Login → buka Vue Devtools → cek props $page.props.auth
# Harus ada: user.id, user.name, user.username, user.role, user.permissions (array)
```

---

## Step 6 — Buat Layout Utama (AppLayout + Sidebar + Navbar)

Buat 3 file Vue di `resources/js/Components/Layout/`:

**Hapus file `.gitkeep`** di folder Layout sebelum membuat file baru.

### 6a. `resources/js/Components/Layout/AppLayout.vue`

Layout wrapper yang membungkus semua halaman setelah login. Struktur:
```
+-------------------------------------+
| Navbar (top bar)                    |
+----------+--------------------------+
| Sidebar  | <slot /> (content area)  |
| (kiri)   |                          |
|          |                          |
+----------+--------------------------+
```

**Props:** Tidak perlu props khusus. Ambil data auth dari `usePage().props.auth`.

**Fitur:**
- Sidebar collapsible (toggle open/close) — simpan state di `ref`
- Responsive: di mobile sidebar overlay, di desktop sidebar fixed
- `<slot />` untuk render konten halaman

### 6b. `resources/js/Components/Layout/Sidebar.vue`

Menu navigasi kiri. **Tampilkan menu berdasarkan permission user.**

Ambil permissions dari `usePage().props.auth.user.permissions`.

**Helper function untuk cek permission:**
```javascript
const can = (permission) => {
    return usePage().props.auth.user.permissions.includes(permission)
}
```

**Daftar menu (dari `03-auth-middleware.md` Task 3.4):**

| Menu | Route | Permission yang dicek | Icon |
|------|-------|----------------------|------|
| Dashboard | `/dashboard` | `dashboard.personal` ATAU `dashboard.operational` | HomeIcon |
| Tiket | `/tickets` | `ticket.view` | TicketIcon |
| Kategori | `/master/categories` | `master.category` | TagIcon |
| Prioritas | `/master/priorities` | `master.priority` | FlagIcon |
| Unit Kerja | `/master/work-units` | `master.work-unit` | BuildingIcon |
| Users | `/master/users` | `master.user` | UsersIcon |
| Permission | `/master/permissions` | `master.permission` | ShieldIcon |
| Laporan | `/reports` | `report.export` ATAU `report.personal` | ChartIcon |

**Catatan icon:** Gunakan SVG inline atau Heroicons. Jika tidak mau install library icon, buat SVG sederhana atau gunakan emoji sebagai placeholder.

**Props yang diterima:**
- `collapsed` (Boolean) — apakah sidebar sedang collapsed

**Styling:**
- Dark sidebar (bg gelap, text putih)
- Active menu highlight (berdasarkan `usePage().url`)
- Hover effect pada menu item
- Smooth transition saat collapse/expand

### 6c. `resources/js/Components/Layout/Navbar.vue`

Top bar. Menampilkan:
1. Tombol toggle sidebar (hamburger icon)
2. Judul aplikasi: "Helpdesk Tiketing"
3. Di kanan: **Nama user + Role** (dari `usePage().props.auth.user`)
4. Tombol/dropdown **Logout** — submit POST ke `/logout` via Inertia `router.post('/logout')`

**Events yang di-emit:**
- `toggle-sidebar` — saat hamburger diklik

**Fitur logout di Navbar:**
```javascript
import { router } from '@inertiajs/vue3'

const logout = () => {
    router.post('/logout')
}
```

### 6d. Buat halaman Dashboard placeholder

File: `resources/js/Pages/Dashboard/Index.vue`

**Hapus file `.gitkeep`** di folder Dashboard sebelum membuat file baru.

Halaman sederhana yang menampilkan:
- Menggunakan `AppLayout` sebagai wrapper
- Heading "Dashboard"
- Teks: "Selamat datang, {{ auth.user.name }}! ({{ auth.user.role }})"
- Placeholder card sederhana

**Tambah route di `routes/web.php`:**
```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Dashboard/Index');
    })->name('dashboard');
});
```

### Validasi Step 6
```powershell
# Login sebagai superadmin → redirect ke /dashboard
# - Sidebar muncul dengan menu sesuai permission super_admin:
#   Dashboard, Tiket, Kategori, Prioritas, Unit Kerja, Users, Permission, Laporan
# - Navbar menampilkan "Super Administrator" + "super_admin"
# - Tombol logout berfungsi
# - Sidebar bisa di-toggle (collapse/expand)
```

---

## Step 7 — Halaman Profil + Ganti Password

### 7a. Buat ProfileController

```powershell
php artisan make:controller ProfileController
```

File: `app/Http/Controllers/ProfileController.php`

**3 method:**

**`edit()`** — menampilkan halaman profil:
```php
public function edit(Request $request)
{
    $user = $request->user()->load('workUnit');
    return inertia('Profile/Edit', [
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->getRoleNames()->first(),
            'work_unit' => $user->workUnit?->name,
        ],
    ]);
}
```

**`update(Request $request)`** — update profil (name, email, avatar):

Validasi:
- `name` → required, string, max:100
- `email` → nullable, email, max:100, unique:users,email,{id user saat ini}
- `avatar` → nullable, image, max:2048 (2MB)

Logika avatar upload:
1. Jika ada file avatar baru → simpan ke `storage/app/public/avatars/`
2. Hapus avatar lama jika ada (`Storage::disk('public')->delete(...)`)
3. Simpan path relatif di kolom `avatar`

Return: `back()->with('success', 'Profil berhasil diperbarui')`

**`updatePassword(Request $request)`** — ganti password:

Salin kode persis dari `03-auth-middleware.md` Task 3.6:
1. Validate: `current_password` required, `password` required|min:8|confirmed
2. Cek password lama: `Hash::check($request->current_password, $request->user()->password)`
3. Jika salah: `back()->withErrors(['current_password' => 'Password lama salah'])`
4. Update: `$request->user()->update(['password' => Hash::make($request->password)])`
5. Return: `back()->with('success', 'Password berhasil diubah')`

### 7b. Buat halaman Profile/Edit.vue

File: `resources/js/Pages/Profile/Edit.vue`

**Hapus file `.gitkeep`** di folder Profile sebelum membuat file baru.

**Menggunakan `AppLayout` sebagai wrapper.**

**2 section/card dalam 1 halaman:**

**Card 1: Edit Profil**
- Field yang **bisa diedit**: Nama (text), Email (email, opsional), Avatar (file upload dengan preview)
- Field yang **read-only** (tampil tapi disabled): Username, Role, Unit Kerja
- Tombol "Simpan Profil" → submit via Inertia useForm

Gunakan `useForm` dari Inertia:
```javascript
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email || '',
    avatar: null,
    _method: 'PUT', // Method spoofing untuk Laravel
})

const submitProfile = () => {
    profileForm.post('/profile', {
        forceFormData: true,
        preserveScroll: true,
    })
}
```

**Card 2: Ganti Password**
- Field: Password lama, Password baru, Konfirmasi password baru
- Tombol "Ubah Password"

```javascript
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submitPassword = () => {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}
```

**Tampilkan flash message sukses** dari `usePage().props.flash.success`.

### 7c. Tambah routes di `routes/web.php`

Tambahkan di dalam group `Route::middleware('auth')`:

```php
use App\Http\Controllers\ProfileController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Dashboard/Index');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
```

**⚠️ Jangan lupa:** Pastikan symbolic link storage sudah dibuat untuk akses avatar:
```powershell
php artisan storage:link
```

### 7d. Tambah link Profil di Navbar

Edit `Navbar.vue`: tambahkan link ke `/profile` di area user info (dropdown atau link langsung di samping nama user).

### Validasi Step 7
```powershell
# 1. Login → klik profil di navbar → halaman profil muncul
# 2. Edit nama → simpan → berhasil, nama terupdate
# 3. Upload avatar → simpan → berhasil, avatar muncul
# 4. Username dan Role tampil tapi tidak bisa diedit
# 5. Ganti password:
#    - Password lama salah → error
#    - Password baru < 8 karakter → error
#    - Konfirmasi tidak cocok → error
#    - Semua valid → sukses, bisa login dengan password baru
```

---

## Validasi Akhir Fase 3

```powershell
php artisan route:list
# Harus ada routes:
# GET  /login            → LoginController@showLoginForm
# POST /login            → LoginController@login (throttle:5,1)
# POST /logout           → LoginController@logout (auth)
# GET  /dashboard        → Closure (auth)
# GET  /profile          → ProfileController@edit (auth)
# PUT  /profile          → ProfileController@update (auth)
# PUT  /profile/password → ProfileController@updatePassword (auth)
```

### Checklist Final
- [ ] `app/Http/Controllers/Auth/LoginController.php` — 3 method: showLoginForm, login, logout
- [ ] `resources/js/Pages/Auth/Login.vue` — form username + password, error handling
- [ ] `app/Http/Middleware/CheckActiveUser.php` — cek is_active setiap request
- [ ] `bootstrap/app.php` — CheckActiveUser terdaftar setelah HandleInertiaRequests
- [ ] `app/Http/Middleware/HandleInertiaRequests.php` — share auth data + flash messages
- [ ] `resources/js/Components/Layout/AppLayout.vue` — wrapper layout (sidebar + navbar + slot)
- [ ] `resources/js/Components/Layout/Sidebar.vue` — menu berdasarkan permission
- [ ] `resources/js/Components/Layout/Navbar.vue` — nama user, role, logout, toggle sidebar, link profil
- [ ] `resources/js/Pages/Dashboard/Index.vue` — placeholder dashboard
- [ ] `app/Http/Controllers/ProfileController.php` — 3 method: edit, update, updatePassword
- [ ] `resources/js/Pages/Profile/Edit.vue` — edit profil + ganti password
- [ ] `routes/web.php` — semua route terdaftar dengan middleware yang benar
- [ ] Login username/password berfungsi
- [ ] Rate limiting 5 percobaan/menit berfungsi
- [ ] User nonaktif tidak bisa login + di-kick saat session aktif
- [ ] Logout berfungsi (session terhapus)
- [ ] Menu sidebar muncul sesuai permission
- [ ] Profil bisa diedit (nama, email, avatar)
- [ ] Ganti password berfungsi dengan validasi

---

## Daftar Semua File yang Dibuat/Diubah

| File | Aksi |
|------|------|
| `app/Http/Controllers/Auth/LoginController.php` | Buat baru |
| `resources/js/Pages/Auth/Login.vue` | Buat baru |
| `app/Http/Middleware/CheckActiveUser.php` | Buat baru |
| `bootstrap/app.php` | Edit — tambah CheckActiveUser |
| `app/Http/Middleware/HandleInertiaRequests.php` | Edit — tambah shared data auth + flash |
| `resources/js/Components/Layout/AppLayout.vue` | Buat baru |
| `resources/js/Components/Layout/Sidebar.vue` | Buat baru |
| `resources/js/Components/Layout/Navbar.vue` | Buat baru |
| `resources/js/Pages/Dashboard/Index.vue` | Buat baru |
| `app/Http/Controllers/ProfileController.php` | Buat baru |
| `resources/js/Pages/Profile/Edit.vue` | Buat baru |
| `routes/web.php` | Edit — tambah semua route auth + profile |

**Total: 8 file baru + 4 file diedit = 12 file**
