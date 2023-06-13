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
        Schema::create('sector_defenses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('ship_id');
            $table->unsignedBigInteger('sector_id');
            $table->char('defense_type', 1)->default('M');
            $table->unsignedInteger('quantity')->default(0);
            $table->string('fm_setting')->default('toll');

            // TODO Foreign Keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sector_defenses');
    }
};
