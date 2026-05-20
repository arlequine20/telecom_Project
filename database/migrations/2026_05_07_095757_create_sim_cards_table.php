<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sim_cards', function (Blueprint $table) {
            $table->id();
            $table->string('sim_number')->unique();
            $table->string('phone_number')->unique();
            $table->decimal('balance', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('inactive');
            $table->enum('tariff_plan', ['prepaid', 'postpaid'])->default('prepaid');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sim_cards');
    }
};