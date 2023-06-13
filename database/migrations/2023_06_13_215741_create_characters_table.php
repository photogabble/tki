<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @deprecated
 */
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TODO: Merge into users table?
        // TODO: I believe this is deprecated.
        Schema::create('characters', function (Blueprint $table) {
            $table->id(); // character_id
            $table->timestamps();

            $table->string('name', 20);
            $table->unsignedInteger('credits')->default(0);

            $table->unsignedInteger('turns')->default(0);
            $table->unsignedInteger('turns_used')->default(0);
            $table->unsignedInteger('rating')->default(0);
            $table->unsignedInteger('score')->default(0);

            // TODO: Add Foreign Keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
