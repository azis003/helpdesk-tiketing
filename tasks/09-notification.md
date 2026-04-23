# Fase 9 — Sistem Notifikasi In-App

> **Tujuan:** Implementasi notifikasi in-app dengan queue.
> **Referensi:** Dokumentasi Bag. 10 (Notifikasi), Bag. 14.2 (Queue), ERD Bag. 14
> **Prasyarat:** Fase 8 selesai

---

## Task 9.1 — Notification Service

**File:** `app/Services/NotificationService.php`

**Apa yang dilakukan:**
Buat service class yang menjadi satu-satunya cara mengirim notifikasi di aplikasi.

```php
class NotificationService
{
    /**
     * Kirim notifikasi ke satu atau banyak user
     *
     * @param array|int $userIds  ID user penerima (bisa array atau integer)
     * @param int|null  $ticketId ID tiket terkait
     * @param string    $type     Salah satu dari notification types (lihat ERD Bag. 14)
     * @param string    $title    Judul notifikasi
     * @param string    $body     Isi notifikasi
     */
    public static function send(
        array|int $userIds,
        ?int $ticketId,
        string $type,
        string $title,
        string $body = null
    ): void {
        $userIds = is_array($userIds) ? $userIds : [$userIds];

        $records = collect($userIds)->map(fn($userId) => [
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'is_read' => false,
            'created_at' => now(),
        ])->toArray();

        // Dispatch ke queue agar tidak blocking
        SendNotificationJob::dispatch($records);
    }
}
```

**File:** `app/Jobs/SendNotificationJob.php`

```php
class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // retry setiap 60 detik

    public function __construct(public array $records) {}

    public function handle(): void
    {
        Notification::insert($this->records);
    }
}
```

**Acceptance Criteria:**
- [ ] `NotificationService::send()` bisa dipanggil dari mana saja
- [ ] Notifikasi terinsert ke tabel `notifications` via queue
- [ ] Queue berjalan: `php artisan queue:work --queue=default`

---

## Task 9.2 — Pasang Notifikasi di Semua Aksi

**Apa yang dilakukan:**
Kembali ke semua controller/service di Fase 5-8, ganti TODO comment dengan panggilan `NotificationService::send()`.

**Referensi:** Dokumentasi Bag. 10 (Mapping ticket_histories.action → notifications.type)

**Checklist (sesuai mapping table):**

| Aksi | Type | Penerima | File yang diubah |
|------|------|----------|------------------|
| Buat tiket | `ticket_created` | Semua Helpdesk | TicketController@store |
| Assign | `ticket_assigned` | Teknisi target | WorkflowService (assign) |
| Reassign | `ticket_reassigned` | Teknisi baru | WorkflowService (reassign) |
| Return to Helpdesk | `ticket_returned` | Semua Helpdesk | WorkflowService (return) |
| Reject di verifikasi | `ticket_rejected_by_helpdesk` | Pelapor | WorkflowService (reject) |
| Minta klarifikasi | `clarification_requested` | Pelapor | CommentController (clarify) |
| Balas klarifikasi | `clarification_replied` | Handler | CommentController (reply) |
| Resolved | `ticket_resolved` | Pelapor | WorkflowService (resolve) |
| Closed by pelapor | `ticket_closed` | Handler | WorkflowService (close) |
| ReOpen | `ticket_reopened` | Handler | WorkflowService (reopen) |
| Request approval | `approval_requested` | Semua Manager IT | WorkflowService (requestApproval) |
| Approved | `ticket_approved` | Handler yang request | WorkflowService (approve) |
| Rejected by Manager | `ticket_rejected` | Pelapor + Handler | WorkflowService (rejectApproval) |
| Waiting Third Party | `ticket_waiting_third_party` | Pelapor | WorkflowService (markThirdParty) |
| Komentar baru | `comment_added` | Partisipan - penulis | CommentController (store) |

**⚠️ Cara ambil "semua user dengan role X":**
```php
$helpdeskIds = User::role('helpdesk')->where('is_active', true)->pluck('id')->toArray();
$managerIds = User::role('manager_it')->where('is_active', true)->pluck('id')->toArray();
```

**⚠️ Cara ambil "semua partisipan tiket kecuali penulis" (untuk komentar):**
```php
$participantIds = collect()
    ->merge([$ticket->reporter_id])
    ->merge([$ticket->handler_id])
    ->merge(
        TicketComment::where('ticket_id', $ticket->id)
            ->pluck('user_id')
    )
    ->unique()
    ->reject(fn($id) => $id === $currentUserId) // kecuali penulis
    ->filter()
    ->values()
    ->toArray();
```

**Acceptance Criteria:**
- [ ] Setiap aksi mengirim notifikasi ke penerima yang benar
- [ ] Tidak ada notifikasi ke diri sendiri (komentar)
- [ ] Type notifikasi sesuai mapping

---

## Task 9.3 — Halaman Inbox Notifikasi

**File yang dibuat:**
1. `app/Http/Controllers/NotificationController.php`
2. `resources/js/Pages/Notification/Index.vue`

**Fitur:**
- List notifikasi milik user login, urut terbaru
- Badge **unread count** di navbar (bell icon)
- Klik notifikasi → tandai `is_read = true`, `read_at = NOW()` → redirect ke tiket terkait
- Tombol "Tandai semua sudah dibaca"
- Pagination (20 per halaman)

**Endpoint yang dibutuhkan:**
```
GET  /notifications           → list notifikasi
POST /notifications/{id}/read → tandai sudah dibaca
POST /notifications/read-all  → tandai semua sudah dibaca
GET  /notifications/unread-count → untuk badge di navbar (JSON)
```

**⚠️ Unread count di navbar (via Inertia shared data):**
```php
// Di HandleInertiaRequests.php -> share()
'unreadNotificationCount' => Auth::check()
    ? Notification::where('user_id', Auth::id())
        ->where('is_read', false)
        ->count()
    : 0,
```

**Acceptance Criteria:**
- [ ] Badge unread count muncul di navbar
- [ ] List notifikasi tampil dengan pagination
- [ ] Klik notifikasi → redirect ke tiket + tandai read
- [ ] "Tandai semua dibaca" berfungsi
