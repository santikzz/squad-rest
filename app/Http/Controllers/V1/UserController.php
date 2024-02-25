<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\GroupResource;
use App\Http\Resources\V1\JoinRequestResource;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserGroupJoinRequest;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index()
    {
        // disable all user listing
        // return new UserCollection(User::paginate());
    }

    // get user data with ulid
    public function show(Request $request, $ulid)
    {
        $user = User::where('ulid', $ulid)->first();
        if ($user) {
            return response()->json(new UserResource($user));
        } else {
            return response()->json(['error' => ['code' => 'user_not_found', 'message' => 'User not found']], Response::HTTP_NOT_FOUND);
        }
    }

    public function getOwnedGroups(Request $request)
    {
        $user = $request->user();
        $groups = $user->ownedGroups;
        return response()->json(GroupResource::collection($groups));
    }

    public function getJoinedGroups(Request $request)
    {
        $user = $request->user();
        $groups = $user->joinedGroups;
        return response()->json(GroupResource::collection($groups));
    }

    public function getJoinRequests(Request $request)
    {
        $user = $request->user();
        $ownedGroups = $user->ownedGroups()->get();
        $joinRequests = collect();
        // Iterate over each owned group and retrieve its join requests
        foreach ($ownedGroups as $group) {
            $joinRequests = $joinRequests->merge($group->joinRequests);
        }
        return response()->json(JoinRequestResource::collection($joinRequests));
    }
}
