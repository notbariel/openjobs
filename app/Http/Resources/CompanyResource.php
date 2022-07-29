<?php

namespace App\Http\Resources;

use App\Http\Resources\ListingResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CompanyResource extends JsonResource
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
            'id' => $this->nanoid,

            'name' => $this->name,
            'description' => $this->description,
            'email' => $this->email,
            'url' => $this->url,

            'host' => $this->host,

            'logo' => $this->logo_src,

            'initials' => Str::initials($this->name),

            'invoice_address' => $this->invoice_address,
            'invoice_notes' => $this->invoice_notes,

            'user' => new UserResource($this->whenLoaded('user')),
            'listings' => ListingResource::collection($this->whenLoaded('listings')),

            'open_listings_count' => $this->open_listings_count,
        ];
    }
}
