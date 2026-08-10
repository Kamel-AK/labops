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
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade'); // bigint
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade'); // bigint
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->onDelete('set null'); // bigint (nullable)
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null'); // bigint (nullable)

            $table->dateTime('checked_out_at'); // datetime
            $table->dateTime('expected_return_at'); // datetime
            $table->dateTime('actual_return_at')->nullable(); // datetime
            
            $table->string('status', 255); // string
            $table->string('type', 255); // string
            $table->text('condition_on_return')->nullable(); // text
            $table->timestamps();
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
