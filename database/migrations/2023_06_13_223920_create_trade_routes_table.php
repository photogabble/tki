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
        Schema::create('trade_routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('dest_id');
            $table->unsignedBigInteger('owner');

            $table->char('source_type', 1)->default('P');
            $table->char('dest_type', 1)->default('P');
            $table->char('move_type', 1)->default('W');
            $table->char('circuit', 1)->default(2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_routes');
    }
};
