# Fase 2 — Database: Migration, Model, Enum, Seeder

> **Tujuan:** Membuat seluruh struktur database sesuai ERD, Model Eloquent, PHP Enum, dan Seeder data awal.
> **Referensi:** Dokumentasi bagian ERD (semua tabel), Bag. 2 (Roles), Bag. 5a (Permissions)
> **Prasyarat:** Fase 1 selesai

---

## Task 2.1 — Buat PHP Enum TicketStatus

**Apa yang dilakukan:**
Buat PHP Enum untuk konsistensi status tiket di seluruh aplikasi. Enum ini dipakai di migration, model, controller, dan ticket_histories.

**Referensi:** Dokumentasi Bag. 5 (Tabel Status) + ERD Bag. 12 (catatan implementasi)

**File:** `app/Enums/TicketStatus.php`

```php
<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Verification = 'verification';
    case InProgress = 'in_progress';
    case WaitingForInfo = 'waiting_for_info';
    case WaitingThirdParty = 'waiting_third_party';
    case PendingApproval = 'pending_approval';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';

    /**
     * Label untuk ditampilkan di UI
     */
    public function label(): string
    {
        return match($this) {
            self::Open => 'Open',
            self::Verification => 'Verification',
            self::InProgress => 'In Progress',
            self::WaitingForInfo => 'Waiting for Info',
            self::WaitingThirdParty => 'Waiting Third Party',
            self::PendingApproval => 'Pending Approval',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
        };
    }
}
```

**Acceptance Criteria:**
- [ ] File enum ada di `app/Enums/TicketStatus.php`
- [ ] `TicketStatus::Open->value` menghasilkan string `'open'`
- [ ] `TicketStatus::Open->label()` menghasilkan string `'Open'`

---

## Task 2.2 — Buat Enum Tambahan

**Apa yang dilakukan:**
Buat enum tambahan untuk tipe data ENUM lain yang ada di ERD.

**File-file:**

**`app/Enums/PauseReason.php`** (untuk `ticket_time_logs.pause_reason`)
```php
enum PauseReason: string
{
    case WaitingForInfo = 'waiting_for_info';
    case WaitingThirdParty = 'waiting_third_party';
}
```

**`app/Enums/CommentType.php`** (untuk `ticket_comments.type`)
```php
enum CommentType: string
{
    case Comment = 'comment';
    case Clarification = 'clarification';
    case ClarificationReply = 'clarification_reply';
}
```

**`app/Enums/ApprovalStatus.php`** (untuk `ticket_approvals.status`)
```php
enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
```

**`app/Enums/HistoryAction.php`** (untuk `ticket_histories.action`)
```php
enum HistoryAction: string
{
    case Created = 'created';
    case Verified = 'verified';
    case Assigned = 'assigned';
    case Reassigned = 'reassigned';
    case ReturnedToHelpdesk = 'returned_to_helpdesk';
    case Paused = 'paused';
    case Resumed = 'resumed';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case AutoClosed = 'auto_closed';
    case RejectedClosed = 'rejected_closed';
    case Reopened = 'reopened';
    case ApprovalRequested = 'approval_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case RejectedByHelpdesk = 'rejected_by_helpdesk';
    case AutoClosedNoResponse = 'auto_closed_no_response';
}
```

**Acceptance Criteria:**
- [ ] 4 file enum sudah dibuat
- [ ] Setiap enum bisa di-import tanpa error

---

## Task 2.3 — Buat Migration (Urutan Penting!)

**Apa yang dilakukan:**
Buat file migration untuk semua tabel custom. **Urutan penting** karena ada FK dependencies.

**⚠️ PENTING:** Jalankan migration Spatie Permission terlebih dahulu (sudah ada dari Task 1.5).

**Urutan migration (buat satu per satu dengan `php artisan make:migration`):**

### Migration 1: `create_work_units_table`
**Referensi:** ERD Bag. 1

