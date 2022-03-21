<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'message_id' => $this->message_id,
            'user_id' => $this->message_id,
            'reply' => $this->reply,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
