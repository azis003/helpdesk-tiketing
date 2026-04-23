# Task Development — Helpdesk Tiketing BP2KOMDIGI

> **Berdasarkan:** Dokumentasi Aplikasi Helpdesk Tiketing v6.0
> **Stack:** Laravel 12 + Vue 3 + Inertia.js + Tailwind v4 + MySQL + Spatie Permission
> **Tanggal:** 2026-04-23

---

## Cara Menggunakan Dokumen Ini

1. Kerjakan **berurutan per fase** (Fase 1 → 2 → 3 → dst)
2. Dalam setiap fase, kerjakan **berurutan per task** (Task 1.1 → 1.2 → dst)
3. Setiap task punya **Acceptance Criteria** — pastikan semua terpenuhi sebelum lanjut
4. Setiap task merujuk ke **bagian dokumentasi** — baca bagian tersebut sebelum mulai coding
5. Tandai `[x]` jika task sudah selesai

---

## Daftar Fase

| Fase | File | Deskripsi | Estimasi |
|------|------|-----------|----------|
| **Fase 1** | `01-project-setup.md` | Setup project Laravel, install dependencies | 1 hari |
| **Fase 2** | `02-database.md` | Migration, Model, Enum, Seeder | 2 hari |
| **Fase 3** | `03-auth-middleware.md` | Login, Middleware, Profile | 1 hari |
| **Fase 4** | `04-master-data.md` | CRUD Kategori, Prioritas, Unit Kerja, User | 2 hari |
| **Fase 5** | `05-ticket-core.md` | Buat tiket, list, detail, nomor tiket | 2 hari |
| **Fase 6** | `06-ticket-workflow.md` | Verifikasi, assign, reject, status transitions | 3 hari |
| **Fase 7** | `07-comment-attachment.md` | Komentar, klarifikasi, upload file | 2 hari |
| **Fase 8** | `08-timer-approval.md` | Timer pause/resume, approval Manager IT | 2 hari |
| **Fase 9** | `09-notification.md` | Sistem notifikasi in-app + queue | 2 hari |
| **Fase 10** | `10-scheduler.md` | Auto-close, reminder, auto-close stale | 1 hari |
| **Fase 11** | `11-dashboard-report.md` | Dashboard, laporan, export Excel | 3 hari |
| **Fase 12** | `12-testing-polish.md` | Testing, bug fix, final review | 2 hari |

**Total estimasi: ~23 hari kerja**

---

## Diagram Dependensi Fase

```
Fase 1 (Setup)
  └── Fase 2 (Database)
       └── Fase 3 (Auth)
            ├── Fase 4 (Master Data)
            └── Fase 5 (Ticket Core)
                 └── Fase 6 (Workflow)
                      ├── Fase 7 (Comment & Attachment)
                      ├── Fase 8 (Timer & Approval)
                      └── Fase 9 (Notification)
                           └── Fase 10 (Scheduler)
                                └── Fase 11 (Dashboard & Report)
                                     └── Fase 12 (Testing)
```

---

## Aturan Penting Selama Development

1. **Selalu gunakan PHP Enum `TicketStatus`** untuk status tiket — jangan pakai string literal
2. **Selalu insert `ticket_histories`** setiap ada perubahan status tiket
3. **Selalu kirim notifikasi** sesuai mapping di Dokumentasi Bag. 10
4. **Komentar immutable** — tidak ada fitur edit/delete komentar
5. **Audit trail append-only** — tidak pernah update/delete `ticket_histories`
6. **Permission check** — setiap endpoint harus cek permission via Spatie
7. **Scope visibilitas** — filter tiket sesuai role user (Dokumentasi Bag. 7)
