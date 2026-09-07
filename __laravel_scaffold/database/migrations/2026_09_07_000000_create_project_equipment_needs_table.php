<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_equipment_needs', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->unsignedInteger('quantity_needed')->default(1);
            $table->text('notes')->nullable();

            $table->primary(['project_id', 'equipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_equipment_needs');
    }
};
