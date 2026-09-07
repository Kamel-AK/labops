<?php

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', array_column(ProjectStatus::cases(), 'value'))->default(ProjectStatus::PROPOSED->value);
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->foreignId('lead_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('members')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('members')->nullOnDelete();
            $table->enum('priority', array_column(ProjectPriority::cases(), 'value'))->default(ProjectPriority::MEDIUM->value);
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
