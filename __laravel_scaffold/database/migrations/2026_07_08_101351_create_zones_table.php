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
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color_code')->nullable();
            $table->enum('status', array_column(ZoneStatus::cases(), 'value'))->default(ZoneStatus::OPEN->value);
            $table->time('operating_hours_start')->nullable();
            $table->time('operating_hours_end')->nullable();
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
