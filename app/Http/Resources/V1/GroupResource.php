<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class GroupResource extends JsonResource
{
    // get group data
    public function toArray(Request $request): array
    {

        $user = $request->user();

        $joined = $this->members->contains($user);

        $joinRequest = DB::table('user_group_join_request')
            ->where('user_id', $user->id)
            ->where('group_id', $this->id)
            ->exists();

        // $joinRequest = $joinRequestExists ? 'pending' : 'none';

        return [
            'ulid' => $this->ulid,
            'owner' => new UserGroupResource($this->owner),
            'title' => $this->title,
            'description' => $this->description,
            'tags' => $this->tags->pluck('tag'),
            'facultad' => $this->carrera->facultad->name,
            'carrera' => $this->carrera->name,
            'maxMembers' => $this->max_members,
            'membersCount' => ($this->members->count() + 1),
            'privacy' => $this->privacy,
            'members' => UserGroupResource::collection($this->members),
            'creationDate' => $this->created_at,
            'user' => ['isMember' => $joined, 'hasJoinRequest' => $joinRequest],
            'isOwner' => ($this->owner->ulid == $user->ulid),
        ];
    }
}
