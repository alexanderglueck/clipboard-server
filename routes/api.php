<?php

use App\Http\Controllers\API\AzureController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiLoginController;
use App\Http\Controllers\API\ApiTokenController;
use App\Http\Controllers\API\ApiDeviceController;
use App\Http\Controllers\API\ApiPostController;

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


Route::post('/session', [ApiLoginController::class, 'login'])->name('api.login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/devices', [ApiDeviceController::class, 'index'])->name('api.devices.index');
    Route::post('/devices', [ApiDeviceController::class, 'store'])->name('api.devices.store');
    Route::post('/remove-token', [ApiTokenController::class, 'destroy'])->name('api.token.destroy');
    Route::post('/attach-token', [ApiTokenController::class, 'store'])->name('api.token.store');
    Route::post('/paste', [ApiPostController::class, 'store'])->name('api.post.store');
});

Route::post('/test', [AzureController::class, 'store']);
