<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClothingItemController;
use App\Http\Controllers\OutfitController;
use App\Http\Controllers\OutfitItemController;
use App\Http\Controllers\ClothingTagController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']); //working
Route::post('/login', [AuthController::class, 'login']); //working
 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    //clothing items 
    Route::get('/allClothing/{id?}', [ClothingItemController::class, 'getAllClothingItems']); //working
    Route::post('/add_update_clothing/{id?}', [ClothingItemController::class, 'createOrUpdateClothingItem']); //working
    Route::delete('/delete_clothing/{id}', [ClothingItemController::class, 'deleteClothingItem']); //working
    
    //outfits
    Route::get('/outfits/{id?}', [OutfitController::class, 'getAllOutfits']); //working
    Route::post('/add_update_outfit/{id?}', [OutfitController::class, 'createOrUpdateOutfit']); //working
    Route::delete('/delete_outfit/{id}', [OutfitController::class, 'deleteOutfit']); //working

    //outfit items
    Route::get('/outfit_items/{id?}', [OutfitItemController::class, 'getAllOutfitItems']); //working
    Route::post('/add_update_outfit_item/{id?}', [OutfitItemController::class, 'createOrUpdateOutfitItem']); //working
    Route::delete('/delete_outfit_item/{id}', [OutfitItemController::class, 'deleteOutfitItem']); //working

    // Tags
    Route::get('/tags/{id?}',    [TagController::class, 'getTags']); //working
    Route::post('/add_update_tag/{id?}',   [TagController::class, 'createOrUpdateTag']); //working
    Route::delete('/delete_tag/{id?}', [TagController::class, 'deleteTag']);

    // Clothing Tags
    Route::get('/clothingTags/{id?}',    [ClothingTagController::class, 'getClothingTags']);
    Route::post('/add_update_clothing_tag/{id?}',   [ClothingTagController::class, 'createOrUpdateClothingTag']);
    Route::delete('/delete_clothing_tags/{id?}', [ClothingTagController::class, 'deleteClothingTag']);
    //ai generation GET POST DELETE 
});