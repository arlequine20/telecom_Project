<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sim_cards', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('assigned_at');
            $table->decimal('data_balance', 10, 2)->default(0)->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('sim_cards', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'data_balance']);
        });
    }
};
