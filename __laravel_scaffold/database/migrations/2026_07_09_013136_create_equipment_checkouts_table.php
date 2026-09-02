<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\CheckoutStatus;
use App\Enums\CheckoutType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('equipment_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete(); // bigint
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete(); // bigint
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('set null'); // bigint (nullable)
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null'); // bigint (nullable)

            $table->dateTime('checked_out_at'); // datetime
            $table->dateTime('expected_return_at'); // datetime
            $table->dateTime('actual_return_at')->nullable(); // datetime


            $table->enum('status', array_column(CheckoutStatus::cases(), 'value')); // enum
            $table->enum('checkout_type', array_column(CheckoutType::cases(), 'value')); // enum
            $table->text('return_condition')->nullable(); // text
            $table->timestamps();

            $table->index(['status', 'equipment_id'], 'idx_checkouts_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_checkouts');
    }
};
