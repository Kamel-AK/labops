<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SpotType;
use App\Enums\SpotStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade'); // bigint
            $table->string('name'); // varchar
            $table->string('type', 255); // string
            $table->string('status', 255)->default(SpotStatus::ACTIVE->value); // string
            $table->integer('capacity')->default(1); // integer
            $table->text('description')->nullable(); // text
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spots');
    }
};
