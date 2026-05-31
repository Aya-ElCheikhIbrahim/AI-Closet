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
    Schema::create('outfit_items', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('outfitId');
        $table->unsignedBigInteger('clothingItemId');
        $table->integer('position')->default(0);

        $table->foreign('outfitId')->references('id')->on('outfits')->cascadeOnDelete();
        $table->foreign('clothingItemId')->references('id')->on('clothing_items')->cascadeOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outfit_items');
    }
};
