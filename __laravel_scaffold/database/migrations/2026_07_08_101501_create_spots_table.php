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
            $table->string('type', SpotType::cases()); // enum
            $table->string('status', SpotStatus::cases())->default(SpotStatus::ACTIVE->value); // enum
            $table->integer('capacity')->default(1); // integer
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
