# Planning: Fase 2 — Database (Migration, Model, Enum, Seeder)

> **Estimasi:** 2 hari kerja
> **Prasyarat:** Fase 1 selesai (Laravel 12 + Spatie Permission terinstall + database MySQL `helpdesk_tiketing` sudah ada)
> **Kode referensi:** Semua kode PHP ada di `tasks/02-database.md` — salin persis dari sana.

---

## Urutan Pengerjaan

```
Step 1 (Enums) → Step 2 (Migrations) → Step 3 (Models) → Step 4 (Seeders) → Step 5 (Validasi)
```

---

## Step 1 — Buat PHP Enums (4 file)

Buat folder dan 4 file enum. **Salin kode persis dari `02-database.md` Task 2.1 dan Task 2.2.**

```powershell
New-Item -ItemType Directory -Force -Path app/Enums
```

| # | File | Referensi kode |
|---|------|---------------|
| 1 | `app/Enums/TicketStatus.php` | Task 2.1 — 9 case: open, verification, in_progress, waiting_for_info, waiting_third_party, pending_approval, resolved, closed, rejected. Tambah method `label()` |
| 2 | `app/Enums/PauseReason.php` | Task 2.2 — 2 case: waiting_for_info, waiting_third_party |
| 3 | `app/Enums/CommentType.php` | Task 2.2 — 3 case: comment, clarification, clarification_reply |
| 4 | `app/Enums/ApprovalStatus.php` | Task 2.2 — 3 case: pending, approved, rejected |
| 5 | `app/Enums/HistoryAction.php` | Task 2.2 — 17 case (created, verified, assigned, reassigned, returned_to_helpdesk, paused, resumed, resolved, closed, auto_closed, rejected_closed, reopened, approval_requested, approved, rejected, rejected_by_helpdesk, auto_closed_no_response) |

**Semua enum menggunakan `enum NamaEnum: string` (backed enum).**

### Validasi Step 1
```powershell
php artisan tinker --execute="echo App\Enums\TicketStatus::Open->value;"
# Harus output: open
```

---

## Step 2 — Buat Migrations (13 file, urutan FK penting!)

**⚠️ Install doctrine/dbal dulu** (diperlukan untuk `->change()` kolom di Migration 2):
```powershell
composer require doctrine/dbal
```

Buat migration **satu per satu, berurutan**. Kode Schema ada di `02-database.md` Task 2.3.

```powershell
php artisan make:migration create_work_units_table
php artisan make:migration add_columns_to_users_table
php artisan make:migration create_team_members_table
php artisan make:migration create_ticket_categories_table
php artisan make:migration create_ticket_priorities_table
php artisan make:migration create_ticket_counters_table
php artisan make:migration create_tickets_table
php artisan make:migration create_ticket_time_logs_table
php artisan make:migration create_ticket_comments_table
php artisan make:migration create_ticket_attachments_table
php artisan make:migration create_ticket_histories_table
php artisan make:migration create_ticket_approvals_table
php artisan make:migration create_notifications_table
```

Setelah membuat semua file, isi method `up()` masing-masing dengan kode dari `02-database.md` Task 2.3 (Migration 1-13). Pastikan juga isi method `down()` dengan `Schema::dropIfExists('nama_tabel')` atau `Schema::table()` untuk rollback.

### Catatan penting per migration:

