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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password_hash');
            $table->string('phone')->nullable();
            $table->enum('role', ['coordinator', 'team_lead', 'volunteer'])->default('volunteer');
            $table->enum('access_status', ['pending', 'granted', 'revoked', 'suspended'])->default('pending');
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->text('emergency_contact')->nullable();
            $table->date('join_date')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
