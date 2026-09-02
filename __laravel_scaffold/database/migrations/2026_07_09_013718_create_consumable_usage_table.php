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
        Schema::create('consumable_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete(); // bigint
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete(); // bigint
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null'); // bigint (nullable)
            
            $table->decimal('quantity_used', 10, 2);
            $table->decimal('quantity_remaining_after', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_usage');
    }
};
