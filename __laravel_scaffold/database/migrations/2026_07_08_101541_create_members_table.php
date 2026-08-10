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
            $table->string('name'); // varchar
            $table->string('email')->unique(); // varchar
            $table->string('password'); // varchar
            $table->string('telegram_chat_id')->nullable(); // varchar
            $table->string('role', 255); // string
            $table->string('access_status', 255); // string
            $table->text('skills_note')->nullable(); // text
            $table->rememberToken();
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
