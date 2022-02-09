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

    Route::post('oauth/login',  [App\Http\Controllers\ApiAuthController::class, 'login']);
    Route::post('oauth/signup', [App\Http\Controllers\ApiAuthController::class, 'signup']);
    Route::post('oauth/forgotPassword',     [App\Http\Controllers\ApiAuthController::class, 'forgot_password']);
    Route::post('oauth/resetPassword',      [App\Http\Controllers\ApiAuthController::class, 'reset_password']);

    // Social Auth
    Route::get('oauth/social', [App\Http\Controllers\SocialAuthController::class, 'show']);
    Route::get('oauth/{driver}', [App\Http\Controllers\SocialAuthController::class, 'redirectToProvider']);
    Route::get('oauth/{driver}/callback', [App\Http\Controllers\SocialAuthController::class, 'handleProviderCallback']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('oauth/logout', [App\Http\Controllers\ApiAuthController::class, 'logout']);
        Route::post('oauth/user', [App\Http\Controllers\ApiAuthController::class, 'user']);
        Route::post('oauth/changePassword',     [App\Http\Controllers\ApiAuthController::class, 'change_password']);
    });
});
