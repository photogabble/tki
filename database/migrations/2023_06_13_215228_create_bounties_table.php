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
        Schema::create('bounties', function (Blueprint $table) {
            $table->id(); // bounty_id
            $table->timestamps();

            $table->unsignedInteger('amount')->default(0);
            $table->unsignedBigInteger('bounty_on');
            $table->unsignedBigInteger('placed_by');

            $table->index(['bounty_on', 'placed_by']);

            // TODO: Add Foreign Keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounties');
    }
};
