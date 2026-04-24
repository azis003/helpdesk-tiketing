<?php

namespace App\Support;

use Illuminate\Support\Str;

class PermissionCatalog
{
    /**
     * @var array<string, array{label: string, order: int}>
     */
    private const ROLE_META = [
        'super_admin' => ['label' => 'Super Admin', 'order' => 10],
        'manager_it' => ['label' => 'Manager IT', 'order' => 20],
        'helpdesk' => ['label' => 'Helpdesk', 'order' => 30],
        'ketua_tim_kerja' => ['label' => 'Ketua Tim Kerja', 'order' => 40],
        'teknisi' => ['label' => 'Teknisi', 'order' => 50],
        'pegawai' => ['label' => 'Pegawai', 'order' => 60],
    ];

    /**
     * @var array<string, array{label: string, order: int}>
     */
    private const GROUP_META = [
        'dashboard' => ['label' => 'Dashboard', 'order' => 10],
        'ticket' => ['label' => 'Manajemen Tiket', 'order' => 20],
        'report' => ['label' => 'Laporan', 'order' => 30],
        'master' => ['label' => 'Master Data', 'order' => 40],
        'profile' => ['label' => 'Profil Pengguna', 'order' => 50],
        'other' => ['label' => 'Lainnya', 'order' => 999],
    ];

    /**
     * @var array<string, array{group_key: string, label: string, description: string, order: int}>
     */
    private const PERMISSION_META = [
        'dashboard.personal' => ['group_key' => 'dashboard', 'label' => 'Lihat Dashboard Pribadi', 'description' => 'Menampilkan ringkasan tiket milik sendiri.', 'order' => 10],
        'dashboard.team' => ['group_key' => 'dashboard', 'label' => 'Lihat Dashboard Tim', 'description' => 'Menampilkan ringkasan tiket lingkup tim.', 'order' => 20],
        'dashboard.operational' => ['group_key' => 'dashboard', 'label' => 'Lihat Dashboard Operasional', 'description' => 'Menampilkan ringkasan operasional lintas unit.', 'order' => 30],

        'ticket.create' => ['group_key' => 'ticket', 'label' => 'Buat Tiket', 'description' => 'Membuat tiket baru.', 'order' => 10],
        'ticket.view' => ['group_key' => 'ticket', 'label' => 'Lihat Tiket', 'description' => 'Melihat daftar dan detail tiket.', 'order' => 20],
        'ticket.close' => ['group_key' => 'ticket', 'label' => 'Tutup Tiket', 'description' => 'Menutup tiket yang sudah selesai.', 'order' => 30],
        'ticket.reopen' => ['group_key' => 'ticket', 'label' => 'Buka Ulang Tiket', 'description' => 'Membuka kembali tiket yang sudah ditutup.', 'order' => 40],
        'ticket.verify' => ['group_key' => 'ticket', 'label' => 'Verifikasi Tiket', 'description' => 'Memverifikasi tiket sebelum proses lanjut.', 'order' => 50],
        'ticket.change-priority' => ['group_key' => 'ticket', 'label' => 'Ubah Prioritas Tiket', 'description' => 'Mengubah level prioritas tiket.', 'order' => 60],
        'ticket.change-category' => ['group_key' => 'ticket', 'label' => 'Ubah Kategori Tiket', 'description' => 'Mengubah kategori tiket.', 'order' => 70],
        'ticket.assign' => ['group_key' => 'ticket', 'label' => 'Tugaskan Tiket', 'description' => 'Menugaskan tiket ke petugas/teknisi.', 'order' => 80],
        'ticket.reassign' => ['group_key' => 'ticket', 'label' => 'Alihkan Penugasan Tiket', 'description' => 'Mengalihkan tiket ke petugas lain.', 'order' => 90],
        'ticket.return' => ['group_key' => 'ticket', 'label' => 'Kembalikan Tiket', 'description' => 'Mengembalikan tiket ke tahap sebelumnya.', 'order' => 100],
        'ticket.update-progress' => ['group_key' => 'ticket', 'label' => 'Perbarui Progres Tiket', 'description' => 'Memperbarui status/progres pengerjaan tiket.', 'order' => 110],
        'ticket.resolve' => ['group_key' => 'ticket', 'label' => 'Selesaikan Tiket', 'description' => 'Menandai tiket sebagai selesai ditangani.', 'order' => 120],
        'ticket.request-approval' => ['group_key' => 'ticket', 'label' => 'Ajukan Persetujuan Tiket', 'description' => 'Meminta persetujuan sebelum tindakan tertentu.', 'order' => 130],
        'ticket.mark-third-party' => ['group_key' => 'ticket', 'label' => 'Tandai Tiket Pihak Ketiga', 'description' => 'Menandai tiket yang ditangani vendor/pihak ketiga.', 'order' => 140],
        'ticket.clarify' => ['group_key' => 'ticket', 'label' => 'Minta Klarifikasi Tiket', 'description' => 'Meminta informasi tambahan pada tiket.', 'order' => 150],
        'ticket.reply-clarification' => ['group_key' => 'ticket', 'label' => 'Balas Klarifikasi Tiket', 'description' => 'Memberikan jawaban klarifikasi tiket.', 'order' => 160],
        'ticket.comment' => ['group_key' => 'ticket', 'label' => 'Komentar pada Tiket', 'description' => 'Menambahkan komentar pada tiket.', 'order' => 170],
        'ticket.upload-attachment' => ['group_key' => 'ticket', 'label' => 'Unggah Lampiran Tiket', 'description' => 'Mengunggah file lampiran pada tiket.', 'order' => 180],
        'ticket.approve' => ['group_key' => 'ticket', 'label' => 'Setujui Tiket', 'description' => 'Memberikan persetujuan pada tiket.', 'order' => 190],
        'ticket.view-audit-trail' => ['group_key' => 'ticket', 'label' => 'Lihat Riwayat Audit Tiket', 'description' => 'Melihat jejak perubahan tiket.', 'order' => 200],

        'report.personal' => ['group_key' => 'report', 'label' => 'Lihat Laporan Pribadi', 'description' => 'Melihat laporan berbasis data pribadi.', 'order' => 10],
        'report.export' => ['group_key' => 'report', 'label' => 'Ekspor Laporan', 'description' => 'Mengunduh laporan ke format file.', 'order' => 20],

        'master.category' => ['group_key' => 'master', 'label' => 'Kelola Kategori Tiket', 'description' => 'Mengelola data master kategori tiket.', 'order' => 10],
        'master.priority' => ['group_key' => 'master', 'label' => 'Kelola Level Prioritas', 'description' => 'Mengelola data master prioritas tiket.', 'order' => 20],
        'master.work-unit' => ['group_key' => 'master', 'label' => 'Kelola Unit Kerja', 'description' => 'Mengelola data unit kerja dan anggota unit.', 'order' => 30],
        'master.user' => ['group_key' => 'master', 'label' => 'Kelola Pengguna', 'description' => 'Mengelola akun pengguna aplikasi.', 'order' => 40],
        'master.permission' => ['group_key' => 'master', 'label' => 'Kelola Hak Akses & Peran', 'description' => 'Mengatur hak akses untuk setiap peran pengguna.', 'order' => 50],

        'profile.manage' => ['group_key' => 'profile', 'label' => 'Kelola Profil', 'description' => 'Mengubah data profil dan password akun.', 'order' => 10],
    ];

