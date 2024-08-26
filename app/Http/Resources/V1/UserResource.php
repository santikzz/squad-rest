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
            'avatar' => $this->profile_image,
            'avatarFallback' => strtoupper($this->name[0]) . strtoupper($this->surname[0]),
            'idCarrera' => $this->id_carrera,
            'registrationDate' => $this->created_at,
            'carrera' => $this->carrera->name,
            'facultad' => $this->facultad,
        ];
    }
}
