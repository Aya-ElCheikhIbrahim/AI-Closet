<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::get('/allClothing/{id?}', [ClothingItemController::class, 'getAllClothingItems']);
Route::put('/outfit/{id?}', [OutfitController::class,'updateOutfit']);
Route::post('/outfit/{id?}', [OutfitController::class,'createOutfit']);
Route::delete('/outfit/{id?}', [OutfitController::class,'deleteOutfit']);
