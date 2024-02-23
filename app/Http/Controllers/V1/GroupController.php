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
use Ulid\Ulid;


class GroupController extends Controller
{
    // list all groups paginated
    public function index()
    {
        return response()->json(new GroupCollection(Group::paginate()));
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
            return response()->json(['message' => 'Invalid parameters'], Response::HTTP_BAD_REQUEST);
        }
    }

    // show group detail data
    public function show($ulid)
    {
        $group = Group::with('owner', 'members', 'tags')->where('ulid', $ulid)->first();

        if ($group) {
            return response()->json(new GroupResource($group));
        } else {
            return response()->json(['message' => 'Group not found'], Response::HTTP_NOT_FOUND);
        }
    }

    // only group owner can modify
    public function update(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->firstOrFail();

        if ($group->owner_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
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

            return response()->json(['message' => 'Invalid parameters'], Response::HTTP_BAD_REQUEST);
        }
    }

    // only group owner can delete
    public function delete(Request $request, $ulid)
    {
        $group = Group::where('ulid', $ulid)->firstOrFail();
        if ($group->owner_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully'], Response::HTTP_OK);
    }
}
