<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tki\Types\ZonePermission;

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

            $table->unsignedBigInteger('owner_id')->nullable()->default(null);

            $table->boolean('is_team_zone')->default(false);

            $defaults = array_from_enum(ZonePermission::cases());
            $table->enum('allow_beacon', $defaults)->default(ZonePermission::Allow->value);
            $table->enum('allow_attack', $defaults)->default(ZonePermission::Allow->value);
            $table->enum('allow_planetattack', $defaults)->default(ZonePermission::Allow->value);
            $table->enum('allow_warpedit', $defaults)->default(ZonePermission::Allow->value);
            $table->enum('allow_planet', $defaults)->default(ZonePermission::Allow->value);
            $table->enum('allow_trade', $defaults)->default(ZonePermission::Allow->value);
            $table->enum('allow_defenses', $defaults)->default(ZonePermission::Allow->value);

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
