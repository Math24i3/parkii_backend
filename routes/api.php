<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DOSpacesController;
use App\Http\Controllers\ParkingDataController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum', 'cors']], function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/upload', [DOSpacesController::class, 'store']);

    Route::prefix('parking')->group(function () {
        Route::get('/zones', [ParkingDataController::class, 'zones']);
        Route::get('/restrictions', [ParkingDataController::class, 'restrictions']);
    });
});


