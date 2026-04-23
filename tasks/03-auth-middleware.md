# Fase 3 — Auth, Middleware, Profile

> **Tujuan:** Implementasi login, middleware cek aktif, dan halaman profil.
> **Referensi:** Dokumentasi Bag. 9 (Autentikasi)
> **Prasyarat:** Fase 2 selesai

---

## Task 3.1 — Halaman Login

**Apa yang dilakukan:**
Buat halaman login menggunakan username & password (bukan email).

**Referensi:** Dokumentasi Bag. 9

**File yang dibuat/diubah:**
1. `app/Http/Controllers/Auth/LoginController.php`
2. `resources/js/Pages/Auth/Login.vue`
3. `routes/web.php`

**Logika login:**
```php
// Di LoginController
public function login(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    $user = User::where('username', $request->username)->first();

    // Cek user ada
    if (!$user) {
        return back()->withErrors(['username' => 'Username tidak ditemukan']);
    }

    // Cek is_active SEBELUM cek password
    if (!$user->is_active) {
        return back()->withErrors(['username' => 'Akun Anda telah dinonaktifkan']);
    }

    // Cek password
    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors(['password' => 'Password salah']);
    }

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->intended('/dashboard');
}
```

**Rate limiting (5 percobaan per menit):**
```php
// Di routes/web.php
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 attempts per 1 minute
```

**Acceptance Criteria:**
- [ ] Halaman login menampilkan form username + password
- [ ] Login berhasil → redirect ke dashboard
- [ ] Login gagal (username salah) → tampil error
- [ ] Login gagal (password salah) → tampil error
- [ ] User nonaktif → tampil error "Akun dinonaktifkan"
- [ ] 6x percobaan gagal dalam 1 menit → throttled

---

## Task 3.2 — Middleware CheckActiveUser

**Apa yang dilakukan:**
Buat middleware yang cek `is_active` di setiap request. Jika user di-nonaktifkan saat sedang login, session dihentikan.

**Referensi:** Dokumentasi Bag. 9 (Perilaku user nonaktif)

**File:** `app/Http/Middleware/CheckActiveUser.php`

```php
class CheckActiveUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')
                ->withErrors(['username' => 'Akun Anda telah dinonaktifkan']);
        }

        return $next($request);
    }
}
```

**Daftarkan di `bootstrap/app.php`:**
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
        \App\Http\Middleware\CheckActiveUser::class,
    ]);
})
```

**Acceptance Criteria:**
- [ ] User aktif bisa mengakses halaman
- [ ] User yang di-nonaktifkan saat login → langsung di-logout di request berikutnya

---

## Task 3.3 — Logout

**Apa yang dilakukan:**
Implementasi fitur logout.

**File:** `app/Http/Controllers/Auth/LoginController.php` (tambah method)

```php
public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
}
```

**Route:**
```php
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');
```

**Acceptance Criteria:**
- [ ] Klik logout → session terhapus → redirect ke login
- [ ] Setelah logout, akses halaman protected → redirect ke login

---

## Task 3.4 — Layout Utama Aplikasi

**Apa yang dilakukan:**
Buat layout utama (sidebar + navbar + content area) yang dipakai semua halaman setelah login.

**File yang dibuat:**
1. `resources/js/Components/Layout/AppLayout.vue` — Layout wrapper
2. `resources/js/Components/Layout/Sidebar.vue` — Menu navigasi (sesuai role)
3. `resources/js/Components/Layout/Navbar.vue` — Top bar (nama user, notif, logout)

**Menu sidebar per role:**
| Menu | Permission yang dicek |
|------|----------------------|
| Dashboard | `dashboard.personal` / `dashboard.operational` |
| Tiket | `ticket.view` |
| Kategori | `master.category` |
| Prioritas | `master.priority` |
| Unit Kerja | `master.work-unit` |
| Users | `master.user` |
| Permission | `master.permission` |
| Laporan | `report.export` / `report.personal` |

**⚠️ Cara kirim data user + permissions ke frontend (HandleInertiaRequests middleware):**
```php
// Di HandleInertiaRequests.php -> share()
return [
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
];
```

**Acceptance Criteria:**
- [ ] Halaman setelah login menampilkan sidebar + navbar + content area
- [ ] Menu sidebar muncul sesuai permission user yang login
- [ ] Nama user + role muncul di navbar
- [ ] Tombol logout berfungsi

---

## Task 3.5 — Halaman Profil (Lihat & Edit)

**Apa yang dilakukan:**
Buat halaman profil dimana semua user bisa melihat dan mengedit data profilnya sendiri.

**Referensi:** Dokumentasi Bag. 4 (Management Profile)

**File yang dibuat:**
1. `app/Http/Controllers/ProfileController.php`
2. `resources/js/Pages/Profile/Edit.vue`

**Field yang bisa diedit:**
- Nama (`name`)
- Email (`email`) — opsional
- Avatar (`avatar`) — upload foto

**Field yang hanya tampil (read-only):**
- Username
- Role
- Unit Kerja

**Acceptance Criteria:**
- [ ] User bisa melihat profil sendiri
- [ ] User bisa edit nama, email, avatar
- [ ] Username dan role tampil tapi tidak bisa diedit
- [ ] Validasi: nama wajib, email format valid (jika diisi)

---

## Task 3.6 — Fitur Ganti Password

**Apa yang dilakukan:**
Buat fitur ganti password dengan validasi password lama.

**File:** `app/Http/Controllers/ProfileController.php` (tambah method)

**Field:**
- Password lama (wajib, dicek kecocokannya)
- Password baru (wajib, min 8 karakter)
- Konfirmasi password baru (wajib, harus sama dengan password baru)

```php
public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|min:8|confirmed',
    ]);

    if (!Hash::check($request->current_password, $request->user()->password)) {
        return back()->withErrors(['current_password' => 'Password lama salah']);
    }

    $request->user()->update([
        'password' => Hash::make($request->password),
    ]);

    return back()->with('success', 'Password berhasil diubah');
}
```

**Acceptance Criteria:**
- [ ] User bisa ganti password
- [ ] Password lama salah → tampil error
- [ ] Password baru < 8 karakter → tampil error
- [ ] Konfirmasi tidak cocok → tampil error
- [ ] Setelah ganti password, bisa login dengan password baru
