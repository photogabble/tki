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
        // TODO: Rename to Sectors
        Schema::create('universes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name', 30)->nullable()->default(null);

            $table->unsignedBigInteger('zone_id')->nullable()->default(null);

            $table->string('port_type', 8)->default('none');
            $table->integer('port_organics')->default(0);
            $table->integer('port_ore')->default(0);
            $table->integer('port_goods')->default(0);
            $table->integer('port_energy')->default(0);
            $table->string('beacon', 50)->nullable()->default(null);
            $table->decimal('angle1')->default(0);
            $table->decimal('angle2')->default(0);
            $table->integer('distance')->default(0);
            $table->integer('fighters')->default(0);

            $table->index('port_type');

            $table->foreign('zone_id')
                ->references('id')
                ->on((new \App\Models\Zone())->getTable())
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universes');
    }
};
