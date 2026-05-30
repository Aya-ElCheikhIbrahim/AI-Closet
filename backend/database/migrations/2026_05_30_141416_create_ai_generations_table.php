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
    Schema::create('ai_generations', function (Blueprint $table) {
        $table->string('id')->primary();

        $table->string('outfitId');
        $table->text('prompt');
        $table->string('modelUsed')->nullable();
        $table->string('generatedImagePath')->nullable();
        $table->string('status')->default('pending');
        $table->text('errorMessage')->nullable();
        $table->integer('generationTime')->nullable();
        $table->timestamp('createdAt')->useCurrent();

        $table->foreign('outfitId')->references('id')->on('outfits')->cascadeOnDelete();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
