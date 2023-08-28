<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
            "id",
            "zone_id",
            "company_name",
            "first_name",
            "last_name",
            "email",
            // "password",
            "phone",
            "address",
            "identity_number",
            "contact_person_name",
            "contact_person_phone",
            "contact_email",
            "image",
            // "identity_image",
            "order_count",
            "service_man_count",
            "service_capacity_per_day",
            "rating_count",
            "avg_rating",
            "commission_status",
            "commission_percentage",
            "is_active",
            "is_approved",
            "start",
            "end",
            "off_day",
            // Add other user data as needed
        ]);
    }
}
