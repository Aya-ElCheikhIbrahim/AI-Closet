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
    Schema::create('clothing_items', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('userId');
        $table->string('name');
        $table->string('category');
        $table->string('subcategory')->nullable();
        $table->string('color')->nullable();
        $table->string('secondaryColor')->nullable();
        $table->string('season')->nullable();
        $table->string('occasion')->nullable();
        $table->string('brand')->nullable();
        $table->text('notes')->nullable();

        $table->string('imageOriginal')->nullable();
        $table->string('imageNoBg')->nullable();

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
        Schema::dropIfExists('clothing_items');
    }
};
