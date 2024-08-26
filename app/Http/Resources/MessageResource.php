<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $date = new \DateTime($this->created_at);
        $formattedDate = $date->format('H:i');

        return [
            'user' => $this->user->name . " " . $this->user->surname,
            'message' => $this->message,
            'timestamp' => $this->created_at,
            'formatted' => $formattedDate,
        ];
    }
}
