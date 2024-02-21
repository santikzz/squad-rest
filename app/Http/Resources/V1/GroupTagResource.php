<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupTagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            $this->tag,
        ];
    }
}
