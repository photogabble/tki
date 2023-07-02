<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tki\Types\MovementMode;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movement_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sector_id');
            $table->unsignedBigInteger('previous_id')->nullable();

            $table->enum('mode',  array_from_enum(MovementMode::cases()));
            $table->integer('turns_used');
            $table->integer('energy_scooped');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('sector_id')
                ->references('id')
                ->on('universes')
                ->onDelete('cascade');

            $table->foreign('previous_id')
                ->references('id')
                ->on('movement_logs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movement_logs');
    }
};
