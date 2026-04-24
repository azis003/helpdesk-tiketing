<?php

namespace Tests\Feature\Master;

use App\Models\User;
use App\Models\WorkUnit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkUnitMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_add_member_to_work_unit(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $workUnit = WorkUnit::query()->create([
            'name' => 'Unit TI',
            'code' => 'TI',
        ]);

        $member = User::factory()->create(['work_unit_id' => null]);

        $response = $this
            ->actingAs($admin)
            ->post(route('master.work-units.members.store', $workUnit), [
                'user_id' => $member->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'work_unit_id' => $workUnit->id,
        ]);
    }

    public function test_authorized_user_can_remove_member_from_work_unit(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $workUnit = WorkUnit::query()->create([
            'name' => 'Unit TI',
            'code' => 'TI',
        ]);

        $member = User::factory()->create(['work_unit_id' => $workUnit->id]);

        $response = $this
            ->actingAs($admin)
            ->delete(route('master.work-units.members.destroy', [$workUnit, $member]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'work_unit_id' => null,
        ]);
    }
}
