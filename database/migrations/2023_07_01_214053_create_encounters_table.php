<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tki\Types\EncounterType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->enum('type', array_from_enum(EncounterType::cases())); // What happened
            $table->unsignedBigInteger('user_id'); // To Whom
            $table->unsignedBigInteger('sector_id'); // Where
            $table->unsignedBigInteger('movement_id')->nullable(); // Where

            // Encounters store their state between requests in this data column.
            $table->json('data');

            $table->timestamp('completed_at')->nullable()->default(null);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('sector_id')
                ->references('id')
                ->on('systems')
                ->onDelete('cascade');

            $table->foreign('movement_id')
                ->references('id')
                ->on('movement_logs')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
