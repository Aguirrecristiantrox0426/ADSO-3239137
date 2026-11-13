<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ToolController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AdvisorController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\RentalReturnController;

Route::prefix('v1')->group(function () {

    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'La API de RENT-TOOLS está funcionando.',
            'data' => [
                'application' => 'RENT-TOOLS',
                'version' => '1.0.0',
            ],
        ]);
    });

    Route::get('/tools/availability', [ToolController::class, 'availability']);
    Route::get('/tools/{id}/availability', [ToolController::class, 'showAvailability']);

    Route::get('/tools', [ToolController::class, 'index']);
    Route::get('/tools/{id}', [ToolController::class, 'show']);
    Route::post('/tools', [ToolController::class, 'store']);
    Route::put('/tools/{id}', [ToolController::class, 'update']);
    Route::delete('/tools/{id}', [ToolController::class, 'destroy']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

    Route::get('/advisors', [AdvisorController::class, 'index']);
    Route::get('/advisors/{id}', [AdvisorController::class, 'show']);
    Route::post('/advisors', [AdvisorController::class, 'store']);
    Route::put('/advisors/{id}', [AdvisorController::class, 'update']);
    Route::delete('/advisors/{id}', [AdvisorController::class, 'destroy']);

    Route::get('/rentals', [RentalController::class, 'index']);
    Route::post('/rentals', [RentalController::class, 'store']);
    Route::get('/rentals/{id}/details', [RentalController::class, 'details']);
    Route::get('/rentals/{id}', [RentalController::class, 'show']);

    Route::get('/returns', [RentalReturnController::class, 'index']);
    Route::post('/returns', [RentalReturnController::class, 'store']);
    Route::get('/returns/{id}', [RentalReturnController::class, 'show']);

});