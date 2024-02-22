<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\V1\GroupResource;
use App\Http\Resources\V1\GroupCollection;
// use App\Http\Resources\V1\UserGroupResource;


class GroupController extends Controller
{
    // list all groups paginated
    public function index()
    {
        return response()->json(new GroupCollection(Group::paginate()));
    }

    public function create()
    {
        //
    }

    public function store(StoreGroupRequest $request)
    {
        return null;
    }

    // show group detail data
    public function show($ulid)
    {
        $group = Group::with('owner', 'members', 'tags')->where('ulid', $ulid)->first();

        if ($group) {
            return new GroupResource($group);
        } else {
            return response()->json(['message' => 'group not found'], 404);
        }
    }

    public function edit(Group $group)
    {
        //
    }

    public function update(UpdateGroupRequest $request, Group $group)
    {
        //
    }

    public function destroy(Group $group)
    {
        //
    }
}
