<?php

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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // 'transaction', 'customer', 'sim_card', 'revenue', 'summary'
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->json('filters')->nullable();
            $table->json('data')->nullable();
            $table->string('status')->default('generated'); // 'generated', 'archived'
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
