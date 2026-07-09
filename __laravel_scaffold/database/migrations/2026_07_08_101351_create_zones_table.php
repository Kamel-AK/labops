<?php

use App\Enums\ZoneStatus;
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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // varchar
            $table->text('description')->nullable(); // text
            $table->string('color_code')->nullable(); // varchar
            $table->string('status', ZoneStatus::cases())->default(ZoneStatus::OPEN->value); // enum
            $table->date('operating_hours_start')->nullable(); // timestamp
            $table->date('operating_hours_end')->nullable(); // timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
