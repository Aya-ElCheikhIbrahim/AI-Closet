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
    Schema::create('outfits', function (Blueprint $table) {
        $table->string('id')->primary();

        $table->string('userId');
        $table->string('name');
        $table->string('style')->nullable();
        $table->string('occasion')->nullable();
        $table->string('season')->nullable();
        $table->string('outfitHash')->nullable();

        $table->boolean('aiGenerated')->default(false);
        $table->boolean('favorite')->default(false);
        $table->integer('wearCount')->default(0);
        $table->timestamp('lastWornAt')->nullable();
        $table->timestamp('createdAt')->useCurrent();

        $table->foreign('userId')->references('id')->on('users')->cascadeOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfits');
    }
};
