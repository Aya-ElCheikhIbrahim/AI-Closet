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
    Schema::create('clothing_tags', function (Blueprint $table) {
        $table->string('clothingItemId');
        $table->string('tagId');

        $table->primary(['clothingItemId', 'tagId']);

        $table->foreign('clothingItemId')->references('id')->on('clothing_items')->cascadeOnDelete();
        $table->foreign('tagId')->references('id')->on('tags')->cascadeOnDelete();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clothing_tags');
    }
};
