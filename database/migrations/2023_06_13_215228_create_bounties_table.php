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
            $table->unsignedBigInteger('placed_by')->nullable();

            $table->index(['bounty_on', 'placed_by']);

            $table->foreign('bounty_on')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('placed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
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
