<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();
            $table->foreignId('equipment_id')
                ->constrained('equipment')
                ->cascadeOnDelete();
            $table->timestamps();


            $table->unique(['reservation_id', 'equipment_id']);
            $table->index('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_equipment');
    }
};
