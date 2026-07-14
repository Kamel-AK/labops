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
            $table->string('name'); // varchar
            $table->text('description')->nullable(); // text
            $table->string('status', 255)->default(ProjectStatus::PENDING->value); // string
            $table->date('start_date')->nullable(); // date
            $table->date('target_end_date')->nullable(); // date
            $table->date('actual_end_date')->nullable(); // date
            $table->foreignId('lead_id')->constrained('users')->onDelete('cascade'); // bigint
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // bigint
            $table->foreignId('approved_by')->constrained('users')->onDelete('cascade'); // bigint
            $table->string('priority', 255)->default(ProjectPriority::MEDIUM); // string
            $table->date('approved_at')->nullable(); // timestamp
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
