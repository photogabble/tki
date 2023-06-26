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
        Schema::create('ships', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // TODO: Rename to name
            $table->string('ship_name', 20);

            // TODO: should be timestamp of when with ship_id on User set null
            $table->boolean('ship_destroyed')->default(false);
            // Is null until destroyed, then set to user_id who owned it
            $table->unsignedBigInteger('owner_id')->nullable()->default(null);

            $table->unsignedInteger('hull')->default(0);
            $table->unsignedInteger('engines')->default(0);
            $table->unsignedInteger('power')->default(0);
            $table->unsignedInteger('computer')->default(0);
            $table->unsignedInteger('sensors')->default(0);
            $table->unsignedInteger('beams')->default(0);
            $table->unsignedInteger('torp_launchers')->default(0);
            $table->unsignedInteger('torps')->default(0);
            $table->unsignedInteger('shields')->default(0);
            $table->unsignedInteger('armor')->default(0);
            $table->unsignedInteger('armor_pts')->default(0);
            $table->unsignedInteger('cloak')->default(0);
            $table->unsignedBigInteger('sector_id')->default(0);
            $table->unsignedInteger('ship_ore')->default(0);
            $table->unsignedInteger('ship_organics')->default(0);
            $table->unsignedInteger('ship_goods')->default(0);
            $table->unsignedInteger('ship_energy')->default(0);
            $table->unsignedInteger('ship_colonists')->default(0);
            $table->unsignedInteger('ship_fighters')->default(0);
            $table->unsignedInteger('ship_damage')->default(0);

            // TODO remove, has been moved to User
            $table->unsignedInteger('turns')->default(0);

            $table->boolean('on_planet')->default(false);

            $table->unsignedInteger('dev_warpedit')->default(0);
            $table->unsignedInteger('dev_genesis')->default(0);
            $table->unsignedInteger('dev_beacon')->default(0);
            $table->unsignedInteger('dev_emerwarp')->default(0);
            $table->boolean('dev_escapepod')->default(false);
            $table->boolean('dev_fuelscoop')->default(false);
            $table->boolean('dev_lssd')->default(false);
            $table->unsignedInteger('dev_minedeflector')->default(0);

            // TODO remove, has been moved to User
            $table->unsignedInteger('rating')->default(0);
            // TODO remove, has been moved to User
            $table->unsignedInteger('score')->default(0);
            // TODO remove, has been moved to User
            $table->unsignedBigInteger('team_id')->nullable();
            // TODO remove, has been moved to User
            $table->unsignedBigInteger('team_invite')->nullable();
            // TODO remove, has been moved to User
            $table->unsignedBigInteger('planet_id')->nullable();

            $table->boolean('trade_colonists')->default(true);
            $table->boolean('trade_fighters')->default(false);
            $table->boolean('trade_torps')->default(false);
            $table->boolean('trade_energy')->default(true);

            $table->string('cleared_defenses', 99)->nullable()->default(null);

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('ship_id')
                ->references('id')
                ->on('ships')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ships');
    }
};
