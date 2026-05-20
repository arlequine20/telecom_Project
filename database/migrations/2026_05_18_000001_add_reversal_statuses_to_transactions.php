<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('transactions')) {
            DB::statement("ALTER TABLE `transactions` MODIFY `status` ENUM('pending','approved','cancelled','failed','reversal_requested','reversed','reversal_denied') NOT NULL DEFAULT 'pending';");
        }
    }

    public function down()
    {
        if (Schema::hasTable('transactions')) {
            DB::statement("ALTER TABLE `transactions` MODIFY `status` ENUM('pending','approved','cancelled','failed') NOT NULL DEFAULT 'pending';");
        }
    }
};
