<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\V1\GroupResource;
use App\Http\Resources\V1\GroupCollection;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Ulid\Ulid;
use Illuminate\Support\Str;
use App\Http\Resources\V1\JoinRequestResource;

class GroupController extends Controller
{
    // list all groups paginated
    public function index()
    {
        $groups = Group::where('privacy', '!=', 'private')->paginate();
        // $groups = Group::whereNotIn('privacy', ['private'])->paginate();
        return response()->json(new GroupCollection($groups));
    }

    // show group detail data
    public function show($ulid)
    {
        $group = Group::with('owner', 'members', 'tags')->where('ulid', $ulid)->whereNotIn('privacy', ['private'])->first();

        if ($group) {
            return response()->json(new GroupResource($group));
        } else {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
    }

    public function create(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'title' => 'required|string|min:10|max:64',
                'description' => 'required|string|min:10|max:255',
                'privacy' => 'required|string|in:open,closed,private',
                'hasMemberLimit' => 'nullable|boolean',
                'maxMembers' => 'nullable|integer|min:2|max:99',
            ]);

            $group = new Group();

            $ulid = Ulid::generate(true);

            $maxMembers = null;
            if ($request->has('hasMemberLimit', 'maxMembers')) {
                $maxMembers = $validatedData['hasMemberLimit'] == 1 ? $validatedData['maxMembers'] : null;
            }

            $group->ulid = (string)$ulid;
            $group->owner_id = auth()->id();
            $group->title = $validatedData['title'];
            $group->description = $validatedData['description'];
            $group->max_members = $maxMembers;
            $group->privacy = $validatedData['privacy'];

            $group->save();

            return response()->json(new GroupResource($group), Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            // return response()->json(['message' => 'Invalid parameters'], Response::HTTP_BAD_REQUEST);
            return response()->json(['error' => ['code' => 'invalid_parameters', 'message' => 'One or more parameters are invalid.']], Response::HTTP_BAD_REQUEST);
        }
    }

    // only group owner can modify
    public function update(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
        if ($group->owner_id !== auth()->id()) {
            return response()->json(['error' => ['code' => 'unauthorized_group_modification', 'message' => 'You are not authorized to modify this group.']], Response::HTTP_UNAUTHORIZED);
        }

        try {

            $validatedData = $request->validate([
                'title' => 'required|string|min:10|max:64',
                'description' => 'required|string|min:10|max:255',
                'privacy' => 'required|string|in:open,closed,private',
                'hasMemberLimit' => 'nullable|boolean',
                'maxMembers' => 'nullable|integer|min:2|max:99',
            ]);

            $maxMembers = null;
            if ($request->has('hasMemberLimit', 'maxMembers')) {
                $maxMembers = $validatedData['hasMemberLimit'] == 1 ? $validatedData['maxMembers'] : null;
            }

            $group->title = $validatedData['title'];
            $group->description = $validatedData['description'];
            $group->max_members = $maxMembers;
            $group->privacy = $validatedData['privacy'];

            $group->update();

            return response()->json(new GroupResource($group), Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['error' => ['code' => 'invalid_group_parameters', 'message' => 'Invalid or malformed parameters for group creation.']], Response::HTTP_BAD_REQUEST);
        }
    }

    // only group owner can delete
    public function delete(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
        if ($group->owner_id !== auth()->id()) {
            return response()->json(['error' => ['code' => 'unauthorized_group_modification', 'message' => 'You are not authorized to modify this group.']], Response::HTTP_UNAUTHORIZED);
        }
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully.'], Response::HTTP_OK);
    }

    public function join(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        $user = $request->user();

        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        // reject if user is owner, can't join it's own group
        if ($group->owner_id == auth()->id()) {
            return response()->json(['error' => ['code' => 'cannot_join_own_group', 'message' => 'You cannot join a group you own.']], Response::HTTP_BAD_REQUEST);
        }

        // reject if user is already in the group
        $alreadyMember = DB::table('user_group')
            ->where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->exists();
        if ($alreadyMember) {
            return response()->json(['error' => ['code' => 'already_group_member', 'message' => 'You are already a member of this group.']], Response::HTTP_BAD_REQUEST);
        }

        // reject if the group has a member limit, and its already full
        if ($group->max_members != NULL && ($group->members->count() + 1) >= $group->max_members) {
            return response()->json(['error' => ['code' => 'group_full', 'message' => 'The group is full.']], Response::HTTP_BAD_REQUEST);
        }

        // check if group is closed, send join request
        if ($group->privacy == 'closed') {

            // if user already made a join request, don't make another
            $requestAlreadyExists = DB::table('user_group_join_request')
                ->where('user_id', $user->id)
                ->where('group_id', $group->id)
                ->exists();
            if ($requestAlreadyExists) {
                return response()->json(['error' => ['code' => 'join_request_already_sent', 'message' => 'You have already sent a join request to this group.']], Response::HTTP_BAD_REQUEST);
            }

            // make join request
            DB::table('user_group_join_request')->insert([
                'request_id' => Str::random(10),
                'user_id' => $user->id,
                'group_id' => $group->id,
                'owner_id' => $group->owner_id
            ]);
            return response()->json(['message' => 'Join request sent successfully.']);
        }

        // if the group is private, return unauthorized
        if ($group->privacy == 'private') {
            // if ($request->has('secret')) {
            // $secret = $request->query('secret');
            // }
            return response()->json(['error' => ['code' => 'private_group', 'message' => 'You can\'t join a private group.']], Response::HTTP_BAD_REQUEST);
        }

        // if it's open, just join
        DB::table('user_group')->insert([
            'user_id' => $user->id,
            'group_id' => $group->id
        ]);
        return response()->json(['message' => 'Group joined successfully.'], Response::HTTP_OK);
    }

    public function getJoinRequests(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        $user = $request->user();

        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        // reject if user is not the owner
        if ($group->owner_id !== auth()->id()) {
            return response()->json(['error' => ['code' => 'unauthorized_group_access', 'message' => 'You are not authorized to access data of this group.']], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(JoinRequestResource::collection($group->joinRequests));
    }
}
