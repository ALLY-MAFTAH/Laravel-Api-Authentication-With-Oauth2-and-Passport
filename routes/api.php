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

Route::group(['prefix' => 'user/'], function () {

    Route::post('oauth2/login', [App\Http\Controllers\ApiAuthController::class, 'login']);
    Route::post('oauth2/signup', [App\Http\Controllers\ApiAuthController::class, 'signup']);
    Route::post('oauth2/forgotPassword', [App\Http\Controllers\ApiAuthController::class, 'forgot_password']);
    Route::post('oauth2/resetPassword', [App\Http\Controllers\ApiAuthController::class, 'reset_password']);

    // Social Auth
    Route::post('/social/oauth2/{driver}', [App\Http\Controllers\SocialAuthController::class, 'socialViaAPI']);
    // Route::get('oauth2/{driver}', [App\Http\Controllers\SocialAuthController::class, 'redirectToProvider']);
    // Route::get('oauth2/{driver}/callback', [App\Http\Controllers\SocialAuthController::class, 'handleProviderCallback']);


    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('oauth2/logout', [App\Http\Controllers\ApiAuthController::class, 'logout']);
        Route::post('oauth2/user', [App\Http\Controllers\ApiAuthController::class, 'user']);
        Route::post('oauth2/changePassword', [App\Http\Controllers\ApiAuthController::class, 'change_password']);
    });
});
