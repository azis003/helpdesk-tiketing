# Fase 11 — Dashboard & Laporan

> **Tujuan:** Implementasi semua dashboard dan fitur laporan/export.
> **Referensi:** Dokumentasi Bag. 15 (Dashboard)
> **Prasyarat:** Fase 10 selesai

---

## Task 11.1 — Dashboard Personal

**Permission:** `dashboard.personal`
**Role:** Pegawai, Ketua Tim Kerja, Helpdesk, Teknisi
**Referensi:** Dokumentasi Bag. 15.1

**File yang dibuat:**
1. `app/Http/Controllers/DashboardController.php` (method: `personal`)
2. `resources/js/Pages/Dashboard/Personal.vue`

**Widget yang ditampilkan:**

| Widget | Query |
|--------|-------|
| Total tiket saya | `COUNT(*)` dari tiket sesuai scope role |
| Breakdown per status | `GROUP BY status` → tampilkan card per status |
| Tiket terbaru | 5-10 tiket terbaru (urut `created_at DESC`) |
| Tiket butuh perhatian | Tiket waiting_for_info (pelapor perlu jawab), tiket resolved (pelapor perlu konfirmasi) |

**⚠️ Gunakan `TicketVisibilityScope` dari Fase 5 untuk filter tiket sesuai role.**

**Acceptance Criteria:**
- [ ] 4 widget tampil dengan data yang benar
- [ ] Data sesuai scope role masing-masing
- [ ] Klik tiket → redirect ke detail

---

## Task 11.2 — Dashboard Tim (Ketua Tim Kerja)

**Permission:** `dashboard.team`
**Referensi:** Dokumentasi Bag. 15.2

**File:** `resources/js/Pages/Dashboard/Team.vue`

**Widget:**
| Widget | Query |
|--------|-------|
| Total tiket tim | COUNT tiket dari semua anggota tim |
| Breakdown per anggota | GROUP BY reporter_id → nama anggota + count |
| Breakdown per status | GROUP BY status |
| Tiket butuh perhatian | Tiket anggota tim yang perlu follow-up |

**Acceptance Criteria:**
- [ ] Hanya Ketua Tim Kerja yang bisa akses
- [ ] Data mencakup seluruh anggota tim (via team_members)

---

## Task 11.3 — Dashboard Operasional

**Permission:** `dashboard.operational`
**Role:** Super Admin, Helpdesk, Manager IT
**Referensi:** Dokumentasi Bag. 15.3

**File:** `resources/js/Pages/Dashboard/Operational.vue`

**Widget:**
| Widget | Query |
|--------|-------|
| Total tiket sistem | `COUNT(*)` seluruh tiket |
| Breakdown per status | `GROUP BY status` → card + angka |
| Breakdown per kategori | `GROUP BY category_id` → chart/tabel |
| Breakdown per prioritas | `GROUP BY priority_id` → chart/tabel |
| Rata-rata waktu pengerjaan | AVG durasi efektif tiket Closed |
| Tiket per handler | `GROUP BY handler_id` → tabel handler + jumlah tiket aktif |

**Acceptance Criteria:**
- [ ] 6 widget tampil dengan data yang benar
- [ ] Data mencakup seluruh tiket di sistem

---

## Task 11.4 — Laporan & Export Excel

**Permission:** `report.export`
**Role:** Super Admin, Manager IT
**Referensi:** Dokumentasi Bag. 15.4

**File yang dibuat:**
1. `app/Http/Controllers/ReportController.php`
2. `resources/js/Pages/Report/Index.vue`
3. `app/Exports/TicketExport.php` (pakai Laravel Excel)

**Install Laravel Excel:**
```bash
composer require maatwebsite/excel
```

**Fitur halaman laporan:**
- Form filter: rentang tanggal, status, kategori, prioritas, handler
- Tombol "Export Excel" → download file .xlsx
- Preview tabel di halaman (opsional)

**Kolom export:**
Nomor Tiket | Judul | Pelapor | Handler | Kategori | Prioritas | Status | Durasi Efektif | Tgl Dibuat | Tgl Ditutup

**Acceptance Criteria:**
- [ ] Filter berfungsi
- [ ] Export menghasilkan file .xlsx yang valid
- [ ] Kolom sesuai spesifikasi
- [ ] Durasi efektif dihitung dengan benar

---

## Task 11.5 — Laporan Tiket Pribadi

**Permission:** `report.personal`
**Referensi:** Dokumentasi Bag. 15.5

**File:** `resources/js/Pages/Report/Personal.vue`

**⚠️ Query berbeda per role — ikuti persis dari dokumentasi:**

```php
$role = auth()->user()->getRoleNames()->first();
$userId = auth()->id();

$query = match ($role) {
    'pegawai', 'ketua_tim_kerja' =>
        Ticket::where('reporter_id', $userId),

    'helpdesk' =>
        Ticket::whereIn('id',
            TicketHistory::where('actor_id', $userId)
                ->whereIn('action', ['assigned', 'verified', 'reassigned'])
                ->pluck('ticket_id')
        ),

    'teknisi' =>
        Ticket::whereIn('id',
            TicketHistory::where('new_handler_id', $userId)
                ->whereIn('action', ['assigned', 'reassigned'])
                ->pluck('ticket_id')
        ),

    'manager_it' =>
        Ticket::whereIn('id',
            TicketApproval::where('reviewed_by', $userId)
                ->pluck('ticket_id')
        ),
};
```

**⚠️ JANGAN pakai `tickets.handler_id` untuk query Teknisi — pakai `ticket_histories.new_handler_id`.**

**Acceptance Criteria:**
- [ ] Pegawai/Ketua Tim: lihat tiket yang mereka buat
- [ ] Helpdesk: lihat tiket yang pernah mereka tangani
- [ ] Teknisi: lihat tiket yang pernah di-assign ke mereka (termasuk yang sudah di-reassign)
- [ ] Manager IT: lihat tiket yang pernah mereka approve/reject
