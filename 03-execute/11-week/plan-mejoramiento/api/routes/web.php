<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'application' => 'RENT-TOOLS',
        'message' => 'Backend del sistema de alquiler de herramientas.',
        'api' => '/api/v1',
        'health' => '/api/v1/health',
    ]);
});
