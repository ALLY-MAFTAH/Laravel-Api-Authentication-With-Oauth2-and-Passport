<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Social Auth
Route::get('oauth/social', [App\Http\Controllers\SocialAuthController::class, 'show'])->name('social.login');
Route::get('oauth/{driver}', [App\Http\Controllers\SocialAuthController::class, 'redirectToProvider'])->name('social.oauth');
Route::get('oauth/{driver}/callback', [App\Http\Controllers\SocialAuthController::class, 'handleProviderCallback'])->name('social.callback');

