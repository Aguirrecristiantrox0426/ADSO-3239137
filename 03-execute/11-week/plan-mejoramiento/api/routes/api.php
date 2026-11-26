
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ToolController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AdvisorController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\RentalReturnController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Estado de la API
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Autenticación
    |--------------------------------------------------------------------------
    */

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        /*
        |--------------------------------------------------------------------------
        | Rutas exclusivas para administradores
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:administrator')->group(function () {

            // Gestión de usuarios
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{user}', [UserController::class, 'show']);
            Route::post('/users', [UserController::class, 'store']);
            Route::put('/users/{user}', [UserController::class, 'update']);
            Route::delete('/users/{user}', [UserController::class, 'destroy']);

            // Administración de categorías
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{category}', [CategoryController::class, 'update']);
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

            // Eliminación de herramientas
            Route::delete('/tools/{id}', [ToolController::class, 'destroy']);

            // Administración de clientes
            Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
            Route::patch('/customers/{id}/reactivate', [CustomerController::class, 'reactivate']);

            // Administración de asesores
            Route::post('/advisors', [AdvisorController::class, 'store']);
            Route::put('/advisors/{id}', [AdvisorController::class, 'update']);
            Route::delete('/advisors/{id}', [AdvisorController::class, 'destroy']);
        });

        /*
        |--------------------------------------------------------------------------
        | Rutas para administradores y asesores
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:administrator,advisor')->group(function () {

            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index']);

            // Consulta de categorías
            Route::get('/categories', [CategoryController::class, 'index']);

            // Gestión de herramientas
            Route::get('/tools/availability', [ToolController::class, 'availability']);
            Route::get('/tools/{id}/availability', [ToolController::class, 'showAvailability']);
            Route::get('/tools', [ToolController::class, 'index']);
            Route::get('/tools/{id}', [ToolController::class, 'show']);
            Route::post('/tools', [ToolController::class, 'store']);
            Route::put('/tools/{id}', [ToolController::class, 'update']);

            // Gestión de clientes
            Route::get('/customers', [CustomerController::class, 'index']);
            Route::get('/customers/{id}', [CustomerController::class, 'show']);
            Route::post('/customers', [CustomerController::class, 'store']);
            Route::put('/customers/{id}', [CustomerController::class, 'update']);

            // Consulta de asesores
            Route::get('/advisors', [AdvisorController::class, 'index']);
            Route::get('/advisors/{id}', [AdvisorController::class, 'show']);

            // Gestión de alquileres
            Route::get('/rentals', [RentalController::class, 'index']);
            Route::post('/rentals', [RentalController::class, 'store']);
            Route::get('/rentals/{id}/details', [RentalController::class, 'details']);
            Route::get('/rentals/{id}', [RentalController::class, 'show']);

            // Gestión de devoluciones
            Route::get('/returns', [RentalReturnController::class, 'index']);
            Route::post('/returns', [RentalReturnController::class, 'store']);
            Route::get('/returns/{id}', [RentalReturnController::class, 'show']);
        });
    });
});