| # | Migration | Catatan Kritis |
|---|-----------|---------------|
| 1 | work_units | Buat baru. `code` UNIQUE |
| 2 | add_columns_to_users | **ALTER table** (bukan create). Drop `email_verified_at`, ubah `email` jadi nullable, tambah `username` (UNIQUE), `avatar`, `work_unit_id` (FK → work_units, SET NULL), `is_active`. **Perlu doctrine/dbal** |
| 3 | team_members | FK ke work_units + users (CASCADE). UNIQUE composite `(work_unit_id, user_id)`. **Tidak ada `updated_at`** — hanya `joined_at` |
| 4 | ticket_categories | Sederhana. `is_active` default true |
| 5 | ticket_priorities | `level` TINYINT UNSIGNED UNIQUE. `color` VARCHAR(7) nullable |
| 6 | ticket_counters | **PK = `date` (DATE)**, bukan auto-increment. Tidak ada timestamps |
| 7 | tickets | Tabel terbesar. `status` pakai ENUM dari `TicketStatus::cases()`. FK: reporter_id (RESTRICT), handler_id (SET NULL), category_id (RESTRICT), priority_id (RESTRICT). Index pada: reporter_id, handler_id, status, category_id, priority_id, auto_close_at |
| 8 | ticket_time_logs | FK ticket_id CASCADE. `pause_reason` ENUM 2 nilai. `resumed_at` nullable (NULL = masih pause) |
| 9 | ticket_comments | FK ticket_id CASCADE, user_id RESTRICT. `type` ENUM 3 nilai. **Hanya `created_at`, TIDAK ADA `updated_at`** (immutable) |
| 10 | ticket_attachments | FK ticket_id CASCADE, comment_id SET NULL, uploaded_by RESTRICT. **Hanya `created_at`** |
| 11 | ticket_histories | FK ticket_id CASCADE, actor_id SET NULL, new_handler_id SET NULL, time_log_id SET NULL. `from_status`/`to_status` pakai VARCHAR(30) bukan ENUM. **Hanya `created_at`** (append-only) |
| 12 | ticket_approvals | FK ticket_id CASCADE, requested_by RESTRICT, reviewed_by SET NULL. `status` ENUM, `is_current` boolean |
| 13 | notifications | FK user_id CASCADE, ticket_id SET NULL. **Hanya `created_at`** |

### Jalankan migration:
```powershell
php artisan migrate
```

### Validasi Step 2
```powershell
php artisan migrate:status
# Semua harus berstatus Ran

php artisan tinker --execute="echo count(Schema::getTableListing());"
# Harus >= 18 tabel
```

---

## Step 3 — Buat Eloquent Models (13 model)

Buat model. Kode relasi ada di `02-database.md` Task 2.4.

```powershell
php artisan make:model WorkUnit
# User sudah ada, cukup edit
php artisan make:model TeamMember
php artisan make:model TicketCategory
php artisan make:model TicketPriority
php artisan make:model TicketCounter
php artisan make:model Ticket
php artisan make:model TicketTimeLog
php artisan make:model TicketComment
php artisan make:model TicketAttachment
php artisan make:model TicketHistory
php artisan make:model TicketApproval
php artisan make:model Notification
```

### Konfigurasi per Model

**Semua model harus punya:** `$fillable` (atau `$guarded = []`), `$casts`, dan relasi.

| Model | $casts khusus | Relasi | Catatan |
|-------|-------------|--------|---------|
| **WorkUnit** | — | hasMany: teamMembers, users | — |
| **User** (edit existing) | — | belongsTo: workUnit. hasMany: reportedTickets, handledTickets, teamMemberships | Sudah punya HasRoles. Tambah `username` ke fillable |
| **TeamMember** | `joined_at → datetime` | belongsTo: workUnit, user | `const UPDATED_AT = null;` (tidak ada updated_at, tapi ada joined_at custom) Lebih tepatnya: `public $timestamps = false;` |
| **TicketCategory** | — | hasMany: tickets | — |
| **TicketPriority** | — | hasMany: tickets | — |
| **TicketCounter** | `date → date` | — | `$timestamps = false; $incrementing = false; $primaryKey = 'date'; $keyType = 'string';` |
| **Ticket** | `status → TicketStatus, started_at → datetime, resolved_at → datetime, closed_at → datetime, auto_close_at → datetime` | belongsTo: reporter(User), handler(User), category, priority. hasMany: timeLogs, comments, attachments, histories, approvals | Model terbesar |
| **TicketTimeLog** | `pause_reason → PauseReason, paused_at → datetime, resumed_at → datetime` | belongsTo: ticket | — |
| **TicketComment** | `type → CommentType` | belongsTo: ticket, user. hasMany: attachments | `const UPDATED_AT = null;` |
| **TicketAttachment** | — | belongsTo: ticket, comment, uploader(User) | `const UPDATED_AT = null;` Atau `$timestamps = false;` + manual created_at |
| **TicketHistory** | — | belongsTo: ticket, actor(User), newHandler(User), timeLog | `const UPDATED_AT = null;` |
| **TicketApproval** | `status → ApprovalStatus, reviewed_at → datetime` | belongsTo: ticket, requester(User), reviewer(User) | — |
| **Notification** | `read_at → datetime` | belongsTo: user, ticket | `const UPDATED_AT = null;` |

