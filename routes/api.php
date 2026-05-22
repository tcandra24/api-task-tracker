<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function(){
    Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class, [ 'except' => 'show' ]);
    Route::apiResource('plans', \App\Http\Controllers\Api\PlanController::class);
    Route::apiResource('realizations', \App\Http\Controllers\Api\RealizationController::class);
});


