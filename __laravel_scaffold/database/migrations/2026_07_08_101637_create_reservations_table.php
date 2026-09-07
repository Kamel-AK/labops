<?php

use App\Enums\ReservationStatus;
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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('zone_id')->constrained('zones');
            $table->foreignId('spot_id')->constrained('spots');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('purpose');
            $table->enum('status', array_column(ReservationStatus::cases(), 'value'))->default(ReservationStatus::CONFIRMED->value);
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->foreignId('created_by')->constrained('members')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['spot_id', 'start_time', 'end_time', 'status'], 'idx_reservations_spot_time');
            $table->index(['member_id', 'start_time', 'end_time', 'status'], 'idx_reservations_member_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
