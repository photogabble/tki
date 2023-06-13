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
        // TODO: Rename ship_presets ???
        Schema::create('presets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('ship_id');
            $table->unsignedInteger('preset')->default(1);
            $table->char('type', 1)->default('R');

            $table->index(['ship_id', 'preset']);

            // TODO: Foreign Keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presets');
    }
};
