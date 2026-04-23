<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['work_unit_id']);
            $table->dropColumn(['username', 'avatar', 'work_unit_id', 'is_active']);
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('email')->nullable(false)->change();
        });
    }
};
