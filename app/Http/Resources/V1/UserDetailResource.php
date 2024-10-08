<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $memberSince = Carbon::parse($this->created_at)->format('d/m/Y');

        return [
            'ulid' => $this->ulid,
            'name' => $this->name,
            'surname' => $this->surname,
            'about' => $this->about,
            'facultad' => $this->carrera->facultad->name,
            'carrera' => $this->carrera->name,
            'email' => $this->email,
            'memberSince' => $memberSince,
            'avatar' => $this->profile_image,
            'avatarFallback' => strtoupper($this->name[0]) . strtoupper($this->surname[0]),
            'isOwner' => ($this->ulid == $request->user()->ulid),
        ];
    }
}
