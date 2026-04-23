# Fase 12 — Testing & Polish

> **Tujuan:** Validasi keseluruhan sistem, perbaikan bug, dan finalisasi.
> **Prasyarat:** Fase 11 selesai

---

## Task 12.1 — Test Alur Lengkap (Happy Path)

**Apa yang dilakukan:**
Test alur tiket dari awal sampai akhir secara manual.

**Skenario 1: Alur normal**
```
1. Login sebagai Pegawai → buat tiket → cek status Open
2. Login sebagai Helpdesk → verifikasi tiket → cek status Verification
3. Helpdesk assign ke Teknisi → cek status In Progress, handler terisi
4. Login sebagai Teknisi → tandai selesai → cek status Resolved
5. Login sebagai Pegawai → tutup tiket → cek status Closed
6. Cek durasi pengerjaan tampil di detail
```

**Skenario 2: Klarifikasi**
```
1. Tiket In Progress → Teknisi minta klarifikasi → cek status WFI
2. Cek timer di-pause (ticket_time_logs terisi)
3. Login Pegawai → balas klarifikasi → cek status In Progress
4. Cek timer di-resume (total_paused_seconds terupdate)
```

**Skenario 3: Reject di verifikasi**
```
1. Tiket Open → Helpdesk verifikasi → Helpdesk reject
2. Cek status langsung Closed
3. Cek started_at tetap NULL
4. Cek pelapor dapat notifikasi
```

**Skenario 4: Approval Manager IT (reject)**
```
1. Tiket In Progress → Handler request approval
2. Manager IT reject dengan alasan
3. Cek status langsung Closed
4. Cek 2 record ticket_histories (rejected + rejected_closed)
```

**Skenario 5: Auto-close**
```
1. Buat tiket sampai Resolved
2. Jalankan: php artisan tickets:auto-close-resolved
3. Cek tiket masih open (belum 72 jam)
4. Ubah auto_close_at ke masa lalu
5. Jalankan ulang command → cek tiket tertutup
```

**Skenario 6: ReOpen**
```
1. Tiket Resolved → Pegawai reopen → cek status In Progress
2. Cek handler_id tidak berubah
3. Cek started_at tidak berubah
4. Cek resolved_at dan auto_close_at = NULL
```

**Acceptance Criteria:**
- [ ] Semua 6 skenario berhasil tanpa error
- [ ] Audit trail lengkap di setiap skenario
- [ ] Notifikasi terkirim ke penerima yang benar

---

## Task 12.2 — Test Scope Visibilitas

```
1. Buat 3 tiket dari user berbeda (Pegawai A, Pegawai B dari unit berbeda)
2. Login Pegawai A → hanya lihat tiket sendiri
3. Login Ketua Tim → lihat tiket anggota timnya
4. Login Helpdesk → lihat semua tiket
5. Login Teknisi yang di-assign 1 tiket → hanya lihat tiket itu
6. Login Manager IT → lihat semua tiket
7. Login Super Admin → lihat semua tiket, tapi tidak bisa aksi
```

**Acceptance Criteria:**
- [ ] Setiap role hanya melihat tiket sesuai scope-nya
- [ ] Super Admin tidak bisa melakukan aksi pada tiket

---

## Task 12.3 — Test Permission Guard

**Apa yang dilakukan:**
Test bahwa setiap endpoint dicek permission-nya.

```
1. Login Pegawai → coba akses halaman master data → 403
2. Login Teknisi → coba buat tiket → 403
3. Login Pegawai → coba verifikasi tiket → 403
4. Login Helpdesk → coba approve tiket → 403
5. Login Manager IT → coba assign tiket → 403
```

**Acceptance Criteria:**
- [ ] Semua aksi yang tidak sesuai permission → 403 Forbidden
- [ ] Menu sidebar hanya tampil untuk permission yang dimiliki

---

## Task 12.4 — Test Edge Cases

```
1. User di-nonaktifkan saat login → cek session terhentikan
2. Nonaktifkan user yang punya tiket aktif → cek pesan error
3. Buat tiket dengan kategori yang kemudian di-nonaktifkan → cek detail tetap tampil
4. Upload file .exe → cek ditolak
5. Upload file > 10MB → cek ditolak
6. Upload > 5 file saat submit tiket → cek ditolak
7. Coba reopen tiket yang sedang pause → cek ditolak (safety guard)
8. Coba buat nomor tiket duplikat (concurrent request) → cek tidak duplikat
```

**Acceptance Criteria:**
- [ ] Semua edge case ter-handle dengan pesan error yang jelas

---

## Task 12.5 — UI Polish

**Checklist:**
- [ ] Responsive layout (desktop & tablet minimal)
- [ ] Loading state pada setiap form submit
- [ ] Konfirmasi SweetAlert2 sebelum aksi penting (delete, reject, close)
- [ ] Toast/notification setelah aksi berhasil
- [ ] Empty state yang informatif ("Belum ada tiket", dll)
- [ ] Badge warna untuk status dan prioritas konsisten
- [ ] Pagination di semua halaman list
- [ ] Breadcrumb navigasi
- [ ] Favicon dan title tag

---

## Task 12.6 — Checklist Akhir Sebelum Deploy

- [ ] `php artisan migrate:fresh --seed` berhasil tanpa error
- [ ] `npm run build` berhasil tanpa error
- [ ] Semua route terlindungi middleware `auth`
- [ ] Queue worker berjalan: `php artisan queue:work`
- [ ] Scheduler terdaftar: `php artisan schedule:list`
- [ ] File `.env.example` terupdate
- [ ] `APP_DEBUG=false` di production
- [ ] `APP_URL` sesuai domain production
- [ ] Storage link: `php artisan storage:link`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache route: `php artisan route:cache`
