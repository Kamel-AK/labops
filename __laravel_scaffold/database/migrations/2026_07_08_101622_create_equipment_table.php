<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\EquipmentStatus;
use App\Enums\EquipmentType;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // varchar
            $table->foreignId('category_id')->constrained('equipment_categories'); // bigint
            $table->string('subcategory'); // varchar
            $table->string('asset_tag')->unique(); // varchar
            $table->string('type', 255); // string
            $table->string('status', 255)->default(EquipmentStatus::AVAILABLE->value); // string

            $table->foreignId('current_custodian_id')->nullable()->constrained('members')->onDelete('set null'); // bigint
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null'); // bigint
            $table->foreignId('spot_id')->nullable()->constrained('spots')->onDelete('set null'); // bigint

            $table->integer('quantity_total')->default(1); // int
            $table->integer('quantity_available')->default(1); // int
            $table->integer('min_stock_threshold')->default(0); // int

            $table->boolean('allow_borrow')->default(false); // boolean
            $table->integer('max_borrow_days')->default(0); // int

            $table->string('photo_url')->nullable(); // varchar
            $table->string('manual_url')->nullable(); // varchar
            $table->text('notes')->nullable(); // text
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
