<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    // get group data
    public function toArray(Request $request): array
    {
        return [
            'ulid' => $this->ulid,
            'owner' => new UserGroupResource($this->owner),
            'title' => $this->title,
            'description' => $this->description,
            'tags' => $this->tags->pluck('tag'), //GroupTagResource::collection($this->tags),
            'maxMembers' => $this->max_members,
            'memersCount' => ($this->members->count()+1),
            'privacy' => $this->privacy,
            'members' => UserGroupResource::collection($this->members),
        ];
    }
}
