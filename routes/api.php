<?php

use Illuminate\Http\Request;
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

Route::group(['prefix' => 'user/oauth'], function () {

    Route::post('login',  [App\Http\Controllers\AuthController::class, 'login']);
    Route::post('signup', [App\Http\Controllers\AuthController::class, 'signup']);
    Route::post('forgotPassword',     [App\Http\Controllers\AuthController::class, 'forgot_password']);
    Route::post('resetPassword',      [App\Http\Controllers\AuthController::class, 'reset_password']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('logout', [App\Http\Controllers\AuthController::class, 'logout']);
        Route::get('user', [App\Http\Controllers\AuthController::class, 'user']);
        Route::post('changePassword',     [App\Http\Controllers\AuthController::class, 'change_password']);
    });
});



// ****** WHAT I FOUND


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
