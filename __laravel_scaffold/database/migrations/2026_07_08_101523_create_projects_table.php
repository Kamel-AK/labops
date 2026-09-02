<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ProjectStatus;
use App\Enums\ProjectPriority;

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
            $table->string('status')->default(ProjectStatus::PROPOSED->value);
            $table->foreignId('lead_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('members')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('members')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->string('priority')->default(ProjectPriority::MEDIUM->value);
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
