<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CallEventApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/login', [AuthApiController::class, 'login'])->name('login');
Route::post('/registration', [AuthApiController::class, 'registration'])->name('registration');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->prefix('auth')
    ->name('auth.')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout'])->name('logout');
    });

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::post('/call-event', [CallEventApiController::class, 'receive'])->name('call-event.receive');
    });
