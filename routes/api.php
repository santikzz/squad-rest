<?php

use App\Http\Controllers\V1\GroupController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\MiscController;
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
    // Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10:5');
    // Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5:10'); // rate limit error: 429
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']); // rate limit error: 429
});

// user authentication protected endpoints
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1', 'middleware' => ['auth:sanctum']], function () {
    
    Route::get('logout', [AuthController::class, 'logout']);
    
    // private self user endpoints
    Route::get('user', function (Request $request) { return new UserResource($request->user()); });
    
    Route::get('user/requests', [UserController::class, 'getJoinRequests']);
    Route::get('user/requests/{requestId}/{action}', [GroupController::class, 'handleJoinRequest']);
    Route::get('user/joined', [UserController::class, 'getJoinedGroups']);
    Route::get('user/groups', [UserController::class, 'getOwnedGroups']);
                
    Route::get('users/{ulid}', [UserController::class, 'show']);
    
    Route::get('groups', [GroupController::class, 'index']);
    Route::get('groups/{ulid}', [GroupController::class, 'show']);
    Route::post('groups', [GroupController::class, 'create']);
    Route::put('groups/{ulid}', [GroupController::class, 'update']);
    Route::delete('groups/{ulid}', [GroupController::class, 'delete']);
    Route::get('groups/join/{ulid}', [GroupController::class, 'join']);
    Route::get('groups/leave/{ulid}', [GroupController::class, 'leave']);
    Route::get('groups/cancel/{ulid}', [GroupController::class, 'cancelRequest']);

    //Route::get('groups/{ulid}', [GroupController::class, 'showOwnerGroup']);
    Route::get('groups/{group}/kick/{user}', [GroupController::class, 'kick']);
    Route::get('groups/{ulid}/requests', [GroupController::class, 'getJoinRequests']);
    Route::get('groups/{ulid}/invite', [GroupController::class, 'getInviteLink']);
    
    Route::get('facultades', [MiscController::class, 'getFacultades']);

});
