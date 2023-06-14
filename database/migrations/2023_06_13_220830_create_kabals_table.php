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
        Schema::create('kabals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->boolean('active')->default(true);

            $table->integer('aggression')->default(0);
            $table->integer('orders')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kabals');
    }
};