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
        Schema::create('planets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('sector_id');
            $table->string('name', 15);

            $table->integer('organics')->default(0);
            $table->integer('ore')->default(0);
            $table->integer('goods')->default(0);
            $table->integer('energy')->default(0);
            $table->integer('colonists')->default(0);
            $table->integer('credits')->default(0);
            $table->integer('fighters')->default(0);
            $table->integer('torps')->default(0);

            $table->unsignedBigInteger('owner')->nullable()->default(null);
            $table->unsignedBigInteger('team')->nullable()->default(null);

            $table->boolean('base')->default(false);
            $table->boolean('sells')->default(false);
            $table->integer('prod_organics')->default(0);
            $table->integer('prod_ore')->default(0);
            $table->integer('prod_goods')->default(0);
            $table->integer('prod_energy')->default(0);
            $table->integer('prod_fighters')->default(0);
            $table->integer('prod_torp')->default(0);

            $table->boolean('defeated')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planets');
    }
};
