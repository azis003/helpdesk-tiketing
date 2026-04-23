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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_histories');
    }
};
