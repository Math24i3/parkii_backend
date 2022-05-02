<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DOSpacesController;
use App\Http\Controllers\ParkingDataController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'userProfile']);

    //user
    Route::apiResource('/users', UserController::class);
    // parking
    Route::prefix('parking')->group(function () {
        Route::get('/zones', [ParkingDataController::class, 'zones']);
        Route::get('/restrictions', [ParkingDataController::class, 'restrictions']);
    });
    // upload
    Route::post('/upload', [DOSpacesController::class, 'store']);
});