    /**
     * @return array{label: string, order: int}
     */
    public static function roleMeta(string $roleName): array
    {
        if (isset(self::ROLE_META[$roleName])) {
            return self::ROLE_META[$roleName];
        }

        return [
            'label' => Str::of($roleName)->replace('_', ' ')->title()->toString(),
            'order' => 999,
        ];
    }

    /**
     * @return array{group_key: string, group_label: string, group_order: int, label: string, description: string, order: int}
     */
    public static function permissionMeta(string $permissionName): array
    {
        if (isset(self::PERMISSION_META[$permissionName])) {
            $meta = self::PERMISSION_META[$permissionName];

            return array_merge(
                $meta,
                self::groupMeta($meta['group_key']),
            );
        }

        return self::fallbackPermissionMeta($permissionName);
    }

    /**
     * @return array{group_label: string, group_order: int}
     */
    private static function groupMeta(string $groupKey): array
    {
        $group = self::GROUP_META[$groupKey] ?? self::GROUP_META['other'];

        return [
            'group_label' => $group['label'],
            'group_order' => $group['order'],
        ];
    }

    /**
     * @return array{group_key: string, group_label: string, group_order: int, label: string, description: string, order: int}
     */
    private static function fallbackPermissionMeta(string $permissionName): array
    {
        [$groupKey, $action] = array_pad(explode('.', $permissionName, 2), 2, null);
        $normalizedGroupKey = $groupKey ?: 'other';

        return array_merge(
            [
                'group_key' => $normalizedGroupKey,
                'label' => Str::of($permissionName)->replace(['.', '-', '_'], ' ')->title()->toString(),
                'description' => $action
                    ? 'Hak akses teknis untuk '.Str::of($action)->replace(['-', '_'], ' ')->lower().'.'
                    : 'Hak akses teknis.',
                'order' => 999,
            ],
            self::groupMeta($normalizedGroupKey),
        );
    }
}
