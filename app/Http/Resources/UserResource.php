<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'website' => $this->website,
            'shortening' => $this->shortening,
            'total_friends' => $this->friends_count,
            'network' => $this->network != null ? UserResource::collection($this->network) : null,
            'is_friend' => $this->is_friend ?? false
        ];
    }
}
