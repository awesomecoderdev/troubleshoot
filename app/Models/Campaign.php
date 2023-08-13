<?php

namespace App\Models;

use App\Models\Zone;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Service
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Display the specified resource.
     *
     * @return  \App\Models\Zone
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Interact with the user's image.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value != null ? asset($value) : null,
            // set: fn ($value) => strtolower($value),
        );
    }
}
