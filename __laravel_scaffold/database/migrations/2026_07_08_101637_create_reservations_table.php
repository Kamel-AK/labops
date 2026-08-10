<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ReservationStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade'); // bigint
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null'); // bigint (nullable)
            $table->foreignId('zone_id')->constrained('zones'); // bigint
            $table->foreignId('spot_id')->constrained('spots'); // bigint

            $table->dateTime('start_time'); // datetime
            $table->dateTime('end_time'); // datetime
            $table->string('purpose'); // varchar
            $table->string('status', 255)->default(ReservationStatus::CHECKED_IN->value); // string
            
            $table->dateTime('checked_in_at')->nullable(); // datetime
            $table->dateTime('checked_out_at')->nullable(); // datetime
            $table->foreignId('created_by')->constrained('members'); // bigint
            $table->timestamps();

            $table->index(['spot_id', 'start_time', 'end_time', 'status'], 'idx_reservations_spot_time');
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
