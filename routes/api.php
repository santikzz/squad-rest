<?php

use App\Http\Controllers\V1\GroupController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// api/v1
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('register', [AuthController::class, 'register']);
});

// user authentication protected endpoints
Route::middleware('auth:sanctum')->get('v1/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1', 'middleware' => ['auth:sanctum']], function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('groups', GroupController::class);
});
