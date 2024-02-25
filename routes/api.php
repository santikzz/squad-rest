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

    // private self user endpoints
    Route::get('user', function (Request $request) { return new UserResource($request->user()); });                         // get self user data
    Route::get('user/groups', [UserController::class, 'getOwnedGroups']);                                                   // get self user groups
    Route::get('user/groups/joined', [UserController::class, 'getJoinedGroups']);                                           // get self user joined groups
    Route::get('user/groups/{ulid}/requests', [GroupController::class, 'getJoinRequests']);                                 // get self user owned {group_id} join requests
    Route::get('user/groups/requests', [UserController::class, 'getJoinRequests']);                                         // get self user all owned groups join requests

    // public group endpoints
    Route::get('users/{ulid}', [UserController::class, 'show']);                                                            // get user {user_id} data

    Route::get('groups', [GroupController::class, 'index']);                                                                // get groups listing - paginated
    Route::get('groups/{ulid}', [GroupController::class, 'show']);                                                          // get group {group_id} data
    
    Route::post('groups', [GroupController::class, 'create']);                                                              // create group
    Route::put('groups/{ulid}', [GroupController::class, 'update']);                                                        // update {group_id} data
    Route::delete('groups/{ulid}', [GroupController::class, 'delete']);                                                     // delte {group_id} group
    
    Route::get('groups/{ulid}/join', [GroupController::class, 'join']);                                                     // join / request access to group

});
