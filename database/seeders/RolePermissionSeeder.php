<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'ticket.create', 'ticket.view', 'ticket.close', 'ticket.reopen', 'ticket.verify',
            'ticket.change-priority', 'ticket.change-category', 'ticket.assign', 'ticket.reassign',
            'ticket.return', 'ticket.update-progress', 'ticket.resolve', 'ticket.request-approval',
            'ticket.mark-third-party', 'ticket.clarify', 'ticket.reply-clarification',
            'ticket.comment', 'ticket.upload-attachment', 'ticket.approve', 'ticket.view-audit-trail',
            'dashboard.personal', 'dashboard.team', 'dashboard.operational',
            'report.export', 'report.personal',
            'master.category', 'master.priority', 'master.work-unit', 'master.user', 'master.permission',
            'profile.manage'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin (10)
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo([
            'ticket.view', 'ticket.view-audit-trail', 'dashboard.operational', 'report.export',
            'master.category', 'master.priority', 'master.work-unit', 'master.user',
            'master.permission', 'profile.manage'
        ]);

        // Pegawai (10)
        $pegawai = Role::create(['name' => 'pegawai']);
        $pegawai->givePermissionTo([
            'ticket.create', 'ticket.view', 'ticket.close', 'ticket.reopen', 'ticket.reply-clarification',
            'ticket.comment', 'ticket.upload-attachment', 'dashboard.personal', 'report.personal', 'profile.manage'
        ]);

        // Ketua Tim Kerja (11)
        $ketuaTim = Role::create(['name' => 'ketua_tim_kerja']);
        $ketuaTim->givePermissionTo([
            'ticket.create', 'ticket.view', 'ticket.close', 'ticket.reopen', 'ticket.reply-clarification',
            'ticket.comment', 'ticket.upload-attachment', 'dashboard.personal', 'dashboard.team', 'report.personal', 'profile.manage'
        ]);

        // Helpdesk (22)
        $helpdesk = Role::create(['name' => 'helpdesk']);
        $helpdesk->givePermissionTo([
            'ticket.create', 'ticket.view', 'ticket.close', 'ticket.reopen', 'ticket.verify',
            'ticket.change-priority', 'ticket.change-category', 'ticket.assign', 'ticket.reassign',
            'ticket.update-progress', 'ticket.resolve', 'ticket.request-approval', 'ticket.mark-third-party',
            'ticket.clarify', 'ticket.reply-clarification', 'ticket.comment', 'ticket.upload-attachment',
            'ticket.view-audit-trail', 'dashboard.personal', 'dashboard.operational', 'report.personal', 'profile.manage'
        ]);

        // Teknisi (13)
        $teknisi = Role::create(['name' => 'teknisi']);
        $teknisi->givePermissionTo([
            'ticket.view', 'ticket.return', 'ticket.update-progress', 'ticket.resolve', 'ticket.request-approval',
            'ticket.mark-third-party', 'ticket.clarify', 'ticket.comment', 'ticket.upload-attachment',
            'ticket.view-audit-trail', 'dashboard.personal', 'report.personal', 'profile.manage'
        ]);

        // Manager IT (8)
        $managerIt = Role::create(['name' => 'manager_it']);
        $managerIt->givePermissionTo([
            'ticket.view', 'ticket.comment', 'ticket.upload-attachment', 'ticket.approve',
            'dashboard.operational', 'report.export', 'report.personal', 'profile.manage'
        ]);
    }
}
