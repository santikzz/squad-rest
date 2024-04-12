<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UserDetailResource;
use App\Http\Resources\V1\GroupResource;
use App\Http\Resources\V1\JoinRequestResource;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserGroupJoinRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Laravel\Facades\Image;

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
        $user = User::with('carrera.facultad')->where('ulid', $ulid)->first();
        if ($user) {
            return response()->json(new UserDetailResource($user));
            // return response()->json($user);
        } else {
            return response()->json(['error' => ['code' => 'user_not_found', 'message' => 'User not found']], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request)
    {
        // $user = User::where('id', $request->user()->id)->first();
        $user = $request->user();

        try {

            $validatedData = $request->validate([
                'name' => 'string|max:50',
                'surname' => 'string|max:50',
                'about' => 'string|min:255',
                'password' => 'string|min:8|confirmed',
            ]);

            if ($request->has('name')) {
                $user->name = $validatedData['name'];
            }
            if ($request->has('surname')) {
                $user->surname = $validatedData['surname'];
            }
            if ($request->has('about')) {
                $user->about = $validatedData['about'];
            }

            if ($request->has('old_password', 'password', 'password_confirmation')) {
                if (Hash::check($request->input('old_password'), $user->password)) {
                    $user->password = Hash::make($request->input('password'));
                }
            }

            $user->save();
            // return response()->json(new UserResource($user), Response::HTTP_OK);
            return response()->json(['message' => 'User data modified successfully.'], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_parameters', 'message' => 'Invalid or malformed parameters for user modification.']], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateAvatar(Request $request)
    {

        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_image']], Response::HTTP_BAD_REQUEST);
        }

        // $user = $request->user();
        $image = $request->file('avatar');

        // $filename = $image->hashName();
        // $img = Image::read($image->path());
        // $img->resize(150, 150, function($constraint){
        //     $constraint->aspectRatio();
        // })->save('/avatar/'.$filename);

        // $filename = $request->user()->ulid . "_" . $file->hashName() . "." . $file->extension();

        $path = $image->storeAs(
            'public/avatar',
            $image->hashName()
        );

        if($path){
            $user = $request->user();
            $user->profile_image = $path;
            $user->save();
        }

        return response()->json(['path' => $path], Response::HTTP_OK);
    }

    // only group owner can delete
    public function delete(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_modification', 'message' => 'You are not authorized to modify this group.']], Response::HTTP_UNAUTHORIZED);
        }
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully.'], Response::HTTP_OK);
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
