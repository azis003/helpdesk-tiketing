<?php

namespace Tests\Feature\Ticket;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Models\WorkUnit;
use App\Services\TicketVisibilityScope;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketVisibilityScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ketua_tim_hanya_melihat_tiket_dari_unit_kerjanya(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $workUnitA = WorkUnit::query()->create(['name' => 'Unit A', 'code' => 'UA']);
        $workUnitB = WorkUnit::query()->create(['name' => 'Unit B', 'code' => 'UB']);

        $ketua = User::factory()->create(['work_unit_id' => $workUnitA->id]);
        $ketua->assignRole('ketua_tim_kerja');

        $anggotaSatuUnit = User::factory()->create(['work_unit_id' => $workUnitA->id]);
        $anggotaLain = User::factory()->create(['work_unit_id' => $workUnitB->id]);

        $category = TicketCategory::query()->create(['name' => 'Software']);
        $priority = TicketPriority::query()->create(['name' => 'Medium', 'level' => 2]);

        $ticketVisible = Ticket::query()->create([
            'ticket_number' => 'TKT-20260424-0001',
            'reporter_id' => $anggotaSatuUnit->id,
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'title' => 'Tiket Satu Unit',
            'description' => 'Terlihat oleh ketua tim',
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TKT-20260424-0002',
            'reporter_id' => $anggotaLain->id,
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'title' => 'Tiket Unit Lain',
            'description' => 'Tidak terlihat oleh ketua tim',
        ]);

        $visibleIds = TicketVisibilityScope::for($ketua)->pluck('id');

        $this->assertTrue($visibleIds->contains($ticketVisible->id));
        $this->assertCount(1, $visibleIds);
    }

    public function test_teknisi_hanya_melihat_tiket_yang_sedang_diassign_ke_dirinya(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $teknisi = User::factory()->create();
        $teknisi->assignRole('teknisi');

        $reporter = User::factory()->create();
        $category = TicketCategory::query()->create(['name' => 'Hardware']);
        $priority = TicketPriority::query()->create(['name' => 'High', 'level' => 3]);

        $ticketVisible = Ticket::query()->create([
            'ticket_number' => 'TKT-20260424-0003',
            'reporter_id' => $reporter->id,
            'handler_id' => $teknisi->id,
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'title' => 'Tiket Teknisi',
            'description' => 'Terlihat oleh teknisi',
            'status' => 'in_progress',
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TKT-20260424-0004',
            'reporter_id' => $reporter->id,
            'category_id' => $category->id,
            'priority_id' => $priority->id,
            'title' => 'Tiket Bukan Teknisi',
            'description' => 'Tidak terlihat oleh teknisi',
        ]);

        $visibleIds = TicketVisibilityScope::for($teknisi)->pluck('id');

        $this->assertTrue($visibleIds->contains($ticketVisible->id));
        $this->assertCount(1, $visibleIds);
    }
}
