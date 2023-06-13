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
        Schema::create('bank_transfers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger( 'source_id');
            $table->unsignedBigInteger( 'dest_id');

            $table->unsignedInteger('amount');

            $table->index(['source_id', 'dest_id']);

            // Todo Foreign Keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transfers');
    }
};
