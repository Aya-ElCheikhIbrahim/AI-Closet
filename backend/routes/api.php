<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClothingItemController;
use App\Http\Controllers\OutfitController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']); //working
Route::post('/login', [AuthController::class, 'login']); //working
 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    //clothing items POST DELETE 
    Route::get('/allClothing/{id?}', [ClothingItemController::class, 'getAllClothingItems']); //working

    
    //outfits
    Route::get('/outfits/{id?}', [OutfitController::class, 'getAllOutfits']); //working
    Route::post('/add_update_outfit/{id?}', [OutfitController::class, 'createOrUpdateOutfit']); //working
    Route::delete('/delete_outfit/{id}', [OutfitController::class, 'deleteOutfit']); //working

    //outfit items GET POST DELETE 
    //tag GET POST DELETE 
    //clothing tag GET POST DELETE 
    //ai generation GET POST DELETE 
});