### Contoh Model Ticket (model terbesar)
Salin dari `02-database.md` Task 2.4 bagian "Contoh relasi di Model Ticket", lalu tambahkan `$fillable` yang lengkap:

```php
protected $fillable = [
    'ticket_number', 'reporter_id', 'handler_id', 'category_id', 'priority_id',
    'title', 'description', 'status', 'started_at', 'total_paused_seconds',
    'resolved_at', 'closed_at', 'auto_close_at',
];
```

### Validasi Step 3
```powershell
php artisan tinker --execute="App\Models\Ticket::count();"
# Harus return 0 tanpa error

php artisan tinker --execute="App\Models\WorkUnit::count();"
# Harus return 0 tanpa error
```

---

## Step 4 — Buat Seeders (3 file)

### 4a. RolePermissionSeeder

```powershell
php artisan make:seeder RolePermissionSeeder
```

File: `database/seeders/RolePermissionSeeder.php`

**Isi:**
1. Buat 31 permissions (daftar lengkap di Dokumentasi Bag. 5a)
2. Buat 6 roles
3. Assign permissions ke roles sesuai mapping tabel

**Daftar 31 permissions:**
```
ticket.create, ticket.view, ticket.close, ticket.reopen, ticket.verify,
ticket.change-priority, ticket.change-category, ticket.assign, ticket.reassign,
ticket.return, ticket.update-progress, ticket.resolve, ticket.request-approval,
ticket.mark-third-party, ticket.clarify, ticket.reply-clarification,
ticket.comment, ticket.upload-attachment, ticket.approve, ticket.view-audit-trail,
dashboard.personal, dashboard.team, dashboard.operational,
report.export, report.personal,
master.category, master.priority, master.work-unit, master.user, master.permission,
profile.manage
```

**Mapping permissions per role:**
- **super_admin** (10): ticket.view, ticket.view-audit-trail, dashboard.operational, report.export, master.category, master.priority, master.work-unit, master.user, master.permission, profile.manage
- **pegawai** (10): ticket.create, ticket.view, ticket.close, ticket.reopen, ticket.reply-clarification, ticket.comment, ticket.upload-attachment, dashboard.personal, report.personal, profile.manage
- **ketua_tim_kerja** (11): semua pegawai + dashboard.team
- **helpdesk** (22): ticket.create, ticket.view, ticket.close, ticket.reopen, ticket.verify, ticket.change-priority, ticket.change-category, ticket.assign, ticket.reassign, ticket.update-progress, ticket.resolve, ticket.request-approval, ticket.mark-third-party, ticket.clarify, ticket.reply-clarification, ticket.comment, ticket.upload-attachment, ticket.view-audit-trail, dashboard.personal, dashboard.operational, report.personal, profile.manage
- **teknisi** (13): ticket.view, ticket.return, ticket.update-progress, ticket.resolve, ticket.request-approval, ticket.mark-third-party, ticket.clarify, ticket.comment, ticket.upload-attachment, ticket.view-audit-trail, dashboard.personal, report.personal, profile.manage
- **manager_it** (8): ticket.view, ticket.comment, ticket.upload-attachment, ticket.approve, dashboard.operational, report.export, report.personal, profile.manage

**Penting:** Panggil `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();` di awal seeder.

### 4b. InitialDataSeeder

```powershell
php artisan make:seeder InitialDataSeeder
```

File: `database/seeders/InitialDataSeeder.php`

