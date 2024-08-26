<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGroupResource extends JsonResource
{
    // this is the user simplified version for the group data
    public function toArray(Request $request): array
    {
        return [
            'ulid' => $this->ulid,
            'name' => $this->name,
            'surname' => $this->surname,
            'avatar' => $this->profile_image,
            'avatarFallback' => strtoupper($this->name[0]) . strtoupper($this->surname[0]),
        ];
    }
}
