<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PlayerController;
use App\Http\Controllers\API\TournamentController;
use App\Http\Controllers\API\MatchContoller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Routes within this group will be prefixed with /api/v1
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:api')->group(function () {
        Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
        Route::get('logout', [AuthController::class, 'logout']);

        Route::resource('tournaments', TournamentController::class);
        Route::get('/tournaments/{id}/players', [PlayerController::class, 'index']);
        Route::post('/tournaments/{id}/players', [PlayerController::class, 'store']);
        Route::delete('/tournaments/{id}/players/{id2}', [PlayerController::class, 'destroy']);

        Route::resource('matches', MatchContoller::class);
    });
});