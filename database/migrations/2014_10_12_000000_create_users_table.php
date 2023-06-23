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
        // Is players table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('team_invite')->nullable();

            // If null then the player has died in space
            $table->unsignedBigInteger('ship_id')->nullable();

            $table->string('name', 20);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->timestamp('last_login')->nullable()->default(null);
            $table->string('lang', 30)->default('en_GB');

            $table->unsignedInteger('turns')->default(0);
            $table->unsignedInteger('turns_used')->default(0);

            $table->unsignedInteger('rating')->default(0);
            $table->unsignedInteger('score')->default(0);

            $table->unsignedInteger('credits')->default(0);

            $table->rememberToken();
            $table->timestamps();

            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('set null');

            $table->foreign('team_invite')
                ->references('id')
                ->on('teams')
                ->onDelete('set null');

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
        Schema::dropIfExists('users');
    }
};
