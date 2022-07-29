<?php

namespace App\Http\Resources;

use App\Http\Resources\TagResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ListingResource extends JsonResource
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

            'employment_type' => Str::title($this->employment_type),

            'position' => $this->position,
            'location' => $this->location,
            'description' => $this->description,

            'is_highlighted' => $this->is_highlighted,
            'is_pinned' => $this->is_pinned,

            'min_annual_salary' => $this->min_annual_salary,
            'max_annual_salary' => $this->max_annual_salary,

            'apply_url' => $this->apply_url,

            'is_closed' => $this->is_closed,

            'paid_at' => $this->paid_at,

            'user' => new UserResource($this->whenLoaded('user')),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            'clicks_count' => $this->clicks_count,
        ];
    }
}
