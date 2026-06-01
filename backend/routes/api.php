<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClothingItemController;
use App\Http\Controllers\OutfitController;
use App\Http\Controllers\OutfitItemController;
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

    //tag GET POST DELETE 
    //clothing tag GET POST DELETE 
    //ai generation GET POST DELETE 
});