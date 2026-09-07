<?php

use App\Enums\CheckoutStatus;
use App\Enums\CheckoutType;
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
        Schema::create('equipment_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->dateTime('checked_out_at');
            $table->dateTime('expected_return_at');
            $table->dateTime('actual_return_at')->nullable();
            $table->enum('status', array_column(CheckoutStatus::cases(), 'value'));
            $table->enum('checkout_type', array_column(CheckoutType::cases(), 'value'));
            $table->text('return_condition')->nullable();
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
