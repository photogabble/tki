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
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name', 40);

            $table->unsignedBigInteger('owner');

            $table->boolean('team_zone')->default(false);
            $table->boolean('allow_beacon')->default(true);
            $table->boolean('allow_attack')->default(true);
            $table->boolean('allow_planetattack')->default(true);
            $table->boolean('allow_warpedit')->default(true);
            $table->boolean('allow_planet')->default(true);
            $table->boolean('allow_trade')->default(true);
            $table->boolean('allow_defenses')->default(true);

            $table->integer('max_hull')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