```php
Schema::create('work_units', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('code', 20)->unique();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Migration 2: `add_columns_to_users_table`
**Referensi:** ERD Bag. 2
> **Catatan:** Tabel `users` sudah ada dari Laravel default. Kita **tambahkan kolom**, bukan buat baru.

```php
Schema::table('users', function (Blueprint $table) {
    // Hapus kolom yang tidak dipakai
    $table->dropColumn(['email_verified_at']);
    
    // Ubah kolom existing
    $table->string('email', 100)->nullable()->change();
    
    // Tambah kolom baru (setelah kolom tertentu)
    $table->string('username', 50)->unique()->after('id');
    $table->string('avatar', 255)->nullable()->after('password');
    $table->foreignId('work_unit_id')->nullable()->after('avatar')
          ->constrained('work_units')->nullOnDelete();
    $table->boolean('is_active')->default(true)->after('work_unit_id');
    
    // Index
    $table->index('work_unit_id');
});
```

> **⚠️ Perlu install:** `composer require doctrine/dbal` untuk bisa `->change()` kolom.

### Migration 3: `create_team_members_table`
**Referensi:** ERD Bag. 3

```php
Schema::create('team_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('work_unit_id')->constrained('work_units')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->timestamp('joined_at')->useCurrent();
    
    $table->unique(['work_unit_id', 'user_id']);
    $table->index('user_id');
});
```

### Migration 4: `create_ticket_categories_table`
**Referensi:** ERD Bag. 6

```php
Schema::create('ticket_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Migration 5: `create_ticket_priorities_table`
**Referensi:** ERD Bag. 7

```php
Schema::create('ticket_priorities', function (Blueprint $table) {
    $table->id();
    $table->string('name', 50);
    $table->tinyInteger('level')->unsigned()->unique();
    $table->string('color', 7)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Migration 6: `create_ticket_counters_table`
**Referensi:** ERD Bag. 7b

```php
Schema::create('ticket_counters', function (Blueprint $table) {
    $table->date('date')->primary();
    $table->unsignedInteger('last_number')->default(0);
});
```

### Migration 7: `create_tickets_table`
**Referensi:** ERD Bag. 8

```php
use App\Enums\TicketStatus;

Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_number', 30)->unique();
    $table->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
    $table->foreignId('handler_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('category_id')->constrained('ticket_categories')->restrictOnDelete();
    $table->foreignId('priority_id')->constrained('ticket_priorities')->restrictOnDelete();
    $table->string('title', 255);
    $table->text('description');
    $table->enum('status', array_column(TicketStatus::cases(), 'value'))
          ->default(TicketStatus::Open->value);
    $table->timestamp('started_at')->nullable();
    $table->unsignedInteger('total_paused_seconds')->default(0);
    $table->timestamp('resolved_at')->nullable();
    $table->timestamp('closed_at')->nullable();
    $table->timestamp('auto_close_at')->nullable();
    $table->timestamps();

    $table->index('reporter_id');
    $table->index('handler_id');
    $table->index('status');
    $table->index('category_id');
    $table->index('priority_id');
    $table->index('auto_close_at');
});
```

### Migration 8: `create_ticket_time_logs_table`
**Referensi:** ERD Bag. 9

```php
Schema::create('ticket_time_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
    $table->enum('pause_reason', ['waiting_for_info', 'waiting_third_party']);
    $table->text('note')->nullable();
    $table->timestamp('paused_at');
    $table->timestamp('resumed_at')->nullable();
    $table->unsignedInteger('duration_seconds')->nullable();
    $table->timestamps();

    $table->index('ticket_id');
    $table->index('resumed_at');
});
```

### Migration 9: `create_ticket_comments_table`
**Referensi:** ERD Bag. 10

```php
Schema::create('ticket_comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
    $table->text('body');
    $table->enum('type', ['comment', 'clarification', 'clarification_reply'])
          ->default('comment');
    $table->timestamp('created_at')->useCurrent();
    // TIDAK ADA updated_at — komentar immutable

    $table->index('ticket_id');
    $table->index('user_id');
    $table->index('type');
});
```

### Migration 10: `create_ticket_attachments_table`
**Referensi:** ERD Bag. 11

```php
Schema::create('ticket_attachments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
    $table->foreignId('comment_id')->nullable()
          ->constrained('ticket_comments')->nullOnDelete();
    $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
    $table->string('original_name', 255);
    $table->string('stored_name', 255);
    $table->string('file_path', 500);
    $table->unsignedInteger('file_size');
    $table->string('mime_type', 127);
    $table->timestamp('created_at')->useCurrent();

    $table->index('ticket_id');
    $table->index('comment_id');
    $table->index('uploaded_by');
});
```

### Migration 11: `create_ticket_histories_table`
**Referensi:** ERD Bag. 12

```php
Schema::create('ticket_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
    $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('new_handler_id')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('time_log_id')->nullable()
          ->constrained('ticket_time_logs')->nullOnDelete();
    $table->string('from_status', 30)->nullable();
    $table->string('to_status', 30);
    $table->string('action', 100);
    $table->text('note')->nullable();
    $table->timestamp('created_at')->useCurrent();
    // TIDAK ADA updated_at — audit trail append-only

    $table->index('ticket_id');
    $table->index('actor_id');
    $table->index('time_log_id');
    $table->index('new_handler_id');
});
```

### Migration 12: `create_ticket_approvals_table`
**Referensi:** ERD Bag. 13

```php
Schema::create('ticket_approvals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
    $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
    $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->boolean('is_current')->default(true);
    $table->text('note')->nullable();
    $table->timestamp('reviewed_at')->nullable();
    $table->timestamps();

    $table->index('ticket_id');
    $table->index('status');
    $table->index('is_current');
});
```

### Migration 13: `create_notifications_table`
**Referensi:** ERD Bag. 14

```php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
    $table->string('type', 100);
    $table->string('title', 255);
    $table->text('body')->nullable();
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamp('created_at')->useCurrent();

    $table->index('user_id');
    $table->index('is_read');
    $table->index('ticket_id');
});
```

**Setelah semua migration dibuat, jalankan:**
```bash
php artisan migrate
```

**Acceptance Criteria:**
- [ ] Semua 13 migration berhasil dijalankan tanpa error
- [ ] Semua 18 tabel (13 custom + 5 Spatie) sudah ada di database
- [ ] FK constraint sudah benar (cek via MySQL client/phpMyAdmin)

---

## Task 2.4 — Buat Eloquent Models

**Apa yang dilakukan:**
Buat Model Eloquent untuk setiap tabel custom dengan relasi yang benar.

**Daftar Model (buat dengan `php artisan make:model NamaModel`):**

| Model | Tabel | Catatan |
|-------|-------|---------|
| `WorkUnit` | work_units | |
| `User` | users | Sudah ada, tambahkan relasi |
| `TeamMember` | team_members | Tidak punya `updated_at` |
| `TicketCategory` | ticket_categories | |
| `TicketPriority` | ticket_priorities | |
| `TicketCounter` | ticket_counters | PK = `date`, tidak ada auto increment |
| `Ticket` | tickets | Model terbesar, banyak relasi |
| `TicketTimeLog` | ticket_time_logs | |
| `TicketComment` | ticket_comments | Hanya `created_at`, tidak ada `updated_at` |
| `TicketAttachment` | ticket_attachments | Hanya `created_at` |
| `TicketHistory` | ticket_histories | Hanya `created_at`, append-only |
| `TicketApproval` | ticket_approvals | |
| `Notification` | notifications | Hanya `created_at` |

**⚠️ Tips untuk model tanpa `updated_at`:**
```php
// Untuk model yang hanya punya created_at (immutable)
const UPDATED_AT = null;
```

**⚠️ Tips untuk TicketCounter (PK bukan auto increment):**
```php
class TicketCounter extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'date';
    protected $keyType = 'string';
}
```

**Contoh relasi di Model `Ticket`:**
```php
class Ticket extends Model
{
    protected $casts = [
        'status' => TicketStatus::class,
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'auto_close_at' => 'datetime',
    ];

    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }
    public function handler() { return $this->belongsTo(User::class, 'handler_id'); }
    public function category() { return $this->belongsTo(TicketCategory::class, 'category_id'); }
    public function priority() { return $this->belongsTo(TicketPriority::class, 'priority_id'); }
    public function timeLogs() { return $this->hasMany(TicketTimeLog::class); }
    public function comments() { return $this->hasMany(TicketComment::class); }
    public function attachments() { return $this->hasMany(TicketAttachment::class); }
    public function histories() { return $this->hasMany(TicketHistory::class); }
    public function approvals() { return $this->hasMany(TicketApproval::class); }
}
```

**Acceptance Criteria:**
- [ ] Semua 13 model sudah dibuat
- [ ] Setiap model punya `$fillable` atau `$guarded` yang benar
- [ ] Setiap model punya `$casts` untuk ENUM dan date fields
- [ ] Relasi `belongsTo` dan `hasMany` sudah didefinisikan
- [ ] `php artisan tinker` → `Ticket::count()` → return 0 tanpa error

---

## Task 2.5 — Buat Seeder: Roles & Permissions

**Apa yang dilakukan:**
Buat seeder untuk 6 roles dan 31 permissions, lalu assign permissions ke roles.

**Referensi:** Dokumentasi Bag. 2 (Roles) + Bag. 5a (Daftar Permissions + Mapping Tabel)

**File:** `database/seeders/RolePermissionSeeder.php`

**Data yang di-seed:**

**6 Roles:**
`super_admin`, `pegawai`, `ketua_tim_kerja`, `helpdesk`, `teknisi`, `manager_it`

**31 Permissions:** (lihat daftar lengkap di Dokumentasi Bag. 5a)

**Mapping:** (lihat tabel Mapping Permission × Role di Dokumentasi Bag. 5a)
- super_admin: 10 permissions
- pegawai: 10 permissions
- ketua_tim_kerja: 11 permissions
- helpdesk: 22 permissions
- teknisi: 13 permissions
- manager_it: 8 permissions

```php
// Contoh di seeder:
$superAdmin = Role::create(['name' => 'super_admin']);
$superAdmin->givePermissionTo([
    'ticket.view',
    'ticket.view-audit-trail',
    'dashboard.operational',
    'report.export',
    'master.category',
    'master.priority',
    'master.work-unit',
    'master.user',
    'master.permission',
    'profile.manage',
]);
// ... lakukan untuk setiap role
```

**Acceptance Criteria:**
- [ ] `php artisan db:seed --class=RolePermissionSeeder` berhasil
- [ ] Tabel `roles` berisi 6 record
- [ ] Tabel `permissions` berisi 31 record
- [ ] Tabel `role_has_permissions` berisi jumlah record = 10+10+11+22+13+8 = 74

---

## Task 2.6 — Buat Seeder: Data Awal (Prioritas, Kategori, User Admin)

**Apa yang dilakukan:**
Buat seeder untuk data awal yang dibutuhkan agar aplikasi bisa digunakan.

**File:** `database/seeders/InitialDataSeeder.php`

**Data yang di-seed:**

**Prioritas (3 record):**
| name | level | color |
|------|-------|-------|
| Low | 1 | #22C55E |
| Medium | 2 | #F59E0B |
| High | 3 | #EF4444 |

**Kategori (contoh 5 record):**
| name | description |
|------|-------------|
| Hardware | Kendala perangkat keras (PC, laptop, printer, dll) |
| Software | Kendala aplikasi atau software |
| Network | Kendala jaringan internet atau LAN |
| Email | Kendala email atau akun |
| Lainnya | Kendala TI lainnya |

**User Super Admin (1 record):**
| username | name | password | role |
|----------|------|----------|------|
| superadmin | Super Administrator | password | super_admin |

**Acceptance Criteria:**
- [ ] `php artisan db:seed --class=InitialDataSeeder` berhasil
- [ ] Tabel `ticket_priorities` berisi 3 record
- [ ] Tabel `ticket_categories` berisi 5 record (atau sesuai kebutuhan)
- [ ] User `superadmin` bisa dilihat di tabel `users` dengan role `super_admin`

---

## Task 2.7 — Update DatabaseSeeder.php

**Apa yang dilakukan:**
Gabungkan semua seeder ke `DatabaseSeeder.php` agar bisa dijalankan sekaligus.

```php
public function run(): void
{
    $this->call([
        RolePermissionSeeder::class,
        InitialDataSeeder::class,
    ]);
}
```

**Test akhir:**
```bash
php artisan migrate:fresh --seed
```

**Acceptance Criteria:**
- [ ] `php artisan migrate:fresh --seed` berhasil dari awal sampai akhir tanpa error
- [ ] Semua 18 tabel ada di database
- [ ] Data seed sudah benar (roles, permissions, prioritas, kategori, user admin)
