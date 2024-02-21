<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {   
        // disable all user listing
        // return new UserCollection(User::paginate());
    }

    // get user data with ulid
    public function show($ulid)
    {

        $user = User::where('ulid', $ulid)->first();

        if($user){
            return new UserResource($user);
        }else{
            return response()->json(['message' => 'user not found'], 404);
        }

    }

}
