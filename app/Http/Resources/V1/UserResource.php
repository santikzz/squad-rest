<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'ulid' => $this->ulid,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
        ];
    }
}
