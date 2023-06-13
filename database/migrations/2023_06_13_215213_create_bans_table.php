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
        Schema::create('bans', function (Blueprint $table) {
            $table->id(); // ban_id
            $table->timestamps();

            $table->unsignedInteger('type')->default(0);
            $table->string('mask', 16)->nullable()->default(null);
            $table->unsignedInteger('ship')->nullable()->default(null);
            $table->text('public_info');
            $table->text('admin_info');

            // TODO: Add Foreign Keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bans');
    }
};
