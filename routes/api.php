<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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

Route::controller(PostController::class)->prefix('posts')->group(function () {
  Route::get('/', 'index');
  Route::middleware(['auth:api'])->group(function () {
    Route::post('/', 'store');
  });
});

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::middleware(['auth:api'])->group(function () {
      Route::post('logout', 'logout');
      Route::get('whoiam', 'getUserByToken');
    });
});

Route::controller(UserController::class)->prefix('users')->group(function () {
  Route::get('/{id}', 'show');
  Route::middleware(['auth:api'])->group(function () {
    Route::put('/', 'update');
  });
});