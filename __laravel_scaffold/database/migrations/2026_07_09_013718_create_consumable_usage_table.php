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
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade'); // bigint
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade'); // bigint
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null'); // bigint (nullable)
            
            $table->integer('quantity_used'); // int
            $table->integer('quantity_remaining_after'); // int
            $table->dateTime('used_at'); // datetime
            $table->string('purpose_note')->nullable(); // varchar
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
