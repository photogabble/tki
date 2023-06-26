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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('creator');
            $table->string('name', 20);
            $table->string('description', 20);
            $table->unsignedInteger('number_of_members')->default(0);
        });

        Schema::table('users', function (Blueprint $table){
            $table->foreign('team_id')
                ->references('id')
                ->on('teams')
                ->onDelete('set null');

            $table->foreign('team_invite')
                ->references('id')
                ->on('teams')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
