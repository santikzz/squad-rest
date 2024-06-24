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
            'about' => $this->about,
            'email' => $this->email,
            'profileImg' => $this->profile_image,
            'idCarrera' => $this->id_carrera,
            'registrationDate' => $this->created_at,
        ];
    }
}
