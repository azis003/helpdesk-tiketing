<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TicketStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
