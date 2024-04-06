<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Models\Tag;
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
use App\Models\UserGroupJoinRequest;

class GroupController extends Controller
{
    // list all groups paginated
    public function index(Request $request)
    {

        $groups = Group::where('privacy', '!=', 'private')->orderby('created_at', 'desc');

        if ($request->has('search')) {
            $seach_param = $request->query('search');
            $groups = $groups->where('title', 'LIKE', '%' . $seach_param . '%')
                ->orWhere('description', 'LIKE', '%' . $seach_param . '%');
        }

        if ($request->has('tags')) {
            $tags = explode(',', $request->query('tags'));

            foreach ($tags as $tag) {
                $groups = $groups->whereHas('tags', function ($query) use ($tag) {
                    $query->where('tag', $tag);
                });
            }
        }

        return new GroupCollection($groups->paginate());
    }

    // show group detail data
    public function show(Request $request, $ulid)
    {
        // $group = Group::with('owner', 'members', 'tags')->where('ulid', $ulid)->whereNotIn('privacy', ['private'])->first();
        $group = Group::with('owner', 'members', 'tags')->where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        if ($group->privacy == 'private' && $group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new GroupResource($group));
    }

    public function showOwnerGroup(Request $request, $ulid)
    {
        $group = Group::with('owner', 'members', 'tags')->where('ulid', $ulid)->first();

        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_modification', 'message' => 'You are not authorized to modify this group.']], Response::HTTP_UNAUTHORIZED);
        }
        return response()->json(new GroupResource($group));
    }

    public function create(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'title' => 'required|string|min:10|max:64',
                'description' => 'required|string|min:10|max:255',
                'privacy' => 'required|string|in:open,closed,private',
                // 'hasMemberLimit' => 'nullable|boolean',
                'maxMembers' => 'nullable|integer|min:0|max:25',
                'idCarrera' => 'required|integer|min:1',
                'tags' => 'required|array',
                'tags.*' => 'string|max:255',
            ]);

            $group = new Group();

            $ulid = Ulid::generate(true);

            $maxMembers = null;
            if ($request->has('maxMembers')) {
                // $maxMembers = $validatedData['hasMemberLimit'] == 1 ? $validatedData['maxMembers'] : null;
                
                $maxMembers = $validatedData['maxMembers'];
                
                // max members failsafe
                if($maxMembers <= 0){
                    $maxMembers = null;
                
                }elseif($maxMembers >= 25){
                    $maxMembers = 25;
                }

            }

            $group->ulid = (string)$ulid;
            $group->owner_id = $request->user()->id;
            $group->title = $validatedData['title'];
            $group->description = $validatedData['description'];
            $group->max_members = $maxMembers;
            $group->privacy = $validatedData['privacy'];
            $group->id_carrera = $validatedData['idCarrera'];

            $group->save();

            foreach ($validatedData['tags'] as $tag) {
                $tag = Tag::where('tag', $tag)->first();
                $group->tags()->attach($tag);
            }

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
        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_modification', 'message' => 'You are not authorized to modify this group.']], Response::HTTP_UNAUTHORIZED);
        }

        try {

            $validatedData = $request->validate([
                'title' => 'required|string|min:10|max:64',
                'description' => 'required|string|min:10|max:255',
                'privacy' => 'required|string|in:open,closed,private',
                // 'hasMemberLimit' => 'nullable|boolean',
                'maxMembers' => 'nullable|integer|min:1|max:25',
                'idCarrera' => 'integer|min:1',
                'tags' => 'required|array',
                'tags.*' => 'string|max:255',
            ]);

            $maxMembers = null;
            if ($request->has('maxMembers')) {
                // $maxMembers = $validatedData['maxMembers'] >= 1 ? $validatedData['maxMembers'] : null;
                $maxMembers = $validatedData['maxMembers'];
            }

            $group->title = $validatedData['title'];
            $group->description = $validatedData['description'];
            $group->max_members = $maxMembers;
            $group->privacy = $validatedData['privacy'];
            $group->id_carrera = $validatedData['idCarrera'];

            $group->update();

            $group->tags()->detach();
            foreach ($validatedData['tags'] as $tag) {
                $tag = Tag::where('tag', $tag)->first();
                $group->tags()->attach($tag);
            }

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
        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_modification', 'message' => 'You are not authorized to modify this group.']], Response::HTTP_UNAUTHORIZED);
        }
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully.'], Response::HTTP_OK);
    }

    public function join(Request $request, $ulid = null)
    {

        // $isInviteLink = false;
        // if($ulid == null && $request->has('invite')){
        //     $isInviteLink = true;
        //     $inviteId = $request->query('invite');
        //     $group = Group::where('invite', $inviteId)->first();
        // }else{
        //     $group = Group::where('ulid', $ulid)->first();
        // }

        $group = Group::where('ulid', $ulid)->first();
        $user = $request->user();

        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        // reject if user is owner, can't join it's own group
        if ($group->owner_id == $request->user()->id) {
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

    public function kick(Request $request, $group, $user)
    {

        $group = Group::where('ulid', $group)->first();
        $user = User::where('ulid', $user)->first();

        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_access', 'message' => 'You are not authorized to access data of this group.']], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user) {
            return response()->json(['error' => ['code' => 'user_not_found', 'message' => 'User not found']], Response::HTTP_NOT_FOUND);
        }

        if ($user->id == $request->user()->id) {
            return response()->json(['error' => ['code' => 'cannot_kick_yourself', 'message' => 'You cannot kick yourself from the group.']], Response::HTTP_BAD_REQUEST);
        }

        if (!$group->users()->where('ulid', $user->ulid)->exists()) {
            return response()->json(['error' => ['code' => 'user_not_in_group', 'message' => 'The specified user does not exist in this group.']], Response::HTTP_NOT_FOUND);
        }

        $group->users()->detach($user);

        return response()->json(['message' => 'User kicked from the group successfully'], Response::HTTP_OK);
    }

    public function leave(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();

        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        if ($group->owner_id == $request->user()->id) {
            return response()->json(['error' => ['code' => 'cannot_leave_own_group', 'message' => 'As the owner, you cannot leave your own group. Consider deleting the group instead.']], Response::HTTP_BAD_REQUEST);
        }

        if (!$group->users()->where('id', $request->user()->id)->exists()) {
            return response()->json(['error' => ['code' => 'user_not_in_group', 'message' => 'You are not a member of this group, so you cannot leave it.']], Response::HTTP_NOT_FOUND);
        }

        $group->users()->detach($request->user()->id);
        return response()->json(['message' => 'You have left the group successfully'], Response::HTTP_OK);
    }

    public function getJoinRequests(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
        // reject if user is not the owner
        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_access', 'message' => 'You are not authorized to access data of this group.']], Response::HTTP_BAD_REQUEST);
        }
        return response()->json(JoinRequestResource::collection($group->joinRequests));
    }

    public function cancelRequest(Request $request, $ulid)
    {

        $group = Group::where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        $joinRequest = UserGroupJoinRequest::where('user_id', $request->user()->id)->where('group_id', $group->id);

        if (!$joinRequest->exists()) {
            return response()->json(['error' => ['code' => 'request_not_found', 'message' => 'You don\'t have a join request for this group.']], Response::HTTP_NOT_FOUND);
        }

        $joinRequest->delete();

        return response()->json(['message' => 'Join request canceled'], Response::HTTP_OK);
    }

    public function handleJoinRequest(Request $request, $requestId, $action)
    {

        // $joinRequest = DB::table('user_group_join_request')->where('request_id', $requestId)->get();
        $joinRequest = UserGroupJoinRequest::where('request_id', $requestId)->first();


        if (!$joinRequest) {
            return response()->json(['error' => ['code' => 'invalid_join_request', 'message' => 'Join request not found']], Response::HTTP_BAD_REQUEST);
        }

        $group = Group::where('ulid', $joinRequest->group->ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }

        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_access', 'message' => 'You are not authorized to access data of this group.']], Response::HTTP_BAD_REQUEST);
        }

        $user = User::where('ulid', $joinRequest->user->ulid)->first();
        if (!$user) {
            return response()->json(['error' => ['code' => 'user_not_found', 'message' => 'User not found']], Response::HTTP_NOT_FOUND);
        }

        if ($action == 'accept') {

            DB::table('user_group')->insert([
                'user_id' => $user->id,
                'group_id' => $group->id
            ]);
            DB::table('user_group_join_request')->where('request_id', $requestId)->delete();
            return response()->json(['message' => 'Request accepted'], Response::HTTP_OK);
        } elseif ($action == 'decline') {

            DB::table('user_group_join_request')->where('request_id', $requestId)->delete();
            return response()->json(['message' => 'Request declined'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => ['code' => 'invalid_action', 'message' => 'Invalid action']], Response::HTTP_NOT_FOUND);
        }
    }

    public function getInviteLink(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->first();
        if (!$group) {
            return response()->json(['error' => ['code' => 'group_not_found', 'message' => 'The requested group was not found.']], Response::HTTP_NOT_FOUND);
        }
        // reject if user is not the owner
        if ($group->owner_id !== $request->user()->id) {
            return response()->json(['error' => ['code' => 'unauthorized_group_access', 'message' => 'You are not authorized to access data of this group.']], Response::HTTP_BAD_REQUEST);
        }

        if ($group->invite == null) {
            $group->invite = Str::random(10);
            $group->update();
        }

        return response()->json(['invite' => 'api/v1/groups/join?invite=fDgSk3sdg='], Response::HTTP_OK);
    }
}
