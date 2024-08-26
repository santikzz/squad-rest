<?php

use App\Http\Controllers\V1\GroupController;
use App\Http\Controllers\V1\UserController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\MiscController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\V1'], function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('facultades', [MiscController::class, 'getFacultades']);
    Route::get('carreras', [MiscController::class, 'getCarreras']);

    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::get('user', [UserController::class, 'self']);
        Route::put('user', [UserController::class, 'update']);
        Route::post('user/avatar', [UserController::class, 'updateAvatar']);
        Route::get('user/notifications', [UserController::class, 'getNotifications']);
        Route::get('user/notifications/dismiss/{id}', [UserController::class, 'dismissNotification']);
        Route::get('user/requests', [UserController::class, 'getJoinRequests']);
        Route::get('user/requests/{requestId}/{action}', [GroupController::class, 'handleJoinRequest']);
        Route::get('user/joined', [UserController::class, 'getJoinedGroups']);
        Route::get('user/groups', [UserController::class, 'getOwnedGroups']);
        Route::get('user/{ulid}', [UserController::class, 'show']);

        Route::get('groups', [GroupController::class, 'index']);
        Route::get('groups/{ulid}', [GroupController::class, 'show']);
        Route::post('groups', [GroupController::class, 'create']);
        Route::put('groups/{ulid}', [GroupController::class, 'update']);
        Route::delete('groups/{ulid}', [GroupController::class, 'delete']);
        Route::get('groups/join/{ulid}', [GroupController::class, 'join']);
        Route::get('groups/leave/{ulid}', [GroupController::class, 'leave']);
        Route::get('groups/cancel/{ulid}', [GroupController::class, 'cancelRequest']);
        Route::get('groups/{group}/kick/{user}', [GroupController::class, 'kick']);
        Route::get('groups/{ulid}/requests', [GroupController::class, 'getJoinRequests']);
        Route::get('groups/{ulid}/invite', [GroupController::class, 'getInviteLink']);

        Route::get('groups/{ulid}/messages', [MessageController::class, 'getMessages']);
        Route::post('groups/{ulid}/messages', [MessageController::class, 'sendMessage']);

        Route::get('environment', [UserController::class, 'environment']);
        Route::post('feedback', [UserController::class, 'submitFeedback']);
        Route::post('report', [UserController::class, 'submitReport']);
        Route::get('logout', [AuthController::class, 'logout']);
    });
});
