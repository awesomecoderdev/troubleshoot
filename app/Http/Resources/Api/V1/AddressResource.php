<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{

    /**
     * Without field.
     *
     * @return array
     */
    public $without = [
        // 'id',
        'updated_at',
        'created_at',
    ];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [$this->merge(Arr::except(parent::toArray($request), $this->without))];
    }
}