**Isi:**
1. Insert 3 prioritas: Low (level 1, #22C55E), Medium (level 2, #F59E0B), High (level 3, #EF4444)
2. Insert 5 kategori: Hardware, Software, Network, Email, Lainnya
3. Insert 1 user Super Admin: username=superadmin, name=Super Administrator, password=bcrypt('password'), assignRole('super_admin')

### 4c. Update DatabaseSeeder.php

Edit `database/seeders/DatabaseSeeder.php`:
```php
public function run(): void
{
    $this->call([
        RolePermissionSeeder::class,
        InitialDataSeeder::class,
    ]);
}
```

### Validasi Step 4
```powershell
php artisan migrate:fresh --seed
```

Cek:
- `roles` = 6 record
- `permissions` = 31 record
- `role_has_permissions` = 74 record (10+10+11+22+13+8)
- `ticket_priorities` = 3 record
- `ticket_categories` = 5 record
- `users` = 1 record (superadmin dengan role super_admin)

---

## Step 5 — Validasi Akhir

```powershell
# 1. Fresh migrate + seed
php artisan migrate:fresh --seed

# 2. Cek semua tabel ada
php artisan tinker --execute="print_r(Schema::getTableListing());"

# 3. Cek role & permission
php artisan tinker --execute="echo Spatie\Permission\Models\Role::count();"
# Harus: 6

php artisan tinker --execute="echo Spatie\Permission\Models\Permission::count();"
# Harus: 31

# 4. Cek user admin
php artisan tinker --execute="echo App\Models\User::first()->username;"
# Harus: superadmin

php artisan tinker --execute="echo App\Models\User::first()->hasRole('super_admin');"
# Harus: 1 (true)

# 5. Cek data awal
php artisan tinker --execute="echo App\Models\TicketPriority::count();"
# Harus: 3

php artisan tinker --execute="echo App\Models\TicketCategory::count();"
# Harus: 5
```

### Checklist Final
- [ ] 5 file enum di `app/Enums/`
- [ ] 13 migration berhasil dijalankan
- [ ] 18 tabel total di database (13 custom + 5 Spatie)
- [ ] 13 model Eloquent dengan relasi lengkap
- [ ] `php artisan migrate:fresh --seed` sukses tanpa error
- [ ] 6 roles, 31 permissions, 74 role_has_permissions
- [ ] 3 prioritas, 5 kategori, 1 user superadmin

---

## Daftar Semua File yang Dibuat/Diubah

| File | Aksi |
|------|------|
| `app/Enums/TicketStatus.php` | Buat baru |
| `app/Enums/PauseReason.php` | Buat baru |
| `app/Enums/CommentType.php` | Buat baru |
| `app/Enums/ApprovalStatus.php` | Buat baru |
| `app/Enums/HistoryAction.php` | Buat baru |
| `database/migrations/*_create_work_units_table.php` | Buat baru |
| `database/migrations/*_add_columns_to_users_table.php` | Buat baru |
| `database/migrations/*_create_team_members_table.php` | Buat baru |
| `database/migrations/*_create_ticket_categories_table.php` | Buat baru |
| `database/migrations/*_create_ticket_priorities_table.php` | Buat baru |
| `database/migrations/*_create_ticket_counters_table.php` | Buat baru |
| `database/migrations/*_create_tickets_table.php` | Buat baru |
| `database/migrations/*_create_ticket_time_logs_table.php` | Buat baru |
| `database/migrations/*_create_ticket_comments_table.php` | Buat baru |
| `database/migrations/*_create_ticket_attachments_table.php` | Buat baru |
| `database/migrations/*_create_ticket_histories_table.php` | Buat baru |
| `database/migrations/*_create_ticket_approvals_table.php` | Buat baru |
| `database/migrations/*_create_notifications_table.php` | Buat baru |
| `app/Models/WorkUnit.php` | Buat baru |
| `app/Models/User.php` | Edit — tambah relasi + fillable |
| `app/Models/TeamMember.php` | Buat baru |
| `app/Models/TicketCategory.php` | Buat baru |
| `app/Models/TicketPriority.php` | Buat baru |
| `app/Models/TicketCounter.php` | Buat baru |
| `app/Models/Ticket.php` | Buat baru |
| `app/Models/TicketTimeLog.php` | Buat baru |
| `app/Models/TicketComment.php` | Buat baru |
| `app/Models/TicketAttachment.php` | Buat baru |
| `app/Models/TicketHistory.php` | Buat baru |
| `app/Models/TicketApproval.php` | Buat baru |
| `app/Models/Notification.php` | Buat baru |
| `database/seeders/RolePermissionSeeder.php` | Buat baru |
| `database/seeders/InitialDataSeeder.php` | Buat baru |
| `database/seeders/DatabaseSeeder.php` | Edit |
