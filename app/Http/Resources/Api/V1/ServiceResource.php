<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Without field.
     *
     * @return array
     */
    public $without = [
        // 'id',
        'updated_at',
        'email_verified_at',
        'access_token',
    ];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    // public function toArray($request)
    // {
    //     if (Auth::check()) {
    //         if (!Auth::user()->isAdmin) {
    //             $this->without = array_merge($this->without, ['isAdmin',]);
    //         }
    //         if (Auth::user()->provider == null) {
    //             $this->without = array_merge($this->without, ['provider',]);
    //         }
    //     }
    //     return [
    //         $this->merge(Arr::except(parent::toArray($request), $this->without))
    //     ];
    // }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return Arr::only(parent::toArray($request), [
            // "name",
            // "parent_id",
            // "category_id",
            // "provider_id",
            // "zone_id",
            // "price",
            // "fixed",
            // "duration",
            // "image",
            // "discount",
            // "status",
            // "short_description",
            // "long_description",
            // "tax",
            // "order_count",
            // "rating_count",
            // "avg_rating",
            // "is_featured",
            // "by_admin",
            // Add other user data as needed
        ]);
    }
}
