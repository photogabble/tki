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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Seems accounts are linked to ships not players?
            $table->unsignedBigInteger('ship_id');

            $table->integer('balance')->default(0);
            $table->integer('loan')->default(0);

            $table->dateTime('loaned_on')->nullable()->default(null); // loantime
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
