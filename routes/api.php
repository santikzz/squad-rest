<?php

use App\Http\Controllers\V1\GroupController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\GroupResource;
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

// public endpoints
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('register', [AuthController::class, 'register']);
});

// user authentication protected endpoints
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1', 'middleware' => ['auth:sanctum']], function () {
    
    // user self endpoints
    Route::get('user', function (Request $request) { return new UserResource($request->user()); });
    Route::get('user/groups', [UserController::class, 'getOwnedGroups']);
    
    //Route::apiResource('users', UserController::class); // users listing shouldn't be available ( ? )
    Route::get('user/{ulid}', [UserController::class, 'show']);
    
    // Route::apiResource('groups', GroupController::class);
    Route::get('groups', [GroupController::class, 'index']);
    Route::get('groups/{ulid}', [GroupController::class, 'show']);
    
    // GROUPS CRUD
    Route::post('groups', [GroupController::class, 'create']);
    Route::put('groups/{ulid}', [GroupController::class, 'update']);
    Route::delete('groups/{ulid}', [GroupController::class, 'delete']);

});

