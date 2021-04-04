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
            'is_friend' => $this->is_friend ?? false,
            'picture' => $this->profile_photo_path ?? $this->profile_photo_url,
            'no_topic' => $this->no_topic,
            'failed_to_reach' => $this->failed_to_reach,
            'registered_at' => (string) $this->created_at
        ];
    }
}
