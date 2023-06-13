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
        Schema::create('schedulers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->boolean('run_once')->default(false);
            $table->unsignedInteger('ticks_left')->default(0);
            $table->unsignedInteger('ticks_full')->default(0);
            $table->unsignedInteger('spawn')->default(0);
            $table->string('sched_file', 30);
            $table->string('extra_info', 50);
            $table->integer('last_run')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedulers');
    }
};
