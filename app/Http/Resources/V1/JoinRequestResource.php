<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JoinRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'requestId' => $this->request_id,
            'createdAt' => $this->created_at,
            'group' => new GroupShortResource($this->group),
            'user' => new UserGroupResource($this->user),
            'actions' => [
                'accept' => 'api/v1/user/groups/request/' . $this->request_id . '/accept',
                'decline' => 'api/v1/user/groups/request/' . $this->request_id . '/decline',
            ]
        ];
    }
}
